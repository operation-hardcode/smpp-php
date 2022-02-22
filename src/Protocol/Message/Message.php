<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

interface Message extends \JsonSerializable, \Stringable
{
    public function length(): int;

    /**
     * @throws \InvalidArgumentException
     */
    public function encode(): string;
    public function coding(): DataCoding;
    public function id(): ?int;
    public static function fromEncoded(string $encoded, ?int $msgId = null): self;
}
