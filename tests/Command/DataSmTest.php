<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\DataSm;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\EsmeClass;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\TON;
use PHPUnit\Framework\TestCase;

final class DataSmTest extends TestCase
{
    public function testBinary(): void
    {
        $bytes = (string) (new DataSm(
            'test',
            new Destination('929222'),
            new Destination('0020200'),
        EsmeClass::FORWARD,
        ))->withSequence(9);

        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(DataSm::class, $frame);
        self::assertEquals(9, $frame->sequence());
        self::assertEquals('test', $frame->serviceType);
        self::assertEquals('929222', $frame->source->value);
        self::assertEquals(TON::INTERNATIONAL, $frame->source->ton);
        self::assertEquals(NPI::UNKNOWN, $frame->source->npi);
        self::assertEquals('0020200', $frame->destination->value);
        self::assertEquals(TON::INTERNATIONAL, $frame->destination->ton);
        self::assertEquals(NPI::UNKNOWN, $frame->destination->npi);
        self::assertEquals(EsmeClass::FORWARD, $frame->esmeClass);
        self::assertEquals(0, $frame->registeredDelivery);
        self::assertEquals(0, $frame->dataCoding->value);
    }
}
