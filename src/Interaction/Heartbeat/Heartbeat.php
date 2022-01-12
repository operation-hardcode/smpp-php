<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction\Heartbeat;

use Amp;
use OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionClosedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionEstablishedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterPduConsumedExtension;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
use OperationHardcode\Smpp\Protocol\Command\EnquireLink;
use OperationHardcode\Smpp\Protocol\Command\EnquireLinkResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Time;
use Psr\Log\LoggerInterface;

final class Heartbeat implements
    AfterConnectionEstablishedExtension,
    AfterConnectionClosedExtension,
    AfterPduConsumedExtension
{
    private ?string $id = null;

    /**
     * @var array<int, EnquireLinkResp|null>
     */
    private array $heartbeats = [];

    public function __construct(
        private Time $interval,
        private Time $timeout,
        private LoggerInterface $logger,
    ) {
    }

    public function afterConnectionEstablished(SmppExecutor $smppExecutor): Amp\Promise
    {
        return Amp\call(function () use ($smppExecutor): void {
            $this->id = Amp\Loop::repeat($this->interval->duration, function () use ($smppExecutor): \Generator {
                $sequence = yield $smppExecutor->produce(new EnquireLink());

                $this->logger->debug('Sending heartbeat with id "{id}".', [
                    'id' => $sequence,
                ]);

                $this->heartbeats[$sequence] = null;

                Amp\Loop::delay($this->timeout->duration, function (string $watcherId) use ($sequence, $smppExecutor): \Generator {
                    if ($this->heartbeats[$sequence] === null || $this->heartbeats[$sequence]->status !== CommandStatus::ESME_ROK) {
                        $this->logger->debug('Response for heartbeat with id "{id}" was not received.', [
                            'id' => $sequence,
                        ]);

                        Amp\Loop::cancel($watcherId);
                        Amp\Loop::cancel($this->id);

                        yield $smppExecutor->fin();
                    }

                    unset($this->heartbeats[$sequence]);
                });
            });
        });
    }

    public function afterPduConsumed(PDU $pdu, SmppExecutor $smppExecutor): Amp\Promise
    {
        return Amp\call(function () use ($pdu, $smppExecutor): void {
            if ($pdu instanceof EnquireLinkResp) {
                $this->heartbeats[$pdu->sequence()] = $pdu;
            }
        });
    }

    public function afterConnectionClosed(?\Throwable $e = null): Amp\Promise
    {
        if ($this->id !== null) {
            Amp\Loop::cancel($this->id);
        }

        return new Amp\Success();
    }
}
