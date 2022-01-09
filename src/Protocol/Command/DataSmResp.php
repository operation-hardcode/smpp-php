<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class DataSmResp extends PDU
{
    public function __construct(public readonly string $messageId, public readonly CommandStatus $status = CommandStatus::ESME_ROK)
    {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        return new DataSmResp($buffer->consumeString());
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->messageId)
            ->toBytes($this->sequence(), Command::DATA_SM_RESP, $this->status);
    }
}
