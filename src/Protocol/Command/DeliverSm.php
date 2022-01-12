<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\EsmeClass;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Protocol\TON;

final class DeliverSm extends PDU implements Replyable
{
    public function __construct(
        public readonly Destination $from,
        public readonly Destination $to,
        public readonly string $message,
        public readonly string $serviceType = '',
        public readonly EsmeClass|int $esmeClass = EsmeClass::STORE_AND_FORWARD,
        public readonly int $protocolId = 0x00,
        public readonly int $priority = 0x00,
        public readonly int $registeredDeliveryFlag = 0x00,
        public readonly int $dataCoding = 0,
    ) {
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->serviceType)
            ->appendUint8($this->from->ton?->value ?: 0)
            ->appendUint8($this->from->npi?->value ?: 0)
            ->appendString($this->from->value)
            ->appendUint8($this->to->ton?->value ?: 0)
            ->appendUint8($this->to->npi->value ?: 0)
            ->appendString($this->to->value)
            ->appendUint8(
                $this->esmeClass instanceof EsmeClass
                    ? $this->esmeClass->value
                    : $this->esmeClass
            )
            ->appendUint8($this->protocolId)
            ->appendUint8($this->priority)
            ->padding() // schedule_delivery_time
            ->padding() // validity_period
            ->appendUint8($this->registeredDeliveryFlag)
            ->padding() // replace_if_present_flag
            ->appendUint8($this->dataCoding)
            ->padding() // sm_default_msg_id
            ->appendUint8(strlen($this->message))
            ->appendString($this->message)
            ->toBytes($this->sequence(), Command::DELIVER_SM)
        ;
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        $serviceType = $buffer->consumeString();
        $sourceAddrTon = TON::try($buffer->consumeUint8());
        $sourceAddrNpi = NPI::try($buffer->consumeUint8());
        $sourceAddress = $buffer->consumeString();
        $destinationAddrTon = TON::try($buffer->consumeUint8());
        $destinationAddrNpi = NPI::try($buffer->consumeUint8());
        $destinationAddress = $buffer->consumeString();
        $esmeClass = EsmeClass::tryFrom($bits = $buffer->consumeUint8()) ?: $bits;
        $protocolId = $buffer->consumeUint8();
        $priorityFlag = $buffer->consumeUint8();
        $buffer->discard(2); // In `deliver_sm` the `schedule_delivery_time` and `validity_period` fields set to NULL.
        $registeredDelivery = $buffer->consumeUint8();
        $buffer->discard(1); // In `deliver_sm` the `replace_if_present_flag` field set to NULL.
        $dataCoding = $buffer->consumeUint8();
        $buffer->discard(1); // In `deliver_sm` the `sm_default_msg_id` field set to NULL.
        $shortMessage = $buffer->consume($buffer->consumeUint8());

        return new DeliverSm(
            new Destination($sourceAddress, $sourceAddrTon, $sourceAddrNpi),
            new Destination($destinationAddress, $destinationAddrTon, $destinationAddrNpi),
            $shortMessage,
            $serviceType,
            $esmeClass,
            $protocolId,
            $priorityFlag,
            $registeredDelivery,
            $dataCoding,
        );
    }

    public function reply(CommandStatus $status = CommandStatus::ESME_ROK): PDU
    {
        return (new DeliverSmResp($status))->withSequence($this->sequence());
    }
}
