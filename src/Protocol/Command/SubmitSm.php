<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\DataCoding;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\EsmeClass;
use OperationHardcode\Smpp\Protocol\Message\Message;
use OperationHardcode\Smpp\Protocol\Message\MessageFactory;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Protocol\TON;

final class SubmitSm extends PDU implements Replyable
{
    public function __construct(
        public readonly Destination $from,
        public readonly Destination $to,
        public readonly Message $message,
        public readonly string $serviceType = '',
        public readonly EsmeClass|int $esmeClass = EsmeClass::STORE_AND_FORWARD,
        public readonly int $protocolId = 0x00,
        public readonly int $priority = 0x00,
        public readonly ?string $scheduleDeliveryTime = null,
        public readonly ?string $validityPeriod = null,
        public readonly int $registeredDeliveryFlag = 0x00,
        public readonly int $replaceIfPresentFlag = 0x00,
    ) {
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->serviceType)
            ->appendUint8($this->from->ton->value)
            ->appendUint8($this->from->npi->value)
            ->appendString($this->from->value)
            ->appendUint8($this->to->ton->value)
            ->appendUint8($this->to->npi->value)
            ->appendString($this->to->value)
            ->appendUint8(
                $this->esmeClass instanceof EsmeClass
                    ? $this->esmeClass->value
                    : $this->esmeClass
            )
            ->appendUint8($this->protocolId)
            ->appendUint8($this->priority)
            ->appendString($this->scheduleDeliveryTime)
            ->appendString($this->validityPeriod)
            ->appendUint8($this->registeredDeliveryFlag)
            ->appendUint8($this->replaceIfPresentFlag)
            ->appendUint8($this->message->coding()->value)
            ->appendUint8OrNull($this->message->id())
            ->appendUint8($this->message->length())
            ->appendString($this->message->encode())
            ->toBytes($this->sequence(), Command::SUBMIT_SM);
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
        $scheduleDeliveryTime = $buffer->consumeString();
        $validityPeriod = $buffer->consumeString();
        $registeredDelivery = $buffer->consumeUint8();
        $replaceIfPresent = $buffer->consumeUint8();
        $dataCoding = DataCoding::tryFrom($buffer->consumeUint8()) ?: DataCoding::DATA_CODING_DEFAULT;
        $msgDefaultId = $buffer->consumeUint8();
        $shortMessage = $buffer->consume($buffer->consumeUint8());

        return new SubmitSm(
            new Destination($sourceAddress, $sourceAddrTon, $sourceAddrNpi),
            new Destination($destinationAddress, $destinationAddrTon, $destinationAddrNpi),
            MessageFactory::create($dataCoding, $shortMessage, $msgDefaultId),
            $serviceType,
            $esmeClass,
            $protocolId,
            $priorityFlag,
            $scheduleDeliveryTime,
            $validityPeriod,
            $registeredDelivery,
            $replaceIfPresent,
        );
    }

    public function reply(?CommandStatus $status = null): PDU
    {
        return (new SubmitSmResp($status ?: CommandStatus::ESME_ROK()))->withSequence($this->sequence());
    }
}
