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
     * @var array<int, ?EnquireLinkResp>
     */
    private array $heartbeats = [];

    public function __construct(
        private Time $interval,
        private Time $timeout,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function afterConnectionEstablished(SmppExecutor $smppExecutor): Amp\Promise
    {
        return Amp\call(function () use ($smppExecutor): void {
            $this->id = Amp\Loop::repeat($this->interval->duration, function () use ($smppExecutor): \Generator {
                /** @var int $sequence */
                $sequence = yield $smppExecutor->produce(new EnquireLink());

                $this->logger->debug('Sending heartbeat with id "{id}".', [
                    'id' => $sequence,
                ]);

                $this->heartbeats[$sequence] = null;

                Amp\Loop::unreference(
                    Amp\Loop::delay($this->timeout->duration, function () use ($sequence, $smppExecutor): \Generator {
                        if ($this->heartbeats[$sequence]?->status !== CommandStatus::ESME_ROK) {
                            $this->logger->error('Response for heartbeat with id "{id}" was not received.', [
                                'id' => $sequence,
                            ]);

                            $this->cancel();

                            yield $smppExecutor->fin(new EnquireConnectionTimeoutException());
                        }

                        unset($this->heartbeats[$sequence]);
                    })
                );
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    public function afterPduConsumed(PDU $pdu, SmppExecutor $smppExecutor): Amp\Promise
    {
        return Amp\call(function () use ($pdu): void {
            if ($pdu instanceof EnquireLinkResp) {
                $this->heartbeats[$pdu->sequence()] = $pdu;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function afterConnectionClosed(?\Throwable $e = null): Amp\Promise
    {
        $this->cancel();

        return new Amp\Success();
    }

    private function cancel(): void
    {
        [$id, $this->id] = [$this->id, null];

        if ($id !== null) {
            Amp\Loop::cancel($id);
        }
    }
}
