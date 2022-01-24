<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\BindTransceiver;
use OperationHardcode\Smpp\Protocol\Command\BindTransceiverResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\System;
use OperationHardcode\Smpp\Protocol\TON;
use OperationHardcode\Smpp\Protocol\Version;
use PHPUnit\Framework\TestCase;

final class BindTransceiverTest extends TestCase
{
    public function testBinary(): void
    {
        $command = (new BindTransceiver('0209202', 'secret'))->withSequence(13);

        $bytes = (string) $command;

        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindTransceiver::class, $frame);
        self::assertEquals('0209202', $frame->systemId);
        self::assertEquals('secret', $frame->password);
        self::assertEquals(13, $frame->sequence());
        self::assertEquals(Version::V_34, $frame->version);
        self::assertEquals(System::WWW, $frame->system);
        self::assertEquals(NPI::ISDN, $frame->npi);
        self::assertEquals(TON::INTERNATIONAL, $frame->ton);

        $command = (new BindTransceiver('0209202', 'secret', TON::NETWORK_SPECIFIC, NPI::WAP))->withSequence(14);
        $bytes = (string) $command;
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindTransceiver::class, $frame);
        self::assertEquals('0209202', $frame->systemId);
        self::assertEquals('secret', $frame->password);
        self::assertEquals(14, $frame->sequence());
        self::assertEquals(Version::V_34, $frame->version);
        self::assertEquals(System::WWW, $frame->system);
        self::assertEquals(NPI::WAP, $frame->npi);
        self::assertEquals(TON::NETWORK_SPECIFIC, $frame->ton);

        $reply = $frame->reply();
        self::assertInstanceOf(BindTransceiverResp::class, $reply);
        self::assertEquals(14, $reply->sequence());
        self::assertEquals('0209202', $reply->systemId);
        self::assertEquals(CommandStatus::ESME_ROK(), $reply->commandStatus);

        $bytes = (string) $reply;

        $responseFrame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindTransceiverResp::class, $responseFrame);
        self::assertEquals(14, $responseFrame->sequence());
        self::assertEquals('0209202', $responseFrame->systemId);
        self::assertEquals(CommandStatus::ESME_ROK(), $responseFrame->commandStatus);

        $failedReply = $frame->reply(CommandStatus::ESME_RBINDFAIL());
        self::assertInstanceOf(BindTransceiverResp::class, $failedReply);
        self::assertEquals(14, $failedReply->sequence());
        self::assertEquals('0209202', $failedReply->systemId);
        self::assertEquals(CommandStatus::ESME_RBINDFAIL(), $failedReply->commandStatus);
    }
}
