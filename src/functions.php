<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp;

use Amp;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use OperationHardcode\Smpp\Interaction\ConnectionWasNotEstablished;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Transport\Connection;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;
use Monolog\Processor\ProcessorInterface;

/**
 * @psalm-param class-string<Command\BindReceiverResp>|class-string<Command\BindTransceiverResp>|class-string<Command\BindTransmitterResp> $waitableResponse
 *
 * @psalm-return Amp\Success<void>|Amp\Failure<\Throwable>
 */
function establish(Connection $connection, string $waitableResponse, int $timeout): Amp\Promise
{
    /** @psalm-var Amp\Success<void>|Amp\Failure<\Throwable>  */
    return Amp\Promise\timeout(Amp\call(function () use ($connection, $waitableResponse): \Generator {
        await:

        if (null !== ($bytes = yield $connection->read())) {
            if (FrameParser::hasFrame($bytes)) {
                /** @var Command\BindReceiverResp|Command\BindTransmitterResp|Command\BindTransceiverResp $frame */
                $frame = FrameParser::parse($bytes);

                if ($frame instanceof $waitableResponse) {
                    if ($frame->commandStatus === CommandStatus::ESME_ROK) {
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

/**
 * @param ProcessorInterface[] $processors
 */
function stdoutLogger(string $loggerName, array $processors = []): LoggerInterface
{
    $handler = new StreamHandler(Amp\ByteStream\getStdout());
    $handler->setFormatter(new ConsoleFormatter());

    return new Logger($loggerName, [$handler], [new PsrLogMessageProcessor(), new MemoryUsageProcessor(), new MemoryPeakUsageProcessor(), ...$processors]);
}
