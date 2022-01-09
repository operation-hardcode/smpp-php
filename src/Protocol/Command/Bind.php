<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Command;

use OperationHardcode\Smpp\Buffer;
use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\PDU;
use OperationHardcode\Smpp\Protocol\System;
use OperationHardcode\Smpp\Protocol\TON;
use OperationHardcode\Smpp\Protocol\Version;

abstract class Bind extends PDU
{
    protected static Command $command;

    final public function __construct(
        public readonly string $systemId,
        public readonly string $password,
        public readonly TON $ton = TON::INTERNATIONAL,
        public readonly NPI $npi = NPI::ISDN,
        public readonly Version $version = Version::V_34,
        public readonly System $system = System::WWW,
    ) {
    }

    final public static function reconstitute(CommandStatus $status, Buffer $buffer): self
    {
        $systemId = $buffer->consumeString();
        $password = $buffer->consumeString();
        $system = System::tryFrom($buffer->consumeString()) ?: System::UNKNOWN;
        $version = Version::tryFrom($buffer->consumeUint8());
        $ton = TON::tryFrom($buffer->consumeUint32()) ?: TON::INTERNATIONAL;
        $npi = NPI::tryFrom($buffer->consumeUint32()) ?: NPI::ISDN;

        return new static($systemId, $password, $ton, $npi, $version, $system);
    }

    final public function __toString(): string
    {
        return Buffer::new()
            ->appendString($this->systemId)
            ->appendString($this->password)
            ->appendString($this->system->value)
            ->appendUint8($this->version->value)
            ->appendUint32($this->ton->value)
            ->appendUint32($this->npi->value)
            ->toBytes($this->sequence(), static::$command);
    }
}
