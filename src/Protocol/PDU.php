<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

use OperationHardcode\Smpp\Buffer;

abstract class PDU implements \Stringable
{
    private int $sequence = 0;

    final public function sequence(): int
    {
        return $this->sequence;
    }

    final public function withSequence(int $sequence): PDU
    {
        $this->sequence = $sequence;

        return $this;
    }

    abstract public static function reconstitute(CommandStatus $status, Buffer $buffer): self;
}
