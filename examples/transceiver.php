<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessorInterface;
use Monolog\Processor\PsrLogMessageProcessor;
use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\LoggerInterface;

/**
 * @param ProcessorInterface[] $processors
 */
function stdoutLogger(string $loggerName, array $processors = []): LoggerInterface
{
    $handler = new StreamHandler(Amp\ByteStream\getStdout());
    $handler->setFormatter(new ConsoleFormatter());

    return new Logger($loggerName, [$handler], [new PsrLogMessageProcessor(), new MemoryUsageProcessor(), new MemoryPeakUsageProcessor(), ...$processors]);
}

Amp\Loop::run(function (): \Generator {
    $transceiver = Connector::connect()->asTransceiver(
        ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'),
        stdoutLogger('transceiver'),
    );

    try {
        yield $transceiver->consume(function (PDU $pdu, Smpp\Interaction\SmppExecutor $executor): \Generator {
            yield $executor->produce((new Smpp\Protocol\Command\GenericNack(Smpp\Protocol\CommandStatus::ESME_RINVDSTTON))->withSequence($pdu->sequence()));

            var_dump($pdu);

            return new Amp\Success();
        });
    } catch (\Throwable $e) {
        echo $e->getMessage() . \PHP_EOL;

        Amp\Loop::stop();
    }
});
