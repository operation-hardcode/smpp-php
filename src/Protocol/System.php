<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

// System type.
enum System: string
{
    case WWW = 'WWW';
    case UNKNOWN = 'UNKNOWN';
}
