<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

enum TON: int
{
    case UNKNOWN = 0;
    case INTERNATIONAL = 1;
    case NATIONAL = 2;
    case NETWORK_SPECIFIC = 3;
    case SUBSCRIBER_NUMBER = 4;
    case ALPHANUMERIC = 5;
    case ABBREVIATED = 6;

    public static function try(int|string $value, TON $default = TON::INTERNATIONAL): TON
    {
        return self::tryFrom($value) ?: $default;
    }
}
