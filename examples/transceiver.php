<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\ConnectionContext;

Amp\Loop::run(function (): \Generator {
    $transceiver = Connector::asTransceiver(
        ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '216629', password: '64e610'),
        Smpp\stdoutLogger('transceiver'),
    );

    try {
        yield $transceiver->consume(function (PDU $pdu, Smpp\Interaction\SmppExecutor $executor): Amp\Promise {
            return new Amp\Success();
        });
    } catch (\Throwable $e) {
        echo $e->getMessage() . \PHP_EOL;

        Amp\Loop::stop();
    }
});
