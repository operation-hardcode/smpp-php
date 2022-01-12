<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Transport;

use Amp;

interface Connection
{
    /**
     * @psalm-return Amp\Success<void>|Amp\Failure<\Throwable>
     */
    public function write(\Stringable $data): Amp\Promise;

    /**
     * @psalm-return Amp\Promise<string|null>
     */
    public function read(): Amp\Promise;
    public function close(): void;
    public function isConnected(): bool;

    /**
     * @param callable(): Amp\Promise<void> $invoke
     */
    public function onClose(callable $invoke): void;
}
