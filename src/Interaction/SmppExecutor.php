<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction;

use Amp;
use OperationHardcode\Smpp\Protocol\Command\Unbind;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Sequence;
use OperationHardcode\Smpp\Transport\Connection;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\LoggerInterface;

final class SmppExecutor
{
    /**
     * @psalm-var (callable(SmppExecutor): (void|Amp\Promise<void>))[]
     */
    private array $onConnectCallbacks = [];

    /**
     * @psalm-var (callable(SmppExecutor): (void|Amp\Promise<void>))[]
     */
    private array $onShutdownCallbacks = [];
    private ?Connection $connection = null;

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
     * @psalm-param callable(SmppExecutor): (void|Amp\Promise<void>) $fn
     */
    public function onConnect(callable $fn): SmppExecutor
    {
        $this->onConnectCallbacks[] = $fn;

        return $this;
    }

    /**
     * @psalm-param callable(SmppExecutor): (void|Amp\Promise<void>) $fn
     */
    public function onShutdown(callable $fn): SmppExecutor
    {
        $this->onShutdownCallbacks[] = $fn;

        return $this;
    }

    /**
     * @psalm-param callable(PDU, SmppExecutor): Amp\Promise<void> $onMessage
     *
     * @psalm-return Amp\Success<void>|Amp\Failure<\Throwable>
     */
    public function consume(callable $onMessage): Amp\Promise
    {
        return Amp\call(function () use ($onMessage): \Generator {
            return Consumer::new(yield $this->connect())->listen($onMessage, $this);
        });
    }

    /**
     * @psalm-return Amp\Success<void>|Amp\Failure<\Throwable>
     */
    public function produce(PDU $packet): Amp\Promise
    {
        return Amp\call(function () use ($packet): \Generator {
            /** @var Connection $connection */
            $connection = yield $this->connect();

            if (Sequence::delegate()->overflow()) {
                Sequence::delegate()->reset();

                /** @var Connection $connection */
                $connection = yield $this->reconnect();
            }

            yield $connection->write($packet);
        });
    }

    /**
     * @psalm-return Amp\Promise<void>
     */
    public function fin(): Amp\Promise
    {
        return Amp\call(function (): \Generator {
            $this->logger->debug('Closing connection...');

            foreach ($this->onShutdownCallbacks as $fn) {
                Amp\asyncCall($fn, $this);
            }

            yield $this->produce((new Unbind())->withSequence(yield Sequence::delegate()->next()));

            if ($this->connection?->isConnected() === true) {
                try {
                    $this->connection->close();
                } catch (\Throwable $e) {
                    $this->logger->error($e->getMessage(), ['exception' => $e]);
                }
            }
        });
    }

    /**
     * @psalm-return Amp\Success<Connection>|Amp\Failure<\Throwable>
     */
    private function connect(): Amp\Promise
    {
        return Amp\call(function (): \Generator {
            if ($this->connection?->isConnected() === true) {
                return $this->connection;
            }

            return yield $this->doConnect();
        });
    }

    /**
     * @psalm-return Amp\Success<Connection>|Amp\Failure<\Throwable>
     */
    private function reconnect(): Amp\Promise
    {
        return Amp\call(function (): \Generator {
            $this->connection?->close();

            return yield $this->doConnect();
        });
    }

    /**
     * @psalm-return Amp\Success<Connection>|Amp\Failure<\Throwable>
     */
    private function doConnect(): Amp\Promise
    {
        return Amp\call(function (): \Generator {
            try {
                $this->connection = yield Amp\call($this->establisher, $this->context);
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage(), ['exception' => $e]);

                throw new ConnectionWasNotEstablished($e->getMessage(), $e->getCode(), $e);
            }

            foreach ($this->onConnectCallbacks as $fn) {
                Amp\asyncCall($fn, $this);
            }

            return $this->connection;
        });
    }
}
