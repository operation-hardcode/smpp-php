<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp;

/**
 * @psalm-immutable
 */
final class Time
{
    /**
     * @psalm-param positive-int $duration
     */
    private function __construct(public readonly int $duration)
    {
    }

    public static function fromMilliseconds(int $milliseconds): Time
    {
        if ($milliseconds <= 0) {
            throw new \InvalidArgumentException('Time value must be greater or equal to zero.');
        }

        return new Time($milliseconds);
    }

    public static function fromSeconds(int $seconds): Time
    {
        if ($seconds <= 0) {
            throw new \InvalidArgumentException('Time value must be greater or equal to zero.');
        }

        return new Time($seconds * 1000);
    }
}
