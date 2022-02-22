<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Message;

use OperationHardcode\Smpp\Protocol\Message\UnicodeMessage;
use PHPUnit\Framework\TestCase;

final class UnicodeMessageTest extends TestCase
{
    public function testMessageEncoded(): void
    {
        $message = new UnicodeMessage('Привет, мир');
        self::assertNotEquals('Привет, мир', $message->encode());
        self::assertEquals('Привет, мир', (string) $message);
        self::assertEquals(strlen($message->encode()), $message->length());
        self::assertNull($message->id());

        $encodedMessage = UnicodeMessage::fromEncoded($encoded = $message->encode());
        self::assertEquals($encoded, $encodedMessage->encode());
        self::assertEquals('Привет, мир', (string) $encodedMessage);
    }
}
