<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class BindReceiverResp extends PDU
{
    public function __construct(
        public readonly string $systemId,
        public readonly CommandStatus $commandStatus,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        return new BindReceiverResp($buffer->consumeString(), $status);
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->systemId)
            ->toBytes($this->sequence(), Command::BIND_RECEIVER_RESP, $this->commandStatus);
    }
}
