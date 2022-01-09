<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\ConnectionContext;

Amp\Loop::run(function (): \Generator {
    $executor = Connector::asReceiver(
        ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'),
        Smpp\stdoutLogger('receiver')
    )->onConnect(function (): void {
        dump('Connected successful...');
    })->onShutdown(function (): void {
        dump('Disconnected successful...');
    });

    try {
        yield $executor->consume(function (PDU $pdu, Smpp\Interaction\SmppExecutor $executor) {
            return new Amp\Success();
        });
    } catch (\Throwable $e) {
        if ($e instanceof Amp\TimeoutException) {
            yield $executor->fin();

            Amp\Loop::stop();
        }
    }
});
