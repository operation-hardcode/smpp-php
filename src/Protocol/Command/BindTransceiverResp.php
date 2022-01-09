<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class BindTransceiverResp extends PDU
{
    public function __construct(
        public readonly string $systemId,
        public readonly CommandStatus $commandStatus = CommandStatus::ESME_ROK,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        return new BindTransceiverResp($buffer->consumeString(), $status);
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->systemId)
            ->toBytes($this->sequence(), Command::BIND_TRANSCEIVER_RESP, $this->commandStatus);
    }
}
