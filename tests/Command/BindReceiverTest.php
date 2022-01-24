<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\BindReceiver;
use OperationHardcode\Smpp\Protocol\Command\BindReceiverResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\System;
use OperationHardcode\Smpp\Protocol\TON;
use OperationHardcode\Smpp\Protocol\Version;
use PHPUnit\Framework\TestCase;

final class BindReceiverTest extends TestCase
{
    public function testBinary(): void
    {
        $command = (new BindReceiver('209202', 'secret'))->withSequence(3);

        $bytes = (string) $command;

        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindReceiver::class, $frame);
        self::assertEquals('209202', $frame->systemId);
        self::assertEquals('secret', $frame->password);
        self::assertEquals(3, $frame->sequence());
        self::assertEquals(Version::V_34, $frame->version);
        self::assertEquals(System::WWW, $frame->system);
        self::assertEquals(NPI::ISDN, $frame->npi);
        self::assertEquals(TON::INTERNATIONAL, $frame->ton);

        $command = (new BindReceiver('209202', 'secret', TON::NETWORK_SPECIFIC, NPI::WAP))->withSequence(4);
        $bytes = (string) $command;
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindReceiver::class, $frame);
        self::assertEquals('209202', $frame->systemId);
        self::assertEquals('secret', $frame->password);
        self::assertEquals(4, $frame->sequence());
        self::assertEquals(Version::V_34, $frame->version);
        self::assertEquals(System::WWW, $frame->system);
        self::assertEquals(NPI::WAP, $frame->npi);
        self::assertEquals(TON::NETWORK_SPECIFIC, $frame->ton);

        $reply = $frame->reply();
        self::assertInstanceOf(BindReceiverResp::class, $reply);
        self::assertEquals(4, $reply->sequence());
        self::assertEquals('209202', $reply->systemId);
        self::assertEquals(CommandStatus::ESME_ROK(), $reply->commandStatus);

        $bytes = (string) $reply;

        $responseFrame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindReceiverResp::class, $responseFrame);
        self::assertEquals(4, $responseFrame->sequence());
        self::assertEquals('209202', $responseFrame->systemId);
        self::assertEquals(CommandStatus::ESME_ROK(), $responseFrame->commandStatus);

        $failedReply = $frame->reply(CommandStatus::ESME_RBINDFAIL());
        self::assertInstanceOf(BindReceiverResp::class, $failedReply);
        self::assertEquals(4, $failedReply->sequence());
        self::assertEquals('209202', $failedReply->systemId);
        self::assertEquals(CommandStatus::ESME_RBINDFAIL(), $failedReply->commandStatus);
    }
}
