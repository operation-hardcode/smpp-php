<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Tools;

use Amp;
use OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionClosedExtension;
use OperationHardcode\Smpp\Interaction\Extensions\AfterConnectionEstablishedExtension;
use OperationHardcode\Smpp\Interaction\SmppExecutor;

final class MonitorConnection implements AfterConnectionEstablishedExtension, AfterConnectionClosedExtension
{
    /**
     * @var callable(SmppExecutor): Amp\Promise<void>
     */
    private $onConnection;

    /**
     * @var callable(?\Throwable): Amp\Promise<void>
     */
    private $onDisconnection;

    /**
     * @psalm-param callable(SmppExecutor): Amp\Promise<void> $onConnection
     * @psalm-param callable(?\Throwable): Amp\Promise<void> $onDisconnection
     */
    public function __construct(callable $onConnection, callable $onDisconnection)
    {
        $this->onConnection = $onConnection;
        $this->onDisconnection = $onDisconnection;
    }

    /**
     * {@inheritdoc}
     */
    public function afterConnectionEstablished(SmppExecutor $smppExecutor): Amp\Promise
    {
        return Amp\call($this->onConnection, $smppExecutor);
    }

    /**
     * {@inheritdoc}
     */
    public function afterConnectionClosed(?\Throwable $e = null): Amp\Promise
    {
        return Amp\call($this->onDisconnection, $e);
    }
}
