<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\EnquireLink;
use OperationHardcode\Smpp\Protocol\Command\EnquireLinkResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use PHPUnit\Framework\TestCase;

final class EnquireLinkTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new EnquireLink())->withSequence(100);

        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(EnquireLink::class, $frame);
        self::assertEquals(100, $frame->sequence());

        $reply = $frame->reply();
        self::assertInstanceOf(EnquireLinkResp::class, $reply);
        self::assertEquals(CommandStatus::ESME_ROK(), $reply->status);
        self::assertEquals(100, $reply->sequence());
    }
}
