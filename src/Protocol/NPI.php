<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

enum NPI: int
{
    case UNKNOWN = 0;
    case ISDN = 1;
    case DATA = 3;
    case TELEX = 4;
    case LAND_MOBILE = 6;
    case NATIONAL = 8;
    case PRIVATE = 9;
    case ERMES = 10;
    case INTERNET = 14;
    case WAP = 18;

    public static function try(int|string $value, NPI $default = NPI::ISDN): NPI
    {
        return self::tryFrom($value) ?: $default;
    }
}
