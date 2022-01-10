<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp;

use Amp\Promise;
use Amp\Sync\LocalMutex;
use Amp\Sync\Mutex;
use function Amp\Sync\synchronized;

final class Sequence
{
    private const THRESHOLD = 0x7FFFFFFF;

    private static int $current = 0;
    private static Mutex $mutex;
    private static ?Sequence $seq = null;

    public static function delegate(?Mutex $mutex = null): Sequence
    {
        if (null === self::$seq) {
            self::$seq = new self($mutex ?: new LocalMutex());
        }

        return self::$seq;
    }

    public function overflow(): bool
    {
        return self::$current >= self::THRESHOLD;
    }

    public function current(): int
    {
        return self::$current;
    }

    /**
     * @psalm-return Promise<int>
     */
    public function next(): Promise
    {
        /** @var Promise<int> */
        return synchronized(self::$mutex, function (): int {
            return ++self::$current;
        });
    }

    public function reset(): void
    {
        self::$current = 0;
    }

    public function __wakeup(): void
    {
        throw new \RuntimeException();
    }

    private function __construct(Mutex $mutex)
    {
        self::$mutex = $mutex;
    }
    private function __clone(): void {}
}
