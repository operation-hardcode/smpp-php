<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction;

use Amp;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Sequence;
use OperationHardcode\Smpp\Transport\AmpStreamConnection;
use OperationHardcode\Smpp\Transport\Connection;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @psalm-type ConnectionFactory = callable(ConnectionContext, LoggerInterface): Amp\Promise<Connection>
 */
final class Connector
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFn;

    /**
     * @psalm-param ConnectionFactory|null $connectionFn
     */
    private function __construct(?callable $connectionFn = null)
    {
        $this->connectionFn = $connectionFn ?: function (ConnectionContext $context, LoggerInterface $logger): Amp\Promise {
            return AmpStreamConnection::new($context, $logger);
        };
    }

    /**
     * @psalm-param ConnectionFactory|null $connectionFn
     */
    public static function connect(?callable $connectionFn = null): Connector
    {
        return new Connector($connectionFn);
    }

    public function asTransceiver(ConnectionContext $context, LoggerInterface $logger = new NullLogger()): SmppExecutor
    {
        return new SmppExecutor($context, $logger, function (ConnectionContext $context) use ($logger): Amp\Promise {
            /** @var Amp\Promise<Connection> */
            return Amp\call(function () use ($context, $logger): \Generator {
                $connection = yield Amp\call($this->connectionFn, $context, $logger);

                yield $connection->write(
                    (new Command\BindTransceiver($context->systemId, $context->password))
                        ->withSequence(yield Sequence::delegate()->next())
                );

                try {
                    yield $this->establish($connection, Command\BindTransceiverResp::class, $context->establishTimeout->duration);

                    $logger->debug('Connected as transceiver...');

                    return $connection;
                } catch (\Throwable $e) {
                    $logger->error('Disconnection from "{uri}" with error "{error}".', [
                        'uri' => $context->uri,
                        'error' => $e->getMessage(),
                        'exception' => $e,
                    ]);

                    $connection->close();

                    return new Amp\Failure($e);
                }
            });
        });
    }

    public function asReceiver(ConnectionContext $context, LoggerInterface $logger = new NullLogger()): SmppExecutor
    {
        return new SmppExecutor($context, $logger, function (ConnectionContext $context) use ($logger): Amp\Promise {
            /** @var Amp\Promise<Connection> */
            return Amp\call(function () use ($context, $logger): \Generator {
                $connection = yield Amp\call($this->connectionFn, $context, $logger);

                yield $connection->write(
                    (new Command\BindReceiver($context->systemId, $context->password))
                        ->withSequence(yield Sequence::delegate()->next())
                );

                try {
                    yield $this->establish($connection, Command\BindReceiverResp::class, $context->establishTimeout->duration);

                    $logger->debug('Connected as receiver...');

                    return $connection;
                } catch (\Throwable $e) {
                    $logger->error('Disconnection from "{uri}" with error "{error}".', [
                        'uri' => $context->uri,
                        'error' => $e->getMessage(),
                        'exception' => $e,
                    ]);

                    $connection->close();

                    return new Amp\Failure($e);
                }
            });
        });
    }

    public function asTransmitter(ConnectionContext $context, LoggerInterface $logger = new NullLogger()): SmppExecutor
    {
        return new SmppExecutor($context, $logger, function (ConnectionContext $context) use ($logger): Amp\Promise {
            /** @var Amp\Promise<Connection> */
            return Amp\call(function () use ($context, $logger): \Generator {
                $connection = yield Amp\call($this->connectionFn, $context, $logger);

                yield $connection->write(
                    (new Command\BindTransmitter($context->systemId, $context->password))
                        ->withSequence(yield Sequence::delegate()->next())
                );

                try {
                    yield $this->establish($connection, Command\BindTransmitterResp::class, $context->establishTimeout->duration);

                    $logger->debug('Connected as transmitter...');

                    return $connection;
                } catch (\Throwable $e) {
                    $logger->error('Disconnection from "{uri}" with error "{error}".', [
                        'uri' => $context->uri,
                        'error' => $e->getMessage(),
                        'exception' => $e,
                    ]);

                    $connection->close();

                    return new Amp\Failure($e);
                }
            });
        });
    }

    /**
     * @psalm-param class-string<Command\BindReceiverResp>|class-string<Command\BindTransceiverResp>|class-string<Command\BindTransmitterResp> $waitableResponse
     *
     * @psalm-return Amp\Success<void>|Amp\Failure<\Throwable>
     */
    private function establish(Connection $connection, string $waitableResponse, int $timeout): Amp\Promise
    {
        /** @psalm-var Amp\Success<void>|Amp\Failure<\Throwable>  */
        return Amp\Promise\timeout(Amp\call(function () use ($connection, $waitableResponse): \Generator {
            await:

            if (null !== ($bytes = yield $connection->read())) {
                if (FrameParser::hasFrame($bytes)) {
                    /** @var Command\BindReceiverResp|Command\BindTransmitterResp|Command\BindTransceiverResp $frame */
                    $frame = FrameParser::parse($bytes);

                    if ($frame instanceof $waitableResponse) {
                        if ($frame->commandStatus->is(CommandStatus::ESME_ROK())) {
                            return new Amp\Success();
                        }

                        return new Amp\Failure(new ConnectionWasNotEstablished(sprintf('Received command status "%s".', $frame->commandStatus->name)));
                    }

                    return new Amp\Failure(new ConnectionWasNotEstablished(sprintf('The command "%s" is not valid response, expected command "%s".', get_class($frame), $waitableResponse)));
                }
            }

            yield Amp\delay(1);

            goto await;
        }), $timeout);
    }
}
