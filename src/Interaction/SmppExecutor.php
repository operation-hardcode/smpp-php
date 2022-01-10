<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction;

use Amp;
use OperationHardcode\Smpp\Protocol\Command\EnquireLink;
use OperationHardcode\Smpp\Protocol\Command\EnquireLinkResp;
use OperationHardcode\Smpp\Protocol\Command\Unbind;
use OperationHardcode\Smpp\Protocol\CommandStatus;
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
     * @var array<int, EnquireLinkResp|null>
     */
    private array $heartbeats = [];

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
            return Consumer::new(yield $this->connect())
                ->onEachMessage(function (PDU $pdu): void {
                    if ($pdu instanceof EnquireLinkResp) {
                        $this->heartbeats[$pdu->sequence()] = $pdu;
                    }
                })
                ->listen($onMessage, $this);
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

            if ($this->connection?->isConnected() === true) {
                try {
                    yield $this->produce((new Unbind())->withSequence(yield Sequence::delegate()->next()));

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
            yield $this->fin();

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

            $this->configureHeartbeat();

            return $this->connection;
        });
    }

    private function configureHeartbeat(): void
    {
        if ($this->context->heartbeatInterval !== null) {
            Amp\Loop::unreference(
                Amp\Loop::repeat($this->context->heartbeatInterval, function (): \Generator {
                    $sequence = yield Sequence::delegate()->next();

                    $this->logger->debug('Sending heartbeat with id "{id}".', [
                        'id' => $sequence,
                    ]);

                    yield $this->produce((new EnquireLink())->withSequence($sequence));

                    $this->heartbeats[$sequence] = null;

                    Amp\Loop::delay($this->context->heartbeatTimeout, function (string $watcherId) use ($sequence): \Generator {
                        if ($this->heartbeats[$sequence] === null || $this->heartbeats[$sequence]->status !== CommandStatus::ESME_ROK) {
                            $this->logger->debug('Response for heartbeat with id "{id}" was not received.', [
                                'id' => $sequence,
                            ]);

                            Amp\Loop::cancel($watcherId);

                            yield $this->fin();
                        }

                        unset($this->heartbeats[$sequence]);
                    });
                })
            );
        }
    }
}
