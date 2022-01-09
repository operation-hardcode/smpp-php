<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\CancelSm;
use OperationHardcode\Smpp\Protocol\Command\CancelSmResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\TON;
use PHPUnit\Framework\TestCase;

final class CancelSmTest extends TestCase
{
    public function testBinary(): void
    {
        $cancelSm = (new CancelSm(new Destination('1111', null, null), new Destination('222'), 'test'))->withSequence(2);

        $bytes = (string) $cancelSm;

        self::assertTrue(FrameParser::hasFrame($bytes));

        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(CancelSm::class, $frame);
        self::assertEquals(2, $frame->sequence());
        self::assertEquals('1111', $frame->source->value);
        self::assertEquals(TON::UNKNOWN, $frame->source->ton);
        self::assertEquals(NPI::UNKNOWN, $frame->source->npi);
        self::assertEquals('222', $frame->destination->value);
        self::assertEquals(TON::INTERNATIONAL, $frame->destination->ton);
        self::assertEquals(NPI::ISDN, $frame->destination->npi);
        self::assertEquals('test', $frame->serviceType);

        $reply = $frame->reply();
        self::assertInstanceOf(CancelSmResp::class, $reply);
        self::assertEquals(2, $reply->sequence());
        self::assertEquals(CommandStatus::ESME_ROK, $reply->status);
    }
}
