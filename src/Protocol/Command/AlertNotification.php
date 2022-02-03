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

final class AlertNotification extends PDU
{
    public function __construct(
        public readonly Destination $source,
        public readonly Destination $esme,
    ) {
    }

    public static function reconstitute(CommandStatus $status, Buffer $buffer): PDU
    {
        $sourceTon = TON::try($buffer->consumeUint8());
        $sourceNpi = NPI::try($buffer->consumeUint8());
        $sourceAddr = $buffer->consumeString();

        $esmeTon = TON::try($buffer->consumeUint8());
        $esmeNpi = NPI::try($buffer->consumeUint8());
        $esmeAddr = $buffer->consumeString();

        return new AlertNotification(
            new Destination($sourceAddr, $sourceTon, $sourceNpi),
            new Destination($esmeAddr, $esmeTon, $esmeNpi),
        );
    }

    public function __toString(): string
    {
        return Buffer::new()
            ->appendUint8($this->source->ton->value)
            ->appendUint8($this->source->npi->value)
            ->appendString($this->source->value)
            ->appendUint8($this->esme->ton->value)
            ->appendUint8($this->esme->npi->value)
            ->appendString($this->esme->value)
            ->toBytes($this->sequence(), Command::ALERT_NOTIFICATION);
    }
}
