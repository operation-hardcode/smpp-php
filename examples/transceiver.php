<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Interaction\Heartbeat\Heartbeat;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\NullLogger;

Amp\Loop::run(function (): \Generator {
    $logger = new NullLogger();

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

    try {
        yield $transceiver->consume(function (PDU $pdu, SmppExecutor $executor): \Generator {
           if ($pdu instanceof Smpp\Protocol\Command\Replyable) {
                $reply = $pdu->reply();

                yield $executor->produce($reply);

                if ($pdu instanceof Smpp\Protocol\Command\DeliverSm) {
                    yield $executor->produce(new Smpp\Protocol\Command\SubmitSm($pdu->to, $pdu->from, new Smpp\Protocol\Message\Utf8Message('Hello'), $pdu->serviceType, Smpp\Protocol\EsmeClass::STORE_AND_FORWARD));
                }
            }

            return new Amp\Success();
        });

        echo "Send [Ctrl+C] to quit".\PHP_EOL;
    } catch (\Throwable $e) {
        echo $e->getMessage() . \PHP_EOL;

        Amp\Loop::stop();
    }

    Amp\Loop::unreference(
        Amp\Loop::onSignal(\SIGINT, function () use ($transceiver): \Generator {
            yield $transceiver->fin();
        })
    );
});
