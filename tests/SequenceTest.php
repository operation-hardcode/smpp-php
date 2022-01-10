<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests;

use OperationHardcode\Smpp\Sequence;
use Amp;

final class SequenceTest extends SmppTestCase
{
    public function testSequenceIncrement(): void
    {
        Amp\Loop::run(function (): \Generator {
            self::assertEquals(0, Sequence::delegate()->current());
            self::assertEquals(1, yield Sequence::delegate()->next());
            self::assertEquals(1, Sequence::delegate()->current());
            self::assertEquals(2, yield Sequence::delegate()->next());
            self::assertEquals(2, Sequence::delegate()->current());
            self::assertEquals(3, yield Sequence::delegate()->next());
            self::assertEquals(3, Sequence::delegate()->current());
            self::assertFalse(Sequence::delegate()->overflow());
            Sequence::delegate()->reset();
            self::assertEquals(0, Sequence::delegate()->current());
            self::assertEquals(1, yield Sequence::delegate()->next());
        });
    }
}
