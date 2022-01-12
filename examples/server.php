<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Amp\Socket\ResourceSocket;
use Amp\Socket\Server;
use Amp\Loop;
use OperationHardcode\Smpp\Protocol\Command\BindTransceiver;
use OperationHardcode\Smpp\Protocol\Command\BindTransceiverResp;
use OperationHardcode\Smpp\Protocol\Command\SubmitSm;
use OperationHardcode\Smpp\Protocol\Command\SubmitSmResp;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\FrameParser;
use function Amp\asyncCoroutine;

Loop::run(static function () {
    $clientHandler = asyncCoroutine(function (ResourceSocket $socket): \Generator {
        $data = yield $socket->read();

        $frame = FrameParser::parse($data);

        if ($frame instanceof BindTransceiver) {
            yield $socket->write((string) (new BindTransceiverResp($frame->systemId))->withSequence($frame->sequence()));
        }

        if ($frame instanceof SubmitSm) {
            yield $socket->write((string) (new SubmitSmResp())->withSequence($frame->sequence()));
        }

        yield $socket->write((string) (new SubmitSm(new Destination('25201'), new Destination('xxxxx'), 'Hello, world'))->withSequence(2));
    });

    $server = Server::listen('127.0.0.1:8090');

    while ($socket = yield $server->accept()) {
        $clientHandler($socket);
    }
});
