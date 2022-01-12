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
use OperationHardcode\Smpp\Interaction\Heartbeat\Heartbeat;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
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
    $logger = stdoutLogger('transceiver');

    $transceiver = Connector::connect()
        ->asTransceiver(
            ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'),
            $logger
        )
        ->withExtensions([
            new Heartbeat(
                Smpp\Time::fromSeconds(4),
                Smpp\Time::fromSeconds(2),
                $logger
            )
        ]);

    Amp\Loop::unreference(
        Amp\Loop::onSignal(\SIGINT, function () use ($transceiver): \Generator {
            yield $transceiver->fin();
        })
    );

    try {
        yield $transceiver->consume(function (PDU $pdu, SmppExecutor $executor): \Generator {
           if ($pdu instanceof Smpp\Protocol\Command\Replyable) {
                $reply = $pdu->reply();

                yield $executor->produce($reply);

                if ($pdu instanceof Smpp\Protocol\Command\DeliverSm) {
                    yield $executor->produce(new Smpp\Protocol\Command\SubmitSm($pdu->to, $pdu->from, 'Activate', $pdu->serviceType, 28));
                }
            }

            return new Amp\Success();
        });
    } catch (\Throwable $e) {
        echo $e->getMessage() . \PHP_EOL;

        Amp\Loop::stop();
    }
});
