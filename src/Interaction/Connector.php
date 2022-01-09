<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction;

use Amp;
use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Sequence;
use OperationHardcode\Smpp\Transport\AmpStreamConnection;
use OperationHardcode\Smpp\Transport\Connection;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Connector
{
    public static function asTransceiver(ConnectionContext $context, LoggerInterface $logger = new NullLogger()): SmppExecutor
    {
        return new SmppExecutor($context, $logger, function (ConnectionContext $context) use ($logger): Amp\Promise {
            return Amp\call(function () use ($context, $logger): \Generator {
                /** @var Connection $connection */
                $connection = yield AmpStreamConnection::new($context, $logger);

                yield $connection->write(
                    (new Command\BindTransceiver($context->systemId, $context->password))
                        ->withSequence(yield Sequence::delegate()->next())
                );

                try {
                    yield Smpp\establish($connection, Command\BindTransceiverResp::class, $context->establishTimeout);

                    $logger->debug('Connected as transceiver...');

                    return new Amp\Success($connection);
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

    public static function asReceiver(ConnectionContext $context, LoggerInterface $logger = new NullLogger()): SmppExecutor
    {
        return new SmppExecutor($context, $logger, function (ConnectionContext $context) use ($logger): Amp\Promise {
            return Amp\call(function () use ($context, $logger): \Generator {
                /** @var Connection $connection */
                $connection = yield AmpStreamConnection::new($context, $logger);

                yield $connection->write(
                    (new Command\BindReceiver($context->systemId, $context->password))
                        ->withSequence(yield Sequence::delegate()->next())
                );

                try {
                    yield Smpp\establish($connection, Command\BindReceiverResp::class, $context->establishTimeout);

                    $logger->debug('Connected as receiver...');

                    return new Amp\Success($connection);
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

    public static function asTransmitter(ConnectionContext $context, LoggerInterface $logger = new NullLogger()): SmppExecutor
    {
        return new SmppExecutor($context, $logger, function (ConnectionContext $context) use ($logger): Amp\Promise {
            return Amp\call(function () use ($context, $logger): \Generator {
                /** @var Connection $connection */
                $connection = yield AmpStreamConnection::new($context, $logger);

                yield $connection->write(
                    (new Command\BindTransmitter($context->systemId, $context->password))
                        ->withSequence(yield Sequence::delegate()->next())
                );

                try {
                    yield Smpp\establish($connection, Command\BindTransmitterResp::class, $context->establishTimeout);

                    $logger->debug('Connected as transmitter...');

                    return new Amp\Success($connection);
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
}
