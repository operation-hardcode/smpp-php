<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction;

use Amp;
use OperationHardcode\Smpp\Protocol\CannotParseFrame;
use OperationHardcode\Smpp\Protocol\Command\EnquireLink;
use OperationHardcode\Smpp\Protocol\Command\GenericNack;
use OperationHardcode\Smpp\Protocol\Command\Unbind;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Transport\Connection;

final class Consumer
{
    /**
     * @var callable|null
     * @psalm-var (callable(PDU): (Amp\Promise<void>|void))|null
     */
    private $onEachMessageCallback = null;

    public function __construct(private Connection $connection)
    {
    }

    public static function new(Connection $connection): Consumer
    {
        return new Consumer($connection);
    }

    /**
     * @psalm-param callable(PDU): (Amp\Promise<void>|void) $callback
     */
    public function onEachMessage(callable $callback): Consumer
    {
        $this->onEachMessageCallback = $callback;

        return $this;
    }

    /**
     * @psalm-param callable(PDU, SmppExecutor): Amp\Promise<void> $onMessage
     *
     * @psalm-return Amp\Success<void>|Amp\Failure<\Throwable>
     */
    public function listen(callable $onMessage, SmppExecutor $executor): Amp\Promise
    {
        return Amp\call(function () use ($onMessage, $executor): \Generator {
            $running = true;

            $this->connection->onClose(function () use (&$running): void {
                $running = false;
            });

            while ($running && $this->connection->isConnected()) {
                if (null !== ($bytes = yield $this->connection->read())) {
                    try {
                        if (FrameParser::hasFrame($bytes)) {
                            /** @var PDU $pdu */
                            $pdu = FrameParser::parse($bytes);

                            if (null !== $this->onEachMessageCallback) {
                                Amp\asyncCall($this->onEachMessageCallback, $pdu);
                            }

                            Amp\asyncCall(match (get_class($pdu)) {
                                EnquireLink::class => fn (): Amp\Promise => $executor->produce($pdu->reply()),
                                Unbind::class => function () use ($executor, $pdu): \Generator {
                                    yield $executor->produce($pdu->reply());

                                    return $executor->fin();
                                },
                                default => fn (): \Generator|Amp\Promise => $onMessage($pdu, $executor),
                            });
                        }
                    } catch (CannotParseFrame $e) {
                        yield $executor->produce((new GenericNack($e->status))->withSequence($e->sequence));
                    }
                }
            }
        });
    }
}
