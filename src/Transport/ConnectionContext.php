<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Transport;

use Amp\Socket\ClientTlsContext;

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
     * Period in which producer should send the enquire link command (in milliseconds).
     */
    public readonly ?int $heartbeatInterval;

    /**
     * Timeout during which we are waiting for a successful connection confirmation (in milliseconds).
     */
    public readonly int $heartbeatTimeout;

    /**
     * Timeout within which the SMSC must return current connection mode response: bind receiver, bind transmitter or bind transceiver response (in milliseconds).
     */
    public readonly int $establishTimeout;

    public function __construct(
        string $systemId,
        string $password,
        string $uri,
        int $timeout,
        int $attempts,
        bool $noDelay,
        ClientTlsContext $clientTlsContext,
        int $establishTimeout = 10000,
        ?int $heartbeatInterval = null,
        int $heartbeatTimeout = 20000
    ) {
        $this->systemId = $systemId;
        $this->password = $password;
        $this->uri = $uri;
        $this->timeout = $timeout;
        $this->attempts = $attempts;
        $this->noDelay = $noDelay;
        $this->clientTlsContext = $clientTlsContext;
        $this->establishTimeout = $establishTimeout;
        $this->heartbeatInterval = $heartbeatInterval;
        $this->heartbeatTimeout = $heartbeatTimeout;
    }

    public static function default(string $uri, string $systemId, string $password, int $establishTimeout = 10000, ?int $heartbeatInterval = null, int $heartbeatTimeout = 20000): ConnectionContext
    {
        return new ConnectionContext(
            $systemId,
            $password,
            $uri,
            0,
            0,
            false,
            (new ClientTlsContext(''))->withoutPeerVerification(),
            $establishTimeout,
            $heartbeatInterval,
            $heartbeatTimeout,
        );
    }
}
