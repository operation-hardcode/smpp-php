<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class Unbind extends PDU implements Replyable
{
    public function __toString(): string
    {
        return Buffer::new()->toBytes($this->sequence(), Command::UNBIND);
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        return new Unbind();
    }

    public function reply(?CommandStatus $status = null): PDU
    {
        return (new UnbindResp($status ?: CommandStatus::ESME_ROK()))->withSequence($this->sequence());
    }
}
