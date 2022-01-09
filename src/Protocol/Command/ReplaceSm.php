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

final class ReplaceSm extends PDU implements Replyable
{
    public function __construct(
        public readonly string $messageId,
        public readonly string $message,
        public readonly Destination $source,
        public readonly string|null $scheduleDeliveryTime = null,
        public readonly string|null $validityPeriod = null,
        public readonly int $registeredDelivery = 0,
        public readonly int $smDefaultMsgId = 0,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        $messageId = $buffer->consumeString();
        $sourceAddrTon = TON::try($buffer->consumeUint8(), TON::UNKNOWN);
        $sourceAddrNpi = NPI::try($buffer->consumeUint8(), NPI::UNKNOWN);
        $sourceAddr = $buffer->consumeString();
        $scheduleDeliveryTime = $buffer->consumeString();
        $validityPeriod = $buffer->consumeString();
        $registeredDelivery = $buffer->consumeUint8();
        $smDefaultMsgId = $buffer->consumeUint8();
        $message = $buffer->consume($buffer->consumeUint8());

        return new ReplaceSm(
            $messageId,
            $message,
            new Destination($sourceAddr, $sourceAddrTon, $sourceAddrNpi),
            $scheduleDeliveryTime,
            $validityPeriod,
            $registeredDelivery,
            $smDefaultMsgId,
        );
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->messageId)
            ->appendUint8($this->source->ton?->value ?: 0)
            ->appendUint8($this->source->npi?->value ?: 0)
            ->appendString($this->source->value)
            ->appendString($this->scheduleDeliveryTime)
            ->appendString($this->validityPeriod)
            ->appendUint8($this->registeredDelivery)
            ->appendUint8($this->smDefaultMsgId)
            ->appendUint8(\strlen($this->message))
            ->appendString($this->message)
            ->toBytes($this->sequence(), Command::REPLACE_SM);
    }

    public function reply(CommandStatus $status = CommandStatus::ESME_ROK): PDU
    {
        return (new ReplaceSmResp($status))->withSequence($this->sequence());
    }
}
