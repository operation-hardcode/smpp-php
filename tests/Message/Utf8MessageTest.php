<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Message;

use OperationHardcode\Smpp\Protocol\Message\Utf8Message;
use PHPUnit\Framework\TestCase;

final class Utf8MessageTest extends TestCase
{
    public function testMessageEncoded(): void
    {
        $message = new Utf8Message('Hello, world');
        self::assertEquals('Hello, world', $message->encode());
        self::assertEquals('Hello, world', (string)  $message);
        self::assertEquals(strlen('Hello, world'), $message->length());
        self::assertNull($message->id());
        self::assertEquals('Hello, world', Utf8Message::fromEncoded('Hello, world')->encode());
    }
}
