<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\QuerySm;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\TON;
use PHPUnit\Framework\TestCase;

final class QuerySmTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new QuerySm(messageId: '4e2423', sourceAddrTon: TON::SUBSCRIBER_NUMBER, sourceAddr: '0000111'))->withSequence(2);
        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(QuerySm::class, $frame);
        self::assertEquals(2, $frame->sequence());
        self::assertEquals('4e2423', $frame->messageId);
        self::assertEquals(TON::SUBSCRIBER_NUMBER, $frame->sourceAddrTon);
        self::assertEquals(NPI::UNKNOWN, $frame->sourceAddrNpi);
        self::assertEquals('0000111', $frame->sourceAddr);
    }
}
