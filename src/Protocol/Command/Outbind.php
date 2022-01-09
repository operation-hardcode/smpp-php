<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class Outbind extends PDU
{
    public function __construct(
        public readonly string $systemId,
        public readonly string|null $password = null,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        return new Outbind($buffer->consumeString(), $buffer->consumeString());
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->systemId)
            ->appendString($this->password)
            ->toBytes($this->sequence(), Command::OUTBIND);
    }
}
