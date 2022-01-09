<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use OperationHardcode\Smpp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Protocol\Command\SubmitSm;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Transport\ConnectionContext;

Amp\Loop::run(function (): \Generator {
    $transmitter = Connector::asTransmitter(
        ConnectionContext::default(uri: 'smscsim.melroselabs.com:2775', systemId: '900238', password: 'c58775'),
        Smpp\stdoutLogger('publisher'),
    );

    yield $transmitter->produce(new SubmitSm(new Destination('25201'), new Destination('79013533169'), 'Hello, world'));
    yield $transmitter->fin();
});
