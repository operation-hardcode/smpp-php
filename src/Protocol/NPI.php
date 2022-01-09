<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

// Numeric Plan Indicator
enum NPI: int
{
    case UNKNOWN = 00000000;
    case ISDN = 00000001;
    case DATA = 00000011;
    case TELEX = 00000100;
    case LAND_MOBILE = 00000110;
    case NATIONAL = 00001000;
    case PRIVATE = 00001001;
    case ERMES = 00001010;
    case INTERNET = 00001110;
    case WAP = 00010010;

    public static function try(int|string $value, NPI $default = NPI::ISDN): NPI
    {
        return self::tryFrom($value) ?: $default;
    }
}
