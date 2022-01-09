<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class BindTransceiver extends Bind implements Replyable
{
    protected static Command $command = Command::BIND_TRANSCEIVER;

    public function reply(CommandStatus $status = CommandStatus::ESME_ROK): PDU
    {
        return (new BindTransceiverResp($this->systemId, $status))->withSequence($this->sequence());
    }
}
