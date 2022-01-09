<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests;

use OperationHardcode\Smpp\Buffer;
use PHPUnit\Framework\TestCase;

final class BufferTest extends TestCase
{
    public function testBinaryString(): void
    {
        $buffer = new Buffer(
            Buffer::new()
                ->appendString('test')
                ->appendString('string')
                ->appendString('appended')
                ->bytes()
        );
        self::assertEquals('test', $buffer->consumeString());
        self::assertEquals('string', $buffer->consumeString());
        self::assertEquals('appended', $buffer->consumeString());
    }

    public function testComplexBinaryData(): void
    {
        $buffer = new Buffer(
            Buffer::new()
                ->appendString('test')
                ->appendString('string')
                ->appendUint8(20)
                ->appendString('appended')
                ->appendUint8(30)
                ->appendString('with zero 000')
                ->bytes()
        );
        self::assertEquals('test', $buffer->consumeString());
        self::assertEquals('string', $buffer->consumeString());
        self::assertEquals(20, $buffer->consumeUint8());
        self::assertEquals('appended', $buffer->consumeString());
        self::assertEquals(30, $buffer->consumeUint8());
        self::assertEquals('with zero 000', $buffer->consumeString());
    }
}
