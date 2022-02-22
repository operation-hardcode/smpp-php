<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

final class UnicodeMessage implements Message
{
    private string $encoded;

    public function __construct(string $text, private ?int $id = null)
    {
        $this->encoded = $this->doEncode($text);
    }

    public function length(): int
    {
        return \strlen($this->encoded);
    }

    public function encode(): string
    {
        return $this->encoded;
    }

    public function decode(): string
    {
        return $this->doDecode($this->encoded);
    }

    public function coding(): DataCoding
    {
        return DataCoding::DATA_CODING_UCS2;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function jsonSerialize(): string
    {
        return $this->encoded;
    }

    private function doEncode(string $text): string
    {
        $message = iconv('utf-8', 'UCS-2BE', $text);

        if (false === $message) {
            throw new \InvalidArgumentException('Message cannot be encoded to UCS-2BE.');
        }

        return $message;
    }

    private function doDecode(string $text): string
    {
        $message = iconv('UCS-2BE', 'utf-8', $text);

        if (false === $message) {
            throw new \InvalidArgumentException('Message cannot be encoded to utf-8.');
        }

        return $message;
    }
}
