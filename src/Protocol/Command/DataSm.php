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

final class DataSm extends PDU
{
    public function __construct(
        public readonly string $serviceType,
        public readonly Destination $source,
        public readonly Destination $destination,
        public readonly EsmeClass $esmeClass = EsmeClass::STORE_AND_FORWARD,
        public readonly int $registeredDelivery = 0,
        public readonly int $dataCoding = 0,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        $serviceType = $buffer->consumeString();
        $sourceAddrTon = TON::try($buffer->consumeUint8(), TON::UNKNOWN);
        $sourceAddrNpi = NPI::try($buffer->consumeUint8(), NPI::UNKNOWN);
        $sourceAddr = $buffer->consumeString();
        $destinationAddrTon = TON::try($buffer->consumeUint8(), TON::UNKNOWN);
        $destinationAddrNpi = NPI::try($buffer->consumeUint8(), NPI::UNKNOWN);
        $destinationAddr = $buffer->consumeString();
        $esmeClass = EsmeClass::tryFrom($buffer->consumeUint8()) ?: EsmeClass::UNKNOWN;
        $registeredDelivery = $buffer->consumeUint8();
        $dataCoding = $buffer->consumeUint8();

        return new DataSm(
            $serviceType,
            new Destination($sourceAddr, $sourceAddrTon, $sourceAddrNpi),
            new Destination($destinationAddr, $destinationAddrTon, $destinationAddrNpi),
            $esmeClass,
            $registeredDelivery,
            $dataCoding,
        );
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->serviceType)
            ->appendUint8($this->source->ton?->value ?: 0)
            ->appendUint8($this->source->npi?->value ?: 0)
            ->appendString($this->source->value)
            ->appendUint8($this->destination->ton?->value ?: 0)
            ->appendUint8($this->destination->npi?->value ?: 0)
            ->appendString($this->destination->value)
            ->appendUint8($this->esmeClass->value)
            ->appendUint8($this->registeredDelivery)
            ->appendUint8($this->dataCoding)
            ->toBytes($this->sequence(), Command::DATA_SM);
    }
}
