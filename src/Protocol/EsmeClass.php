<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

enum EsmeClass: int
{
    case STORE_AND_FORWARD = 0x00;
    case DATAGRAM = 0x01;
    case FORWARD = 0x10;
    case UNKNOWN = 0x99;
}
