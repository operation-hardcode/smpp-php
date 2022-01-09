<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\AlertNotification;
use OperationHardcode\Smpp\Protocol\Destination;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\TON;
use PHPUnit\Framework\TestCase;

final class AlertNotificationTest extends TestCase
{
    public function testBinary(): void
    {
        $command = (new AlertNotification(new Destination('312312', TON::ALPHANUMERIC, NPI::DATA), new Destination('9403203', TON::ABBREVIATED, NPI::TELEX)))->withSequence(1);

        $bytes = (string) $command;

        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(AlertNotification::class, $frame);
        self::assertEquals('312312', $frame->source->value);
        self::assertEquals(TON::ALPHANUMERIC->value, $frame->source->ton->value);
        self::assertEquals(NPI::DATA->value, $frame->source->npi->value);
        self::assertEquals('9403203', $frame->esme->value);
        self::assertEquals(TON::ABBREVIATED->value, $frame->esme->ton->value);
        self::assertEquals(NPI::TELEX->value, $frame->esme->npi->value);
        self::assertEquals(1, $frame->sequence());
    }
}
