<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
use OperationHardcode\Smpp\Protocol\Command\SubmitSm;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\NullLogger;

Amp\Loop::run(function (): \Generator {
    $transmitter = Connector::connect()
        ->asTransmitter(ConnectionContext::default(uri: '127.0.0.1:8090', systemId: '900238', password: 'c58775'), new NullLogger());

    try {
        yield $transmitter->produce(new SubmitSm(new Destination('25201'), new Destination('xxxxx'), 'Hello, world'));

        yield $transmitter->consume(function (PDU $pdu, SmppExecutor $executor) {
            var_dump($pdu);

            yield $executor->fin();
        });
    } catch (\Throwable $e) {
        dump($e->getMessage());
    }
});
