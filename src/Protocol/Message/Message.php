<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

interface Message extends \JsonSerializable
{
    public function length(): int;

    /**
     * @throws \InvalidArgumentException
     */
    public function encode(): string;

    /**
     * @throws \InvalidArgumentException
     */
    public function decode(): string;
    public function coding(): DataCoding;
    public function id(): ?int;
}
