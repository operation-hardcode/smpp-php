<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\Unbind;
use OperationHardcode\Smpp\Protocol\Command\UnbindResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use PHPUnit\Framework\TestCase;

final class UnbindTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new Unbind())->withSequence(200);

        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(Unbind::class, $frame);
        self::assertEquals(200, $frame->sequence());

        $reply = $frame->reply();
        self::assertInstanceOf(UnbindResp::class, $reply);
        self::assertEquals(200, $reply->sequence());
        self::assertEquals(CommandStatus::ESME_ROK, $reply->status);
    }
}
