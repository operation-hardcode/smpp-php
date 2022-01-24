<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

interface Message extends \JsonSerializable
{
    public function length(): int;
    public function text(): string;
    public function coding(): DataCoding;
    public function id(): ?int;
}
