<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests;

use OperationHardcode\Smpp\Sequence;
use PHPUnit\Framework\TestCase;

abstract class SmppTestCase extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Sequence::delegate()->reset();
    }
}
