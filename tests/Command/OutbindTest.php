<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\Outbind;
use OperationHardcode\Smpp\Protocol\FrameParser;
use PHPUnit\Framework\TestCase;

final class OutbindTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new Outbind('9299323'))->withSequence(90);
        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(Outbind::class, $frame);
        self::assertEquals('9299323', $frame->systemId);
        self::assertEquals(90, $frame->sequence());
        self::assertEmpty($frame->password);

        $bytes = (string) (new Outbind('9299323', 'secret'))->withSequence(90);
        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(Outbind::class, $frame);
        self::assertEquals('9299323', $frame->systemId);
        self::assertEquals(90, $frame->sequence());
        self::assertEquals('secret', $frame->password);
    }
}
