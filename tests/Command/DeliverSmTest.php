<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\DeliverSm;
use OperationHardcode\Smpp\Protocol\Command\DeliverSmResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\FrameParser;
use PHPUnit\Framework\TestCase;

final class DeliverSmTest extends TestCase
{
    public function testBinary(): void
    {
        $command = (new DeliverSm(
            new Destination('99900238'),
            new Destination('00900238'),
            'Hello, world!000',
        ))->withSequence(5);

        $bytes = (string) $command;

        self::assertTrue(FrameParser::hasFrame($bytes));

        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(DeliverSm::class, $frame);
        self::assertEquals(5, $frame->sequence());
        self::assertEmpty($frame->serviceType);
        self::assertEquals('Hello, world!000', $frame->message);
        self::assertEquals('99900238', $frame->from->value);
        self::assertEquals('00900238', $frame->to->value);

        $reply = $frame->reply();
        self::assertInstanceOf(DeliverSmResp::class, $reply);
        self::assertEquals(5, $reply->sequence());
        self::assertEquals(CommandStatus::ESME_ROK, $reply->status);
    }
}
