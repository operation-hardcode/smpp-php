<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\NullLogger;

Amp\Loop::run(function (): \Generator {
    $executor = Connector::connect()
        ->asReceiver(ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'), new NullLogger());

    try {
        yield $executor->consume(function (PDU $pdu, Smpp\Interaction\SmppExecutor $executor): \Generator {
            var_dump($pdu);

            yield $executor->fin();
        });
    } catch (\Throwable $e) {
        if ($e instanceof Amp\TimeoutException) {
            yield $executor->fin();

            Amp\Loop::stop();
        }
    }
});
