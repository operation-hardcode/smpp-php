<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Protocol\TON;

final class QuerySm extends PDU
{
    public function __construct(
        public readonly string $messageId,
        public readonly TON|null $sourceAddrTon = null,
        public readonly NPI|null $sourceAddrNpi = null,
        public readonly string|null $sourceAddr = null,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        $messageId = $buffer->consumeString();
        $sourceAddrTon = TON::tryFrom($buffer->consumeUint8());
        $sourceAddrNpi = NPI::tryFrom($buffer->consumeUint8());
        $sourceAddr = $buffer->consumeString();

        return new QuerySm($messageId, $sourceAddrTon, $sourceAddrNpi, $sourceAddr);
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->messageId)
            ->appendUint8($this->sourceAddrTon?->value ?: 0)
            ->appendUint8($this->sourceAddrNpi?->value ?: 0)
            ->appendString($this->sourceAddr)
            ->toBytes($this->sequence(), Command::QUERY_SM);
    }
}
