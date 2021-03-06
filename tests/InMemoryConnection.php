<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests;

use Amp;
use OperationHardcode\Smpp\Transport\Connection;

final class InMemoryConnection implements Connection
{
    private bool $isConnected;
    private \SplQueue $bytes;
    public array $written = [];

    public function __construct(\SplQueue $bytes)
    {
        $this->isConnected = true;
        $this->bytes = $bytes;
    }

    public function write(\Stringable $data): Amp\Promise
    {
        $this->written[] = $data;

        return new Amp\Success();
    }

    public function read(): Amp\Promise
    {
        $data = '';

        if (!$this->bytes->isEmpty()) {
            $data = $this->bytes->dequeue();
        }

        if ($data instanceof Amp\Promise) {
            return $data;
        }

        return new Amp\Success($data);
    }

    public function close(): void
    {
        $this->isConnected = false;
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }
}
