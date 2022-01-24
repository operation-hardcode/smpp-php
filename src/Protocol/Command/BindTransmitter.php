<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class BindTransmitter extends Bind implements Replyable
{
    protected static Command $command = Command::BIND_TRANSMITTER;

    public function reply(?CommandStatus $status = null): PDU
    {
        return (new BindTransmitterResp($this->systemId, $status ?: CommandStatus::ESME_ROK()))->withSequence($this->sequence());
    }
}
