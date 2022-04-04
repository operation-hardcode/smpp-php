<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction;

use Amp;
use OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionClosedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionEstablishedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterPduConsumedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterPduProducedExtension;
use OperationHardcode\Smpp\Protocol\Command\Unbind;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Sequence;
use OperationHardcode\Smpp\Transport\Connection;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class SmppExecutor
{
    private ?Connection $connection = null;

    /**
     * @var AfterConnectionEstablishedExtension[]
     */
    private array $afterConnectionEstablishedExtensions = [];

    /**
     * @var AfterConnectionClosedExtension[]
     */
    private array $afterConnectionClosedExtensions = [];

    /**
     * @var AfterPduConsumedExtension[]
     */
    private array $afterPduConsumedExtensions = [];

    /**
     * @var AfterPduProducedExtension[]
     */
    private array $afterPduProducedExtensions = [];

    /**
     * @psalm-param callable(ConnectionContext): Amp\Promise<Connection> $establisher
     */
    public function __construct(
        private ConnectionContext $context,
        private LoggerInterface $logger,
        private $establisher,
    ) {
    }

    /**
     * @psalm-param iterable<AfterConnectionEstablishedExtension|AfterConnectionClosedExtension|AfterPduConsumedExtension|AfterPduProducedExtension> $extensions
     */
    public function withExtensions(iterable $extensions): SmppExecutor
    {
        foreach ($extensions as $extension) {
            if ($extension instanceof AfterConnectionEstablishedExtension) {
                $this->afterConnectionEstablishedExtensions[] = $extension;
            }

            if ($extension instanceof AfterConnectionClosedExtension) {
                $this->afterConnectionClosedExtensions[] = $extension;
            }

            if ($extension instanceof AfterPduConsumedExtension) {
                $this->afterPduConsumedExtensions[] = $extension;
            }

            if ($extension instanceof AfterPduProducedExtension) {
                $this->afterPduProducedExtensions[] = $extension;
            }
        }

        return $this;
    }

    /**
     * @psalm-param callable(PDU, SmppExecutor): Amp\Promise<void> $onMessage
     *
     * @psalm-return Amp\Success<void>|Amp\Failure<\Throwable>
     */
    public function consume(callable $onMessage): Amp\Promise
    {
        /** @psalm-var Amp\Success<void>|Amp\Failure<\Throwable> */
        return Amp\call(function () use ($onMessage): \Generator {
            Consumer::new(yield $this->connect())
                ->onEachMessage(function (PDU $packet): Amp\Promise {
                    return Amp\call(function () use ($packet): \Generator {
                        foreach ($this->afterPduConsumedExtensions as $extension) {
                            yield $extension->afterPduConsumed($packet, $this);
                        }
                    });
                })
                ->listen($onMessage, $this);
        });
    }

    /**
     * @psalm-return Amp\Promise<int>
     */
    public function produce(PDU $packet): Amp\Promise
    {
        /** @psalm-var Amp\Promise<int> */
        return Amp\call(function () use ($packet): \Generator {
            $connection = yield $this->connect();

            if (Sequence::delegate()->overflow()) {
                Sequence::delegate()->reset();
            }

            $sequence = $packet->sequence();

            // If it's reply, then the sequence will be non-zero.
            if ($sequence === 0) {
                $sequence = yield Sequence::delegate()->next();
            }

            $packet = $packet->withSequence($sequence);

            yield $connection->write($packet);

            foreach ($this->afterPduProducedExtensions as $extension) {
                yield $extension->afterPduProduced($packet, $this);
            }

            return $sequence;
        });
    }

    /**
     * @psalm-return Amp\Promise<void>
     */
    public function fin(?\Throwable $e = null): Amp\Promise
    {
        /** @psalm-var Amp\Promise<void> */
        return Amp\call(function () use ($e): \Generator {
            $this->logger->log($e ? LogLevel::ERROR : LogLevel::DEBUG, 'Closing connection...', [
                'exception' => $e,
            ]);

            if ($this->connection?->isConnected() === true) {
                try {
                    yield $this->produce(new Unbind());

                    $this->connection->close();
                } catch (\Throwable $e) {
                    $this->logger->error($e->getMessage(), ['exception' => $e]);
                }
            }

            foreach ($this->afterConnectionClosedExtensions as $extension) {
                yield $extension->afterConnectionClosed($e ?? null);
            }

            if ($e !== null) {
                return new Amp\Failure($e);
            }
        });
    }

    /**
     * @psalm-return Amp\Promise<Connection>
     */
    public function reconnect(): Amp\Promise
    {
        /** @psalm-var Amp\Success<Connection> */
        return Amp\call(function (): \Generator {
            yield $this->fin();

            return yield $this->doConnect();
        });
    }

    /**
     * @psalm-return Amp\Promise<Connection>
     */
    private function connect(): Amp\Promise
    {
        /** @psalm-var Amp\Promise<Connection> */
        return Amp\call(function (): \Generator {
            if ($this->connection?->isConnected() === true) {
                return $this->connection;
            }

            return yield $this->doConnect();
        });
    }

    /**
     * @psalm-return Amp\Promise<Connection>
     */
    private function doConnect(): Amp\Promise
    {
        /** @psalm-var Amp\Promise<Connection> */
        return Amp\call(function (): \Generator {
            try {
                $this->connection = yield Amp\call($this->establisher, $this->context);
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);

                throw new ConnectionWasNotEstablished(message: $e->getMessage(), previous: $e);
            }

            foreach ($this->afterConnectionEstablishedExtensions as $extension) {
                yield $extension->afterConnectionEstablished($this);
            }

            return $this->connection;
        });
    }
}
