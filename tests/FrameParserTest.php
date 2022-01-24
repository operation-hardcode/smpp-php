<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests;

use OperationHardcode\Smpp\Protocol\CannotParseFrame;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use PHPUnit\Framework\TestCase;

final class FrameParserTest extends TestCase
{
    public function testInvalidMessageLength(): void
    {
        try {
            FrameParser::parse('');
            self::fail('Frame was parsed.');
        } catch (CannotParseFrame $e) {
            self::assertEquals(CommandStatus::ESME_RINVMSGLEN()->status, $e->status->status);
            self::assertEquals(0, $e->sequence);
        }
    }

    public function testInvalidHeaders(): void
    {
        try {
            FrameParser::parse(pack('NNNN', 16, -1, 0, 2));
            self::fail('Frame was parsed.');
        } catch (CannotParseFrame $e) {
            self::assertEquals(CommandStatus::ESME_RINVCMDID()->status, $e->status->status);
            self::assertEquals(2, $e->sequence);
        }
    }
}
