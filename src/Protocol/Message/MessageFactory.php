<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;

final class MessageFactory
{
    public static function create(DataCoding $coding, string $text, ?int $msgId = null): Message
    {
        if ($coding === DataCoding::DATA_CODING_UCS2) {
            return new UnicodeMessage($text, $msgId);
        }

        return new Utf8Message($text, $msgId);
    }
}
