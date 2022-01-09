<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\GenericNack;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use PHPUnit\Framework\TestCase;

final class GenericNackTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) new GenericNack(CommandStatus::ESME_RINVCMDID);

        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(GenericNack::class, $frame);
        self::assertEquals(0, $frame->sequence());
        self::assertEquals(CommandStatus::ESME_RINVCMDID, $frame->status);
    }
}
