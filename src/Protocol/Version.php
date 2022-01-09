<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

// Protocol version.
enum Version: int
{
    case V_34 = 0x34;
}
