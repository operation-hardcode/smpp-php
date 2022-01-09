<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\DataSmResp;
use OperationHardcode\Smpp\Protocol\FrameParser;
use PHPUnit\Framework\TestCase;

final class DataSmRespTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new DataSmResp('838383800'))->withSequence(2);
        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(DataSmResp::class, $frame);
        self::assertEquals('838383800', $frame->messageId);
        self::assertEquals(2, $frame->sequence());
    }
}
