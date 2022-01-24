<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\ReplaceSm;
use OperationHardcode\Smpp\Protocol\Command\ReplaceSmResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\TON;
use PHPUnit\Framework\TestCase;

final class ReplaceSmTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new ReplaceSm('83838', 'Hello, world :)', new Destination('92399')))->withSequence(4);
        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(ReplaceSm::class, $frame);
        self::assertEquals(4, $frame->sequence());
        self::assertEquals('83838', $frame->messageId);
        self::assertEquals('Hello, world :)', $frame->message);
        self::assertEquals('92399', $frame->source->value);
        self::assertEquals(TON::INTERNATIONAL, $frame->source->ton);
        self::assertEquals(NPI::UNKNOWN, $frame->source->npi);
        self::assertEmpty($frame->scheduleDeliveryTime);
        self::assertEmpty($frame->validityPeriod);
        self::assertEquals(0, $frame->registeredDelivery);
        self::assertEquals(0, $frame->smDefaultMsgId);

        $reply = $frame->reply();
        self::assertInstanceOf(ReplaceSmResp::class, $reply);
        self::assertEquals(4, $reply->sequence());
        self::assertEquals(CommandStatus::ESME_ROK(), $reply->status);
    }
}
