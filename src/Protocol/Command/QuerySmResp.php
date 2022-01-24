<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\PDU;

final class QuerySmResp extends PDU
{
    public function __construct(
        public readonly string $messageId,
        public readonly int $messageState,
        public readonly int $errorCode,
        public readonly CommandStatus $status,
        public readonly string|null $finalDate = null,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        $messageId = $buffer->consumeString();
        $state = $buffer->consumeUint8();
        $errorCode = $buffer->consumeUint8();
        $finalDate = $buffer->consumeString();

        return new QuerySmResp($messageId, $state, $errorCode, $status, $finalDate);
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->messageId)
            ->appendUint8($this->messageState)
            ->appendUint8($this->errorCode)
            ->appendString($this->finalDate)
            ->toBytes($this->sequence(), Command::QUERY_SM_RESP, $this->status);
    }
}
