<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class BindTransceiver extends Bind implements Replyable
{
    protected static Command $command = Command::BIND_TRANSCEIVER;

    public function reply(?CommandStatus $status = null): PDU
    {
        return (new BindTransceiverResp($this->systemId, $status ?: CommandStatus::ESME_ROK()))->withSequence($this->sequence());
    }
}
