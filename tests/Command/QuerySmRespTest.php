<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\QuerySmResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use PHPUnit\Framework\TestCase;

final class QuerySmRespTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new QuerySmResp('929292', 1, 0))->withSequence(22);
        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(QuerySmResp::class, $frame);
        self::assertEquals('929292', $frame->messageId);
        self::assertEquals(1, $frame->messageState);
        self::assertEquals(0, $frame->errorCode);
        self::assertEmpty($frame->finalDate);
        self::assertEquals(CommandStatus::ESME_ROK, $frame->status);
    }
}
