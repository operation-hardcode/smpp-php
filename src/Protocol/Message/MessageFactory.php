<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

final class MessageFactory
{
    public static function create(DataCoding $coding, string $text, ?int $msgId = null): Message
    {
        return match ($coding) {
            DataCoding::DATA_CODING_UCS2 => UnicodeMessage::fromEncoded($text, $msgId),
            default => Utf8Message::fromEncoded($text, $msgId)
        };
    }
}
