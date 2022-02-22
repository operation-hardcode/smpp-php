<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Message;

use OperationHardcode\Smpp\Protocol\DataCoding;
use OperationHardcode\Smpp\Protocol\Message\MessageFactory;
use OperationHardcode\Smpp\Protocol\Message\UnicodeMessage;
use PHPUnit\Framework\TestCase;

final class MessageFactoryTest extends TestCase
{
    public function testMessageEncoded(): void
    {
        $message = MessageFactory::create(DataCoding::DATA_CODING_UCS2, (new UnicodeMessage('Привет, мир'))->encode());
        self::assertInstanceOf(UnicodeMessage::class, $message);
        self::assertEquals('Привет, мир', (string) $message);
    }
}
