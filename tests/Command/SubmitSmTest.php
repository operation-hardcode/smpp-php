<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\SubmitSm;
use OperationHardcode\Smpp\Protocol\Command\SubmitSmResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\TON;
use PHPUnit\Framework\TestCase;

final class SubmitSmTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new SubmitSm(
            from: new Destination('042304023'),
            to: new Destination('02002'),
            message: 'Hello, world!0!0',
            serviceType: 'test',
            priority: 1,
            registeredDeliveryFlag: 1,
            replaceIfPresentFlag: 1,
            defaultMessageId: 2
        ))->withSequence(8);

        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertEquals(8, $frame->sequence());
        self::assertInstanceOf(SubmitSm::class, $frame);
        self::assertEquals('042304023', $frame->from->value);
        self::assertEquals(TON::INTERNATIONAL, $frame->from->ton);
        self::assertEquals(NPI::ISDN, $frame->from->npi);
        self::assertEquals('02002', $frame->to->value);
        self::assertEquals(TON::INTERNATIONAL, $frame->to->ton);
        self::assertEquals(NPI::ISDN, $frame->to->npi);
        self::assertEquals('Hello, world!0!0', $frame->message);
        self::assertEquals('test', $frame->serviceType);
        self::assertEmpty($frame->scheduleDeliveryTime);
        self::assertEmpty($frame->validityPeriod);
        self::assertEquals(0, $frame->protocolId);
        self::assertEquals(1, $frame->priority);
        self::assertEquals(1, $frame->registeredDeliveryFlag);
        self::assertEquals(1, $frame->replaceIfPresentFlag);
        self::assertEquals(0, $frame->dataCoding);
        self::assertEquals(2, $frame->defaultMessageId);

        $reply = $frame->reply(CommandStatus::ESME_RINVREPFLAG);
        self::assertInstanceOf(SubmitSmResp::class, $reply);
        self::assertEquals(8, $reply->sequence());
        self::assertEquals(CommandStatus::ESME_RINVREPFLAG, $reply->status);
    }
}
