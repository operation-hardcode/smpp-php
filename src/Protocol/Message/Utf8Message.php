<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

final class Utf8Message implements Message
{
    public function __construct(private string $text, private ?int $id = null)
    {
    }

    public function length(): int
    {
        return \strlen($this->text);
    }

    public function encode(): string
    {
        return $this->text;
    }

    public function coding(): DataCoding
    {
        return DataCoding::DATA_CODING_DEFAULT;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->text;
    }

    public function jsonSerialize(): string
    {
        return $this->text;
    }

    public static function fromEncoded(string $encoded, ?int $msgId = null): self
    {
        return new Utf8Message($encoded, $msgId);
    }
}
