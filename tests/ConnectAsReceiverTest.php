<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests;

use Amp;
use OperationHardcode\Smpp\Interaction\ConnectionWasNotEstablished;
use OperationHardcode\Smpp\Interaction\Connector;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
use OperationHardcode\Smpp\Protocol\Command\BindReceiverResp;
use OperationHardcode\Smpp\Protocol\Command\DeliverSm;
use OperationHardcode\Smpp\Protocol\Command\Outbind;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\EsmeClass;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Protocol\TON;
use OperationHardcode\Smpp\Transport\ConnectionContext;
use Psr\Log\NullLogger;

final class ConnectAsReceiverTest extends SmppTestCase
{
    public function testConnectionSuccess(): void
    {
        Amp\Loop::run(function (): \Generator {
            $connector = Connector::connect(function (): Amp\Promise {
                $bytes = new \SplQueue();
                $bytes->enqueue((string) (new BindReceiverResp('3333'))->withSequence(1));

                return new Amp\Success(new InMemoryConnection($bytes));
            });

            $connected = false;
            $disconnected = false;

            $executor = $connector
                ->asReceiver(ConnectionContext::default('tcp://test:2225', '3333', 'secret'), new NullLogger())
                ->onConnect(function (SmppExecutor $executor) use (&$connected): Amp\Promise {
                    $connected = true;

                    return Amp\call(function () use ($executor): \Generator {
                        yield $executor->fin();
                    });
                })
                ->onShutdown(function () use (&$disconnected): void {
                    $disconnected = true;
                })
            ;

            yield $executor->consume(function (PDU $pdu, SmppExecutor $executor): void {
                //
            });

            self::assertTrue($connected);
            self::assertTrue($disconnected);
        });
    }

    public function testNotEsmeRokStatusWasReceived(): void
    {
        Amp\Loop::run(function (): \Generator {
            $connector = Connector::connect(function (): Amp\Promise {
                $bytes = new \SplQueue();
                $bytes->enqueue((string) (new BindReceiverResp('3333', CommandStatus::ESME_RBINDFAIL))->withSequence(1));

                return new Amp\Success(new InMemoryConnection($bytes));
            });

            $executor = $connector->asReceiver(ConnectionContext::default('tcp://test:2225', '3333', 'secret'), new NullLogger());

            try {
                yield $executor->consume(function (PDU $pdu, SmppExecutor $executor): void {
                    //
                });

                self::fail('Connection was established, but should not.');
            } catch (ConnectionWasNotEstablished $e) {
                self::assertEquals('Received command status "ESME_RBINDFAIL".', $e->getMessage());
            }
        });
    }

    public function testNotBindReceiverRespCommandWasReceived(): void
    {
        Amp\Loop::run(function (): \Generator {
            $connector = Connector::connect(function (): Amp\Promise {
                $bytes = new \SplQueue();
                $bytes->enqueue((string) (new Outbind('3333'))->withSequence(1));

                return new Amp\Success(new InMemoryConnection($bytes));
            });

            $executor = $connector->asReceiver(ConnectionContext::default('tcp://test:2225', '3333', 'secret'), new NullLogger());

            try {
                yield $executor->consume(function (PDU $pdu, SmppExecutor $executor): void {
                    //
                });

                self::fail('Connection was established, but should not.');
            } catch (ConnectionWasNotEstablished $e) {
                self::assertEquals('The command "OperationHardcode\Smpp\Protocol\Command\Outbind" is not valid response, expected command "OperationHardcode\Smpp\Protocol\Command\BindReceiverResp".', $e->getMessage());
            }
        });
    }

    public function testConnectionWasNotEstablishedDueToTimeout(): void
    {
        Amp\Loop::run(function (): \Generator {
            $connector = Connector::connect(function (): Amp\Promise {
                $bytes = new \SplQueue();
                $bytes->enqueue(new Amp\Delayed(20));

                return new Amp\Success(new InMemoryConnection($bytes));
            });

            $executor = $connector->asReceiver(ConnectionContext::default(uri: 'tcp://test:2225', systemId: '3333', password: 'secret', establishTimeout: 10), new NullLogger());

            try {
                yield $executor->consume(function (PDU $pdu, SmppExecutor $executor): void {
                    //
                });

                self::fail('Connection was established, but should not.');
            } catch (ConnectionWasNotEstablished $e) {
                self::assertEquals('Operation timed out', $e->getMessage());
                self::assertInstanceOf(Amp\TimeoutException::class, $e->getPrevious());
            }
        });
    }

    public function testMessageWasConsumed(): void
    {
        Amp\Loop::run(function (): \Generator {
            $connector = Connector::connect(function (): Amp\Promise {
                $bytes = new \SplQueue();
                $bytes->enqueue((string) (new BindReceiverResp('3333'))->withSequence(1));
                $bytes->enqueue((string) (new DeliverSm(new Destination('0001'), new Destination('1000'), 'Hello from test.'))->withSequence(2));

                return new Amp\Success(new InMemoryConnection($bytes));
            });

            $executor = $connector->asReceiver(ConnectionContext::default(uri: 'tcp://test:2225', systemId: '3333', password: 'secret'), new NullLogger());

            $command = null;

            yield $executor->consume(function (PDU $pdu, SmppExecutor $executor) use (&$command): \Generator {
                $command = $pdu;
                yield $executor->fin();
            });

            self::assertInstanceOf(DeliverSm::class, $command);
            self::assertEquals('0001', $command->from->value);
            self::assertEquals(TON::INTERNATIONAL, $command->from->ton);
            self::assertEquals(NPI::ISDN, $command->from->npi);
            self::assertEquals('1000', $command->to->value);
            self::assertEquals(TON::INTERNATIONAL, $command->to->ton);
            self::assertEquals(NPI::ISDN, $command->to->npi);
            self::assertEquals('Hello from test.', $command->message);
            self::assertEquals(EsmeClass::STORE_AND_FORWARD, $command->esmeClass);
            self::assertEquals(0, $command->protocolId);
            self::assertEquals(0, $command->priority);
            self::assertEquals(0, $command->registeredDeliveryFlag);
            self::assertEquals(0, $command->dataCoding);
        });
    }
}