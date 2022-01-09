<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class GenericNack extends PDU
{
    public function __construct(public readonly CommandStatus $status)
    {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        return new GenericNack($status);
    }

    public function __toString(): string
    {
        return Buffer::new()->toBytes($this->sequence(), Command::GENERIC_NACK, $this->status);
    }
}
