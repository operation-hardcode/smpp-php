<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Transport;

use Amp\Socket\ClientTlsContext;
use OperationHardcode\Smpp\Time;

final class ConnectionContext
{
    /**
     * The system id provided by SMSC.
     */
    public readonly string $systemId;

    /**
     * The password provided by SMSC.
     */
    public readonly string $password;

    /**
     * Uri where connection should be established.
     */
    public readonly string $uri;

    /**
     * Connection timeout (in milliseconds).
     */
    public readonly int $timeout;
    public readonly int $attempts;
    public readonly bool $noDelay;
    public readonly ClientTlsContext $clientTlsContext;

    /**
     * Timeout within which the SMSC must return current connection mode response: bind receiver, bind transmitter or bind transceiver response (in milliseconds).
     */
    public readonly Time $establishTimeout;

    public function __construct(
        string $systemId,
        string $password,
        string $uri,
        int $timeout,
        int $attempts,
        bool $noDelay,
        ClientTlsContext $clientTlsContext,
        Time $establishTimeout,
    ) {
        $this->systemId = $systemId;
        $this->password = $password;
        $this->uri = $uri;
        $this->timeout = $timeout;
        $this->attempts = $attempts;
        $this->noDelay = $noDelay;
        $this->clientTlsContext = $clientTlsContext;
        $this->establishTimeout = $establishTimeout;
    }

    public static function default(string $uri, string $systemId, string $password, Time|int $establishTimeout = 10000): ConnectionContext
    {
        if (\is_int($establishTimeout)) {
            $establishTimeout = Time::fromMilliseconds($establishTimeout);
        }

        return new ConnectionContext(
            $systemId,
            $password,
            $uri,
            0,
            0,
            false,
            (new ClientTlsContext(''))->withoutPeerVerification(),
            $establishTimeout,
        );
    }
}
