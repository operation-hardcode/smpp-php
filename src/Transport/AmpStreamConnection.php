<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Transport;

use Amp;
use Amp\Socket\ConnectContext;
use Amp\Socket\EncryptableSocket;
use Psr\Log\LoggerInterface;
use function Amp\Socket\connect;

final class AmpStreamConnection implements Connection
{
    private bool $isConnected = false;

    /**
     * @var callable
     * @psalm-var (callable(): Amp\Promise<void>)|null
     */
    private $invokeOnDisconnect = null;

    private function __construct(private EncryptableSocket $socket)
    {
    }

    /**
     * @psalm-return Amp\Promise<Connection>
     */
    public static function new(ConnectionContext $connectionContext, LoggerInterface $logger): Amp\Promise
    {
        /** @var Amp\Promise<Connection> */
        return Amp\call(static function () use ($connectionContext, $logger): \Generator {
            $context = new ConnectContext();

            if ($connectionContext->timeout > 0) {
                $context = $context->withConnectTimeout($connectionContext->timeout);
            }

            if ($connectionContext->attempts > 0) {
                $context = $context->withMaxAttempts($connectionContext->attempts);
            }

            if ($connectionContext->noDelay) {
                $context = $context->withTcpNoDelay();
            }

            $context = $context->withTlsContext($connectionContext->clientTlsContext);

            $connection = new AmpStreamConnection(yield connect($connectionContext->uri, $context));
            $connection->isConnected = true;

            $logger->debug('Connection to {uri} was established successful.', [
                'uri' => $connectionContext->uri,
            ]);

            return $connection;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function write(\Stringable $data): Amp\Promise
    {
        /** @var Amp\Failure<\Throwable>|Amp\Success<void> */
        return $this->socket->write((string) $data);
    }

    /**
     * {@inheritdoc}
     */
    public function read(): Amp\Promise
    {
        return $this->socket->read();
    }

    public function close(): void
    {
        if ($this->isConnected) {
            if ($this->invokeOnDisconnect !== null) {
                Amp\asyncCall($this->invokeOnDisconnect);
            }

            $this->socket->close();
            $this->isConnected = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(callable $invoke): void
    {
        $this->invokeOnDisconnect = $invoke;
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }
}
