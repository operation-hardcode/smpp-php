<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests;

use Amp;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Interaction\Heartbeat\EnquireConnectionTimeoutException;
use OperationHardcode\Smpp\Interaction\Heartbeat\Heartbeat;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
use OperationHardcode\Smpp\Protocol\Command\BindTransceiverResp;
use OperationHardcode\Smpp\Protocol\Command\EnquireLinkResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Tests\Tools\MonitorConnection;
use OperationHardcode\Smpp\Time;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\NullLogger;

final class HeartbeatTest extends SmppTestCase
{
    public function testHeartbeatCloseTheConnectionAfterTimeout(): void
    {
        Amp\Loop::run(function (): \Generator {
            $connector = Connector::connect(function (): Amp\Promise {
                $bytes = new \SplQueue();
                $bytes->enqueue((string) (new BindTransceiverResp('3333', CommandStatus::ESME_ROK()))->withSequence(1));
                $bytes->enqueue(new Amp\Delayed(20));

                return new Amp\Success(new InMemoryConnection($bytes));
            });

            $logger = new NullLogger();

            $exception = null;

            $executor = $connector
                ->asTransceiver(ConnectionContext::default(uri: 'tcp://test:2225', systemId: '3333', password: 'secret'), $logger)
                ->withExtensions([
                    new Heartbeat(
                        Time::fromMilliseconds(2),
                        Time::fromMilliseconds(10),
                        $logger,
                    ),
                    new MonitorConnection(onDisconnection: function (\Throwable $e) use (&$exception): void {
                        $exception = $e;
                    })
                ]);

            yield $executor->consume(function (PDU $pdu, SmppExecutor $smppExecutor): void {
                //
            });

            yield Amp\delay(30);

            Amp\Loop::stop();

            self::assertInstanceOf(EnquireConnectionTimeoutException::class, $exception);
        });
    }

    public function testHeartbeatsDoNotInterruptExecutorIfResponseComesInTime(): void
    {
        Amp\Loop::run(function (): \Generator {
            $connector = Connector::connect(function (): Amp\Promise {
                $bytes = new \SplQueue();
                $bytes->enqueue((string) (new BindTransceiverResp('3333', CommandStatus::ESME_ROK()))->withSequence(1));
                $bytes->enqueue((string) (new EnquireLinkResp(CommandStatus::ESME_ROK()))->withSequence(2));

                return new Amp\Success(new InMemoryConnection($bytes));
            });

            $logger = new NullLogger();

            $exception = null;

            $executor = $connector
                ->asTransceiver(ConnectionContext::default(uri: 'tcp://test:2225', systemId: '3333', password: 'secret'), $logger)
                ->withExtensions([
                    new Heartbeat(
                        Time::fromMilliseconds(2),
                        Time::fromMilliseconds(10),
                        $logger,
                    ),
                    new MonitorConnection(onDisconnection: function (?\Throwable $e = null) use (&$exception): void {
                        $exception = $e;
                    })
                ]);

            $command = null;

            yield $executor->consume(function (PDU $pdu, SmppExecutor $smppExecutor) use (&$command): \Generator {
                $command = $pdu;
                yield $smppExecutor->fin();
            });

            self::assertNull($exception);
            self::assertInstanceOf(EnquireLinkResp::class, $command);
            self::assertEquals(CommandStatus::ESME_ROK(), $command->status);
        });
    }
}
