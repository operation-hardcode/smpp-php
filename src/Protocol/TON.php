<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

// Type Of Number (phone)
enum TON: int
{
    case UNKNOWN = 00000000;
    case INTERNATIONAL = 00000001;
    case NATIONAL = 00000010;
    case NETWORK_SPECIFIC = 00000011;
    case SUBSCRIBER_NUMBER = 00000100;
    case ALPHANUMERIC = 00000101;
    case ABBREVIATED = 00000110;

    public static function try(int|string $value, TON $default = TON::INTERNATIONAL): TON
    {
        return self::tryFrom($value) ?: $default;
    }
}
