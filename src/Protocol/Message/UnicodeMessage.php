<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

final class UnicodeMessage implements Message
{
    public function __construct(private string $text, private ?int $id = null)
    {
        $this->text = $this->convert($this->text);
    }

    public function length(): int
    {
        return \strlen($this->text);
    }

    public function text(): string
    {
        return $this->text;
    }

    public function coding(): DataCoding
    {
        return DataCoding::DATA_CODING_UCS2;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function jsonSerialize(): mixed
    {
        return $this->text;
    }

    private function convert(string $text): string
    {
        $message = iconv('utf-8', 'UCS-2BE', $text);

        if (false === $message) {
            throw new \InvalidArgumentException('Message cannot be encoded to UCS-2BE.');
        }

        return $message;
    }
}
