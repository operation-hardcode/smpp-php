<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Protocol\TON;

final class CancelSm extends PDU implements Replyable
{
    public function __construct(
        public readonly Destination $source,
        public readonly Destination $destination,
        public readonly string|null $serviceType = null,
        public readonly string|null $messageId = null,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        $serviceType = $buffer->consumeString();
        $messageId = $buffer->consumeString();
        $sourceAddrTon = TON::try($buffer->consumeUint8());
        $sourceAddrNpi = NPI::try($buffer->consumeUint8());
        $sourceAddr = $buffer->consumeString();
        $destinationAddrTon = TON::try($buffer->consumeUint8());
        $destinationAddrNpi = NPI::try($buffer->consumeUint8());
        $destinationAddr = $buffer->consumeString();

        return new CancelSm(
            new Destination($sourceAddr, $sourceAddrTon, $sourceAddrNpi),
            new Destination($destinationAddr, $destinationAddrTon, $destinationAddrNpi),
            $serviceType,
            $messageId
        );
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->serviceType)
            ->appendString($this->messageId)
            ->appendUint8($this->source->ton->value)
            ->appendUint8($this->source->npi->value)
            ->appendString($this->source->value)
            ->appendUint8($this->destination->ton->value)
            ->appendUint8($this->destination->npi->value)
            ->appendString($this->destination->value)
            ->toBytes($this->sequence(), Command::CANCEL_SM);
    }

    public function reply(?CommandStatus $status = null): PDU
    {
        return (new CancelSmResp($status ?: CommandStatus::ESME_ROK()))->withSequence($this->sequence());
    }
}
