<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

final class UnicodeMessage implements Message
{
    private string $text;

    public function __construct(string $text, private ?int $id = null)
    {
        $this->text = $text;
    }

    public function length(): int
    {
        return \strlen(self::doEncode($this->text));
    }

    public function encode(): string
    {
        return self::doEncode($this->text);
    }

    public function __toString(): string
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

    public function jsonSerialize(): string
    {
        return self::doEncode($this->text);
    }

    public static function fromEncoded(string $encoded, ?int $msgId = null): self
    {
        return new UnicodeMessage(self::doDecode($encoded), $msgId);
    }

    private static function doEncode(string $text): string
    {
        $message = iconv('utf-8', 'UCS-2BE', $text);

        if (false === $message) {
            throw new \InvalidArgumentException('Message cannot be encoded to UCS-2BE.');
        }

        return $message;
    }

    private static function doDecode(string $text): string
    {
        $message = iconv('UCS-2BE', 'UTF-8', $text);

        if (false === $message) {
            throw new \InvalidArgumentException('Message cannot be encoded to utf-8.');
        }

        return $message;
    }
}
