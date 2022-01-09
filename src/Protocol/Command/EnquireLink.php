<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class EnquireLink extends PDU implements Replyable
{
    public function __toString(): string
    {
        return Buffer::new()->toBytes($this->sequence(), Command::ENQUIRE_LINK);
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        return new EnquireLink();
    }

    public function reply(CommandStatus $status = CommandStatus::ESME_ROK): PDU
    {
        return (new EnquireLinkResp($status))->withSequence($this->sequence());
    }
}
