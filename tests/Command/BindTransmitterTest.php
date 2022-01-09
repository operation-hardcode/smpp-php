<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Tests\Command;

use OperationHardcode\Smpp\Protocol\Command\BindTransmitter;
use OperationHardcode\Smpp\Protocol\Command\BindTransmitterResp;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use OperationHardcode\Smpp\Protocol\FrameParser;
use OperationHardcode\Smpp\Protocol\NPI;
use OperationHardcode\Smpp\Protocol\System;
use OperationHardcode\Smpp\Protocol\TON;
use OperationHardcode\Smpp\Protocol\Version;
use PHPUnit\Framework\TestCase;

final class BindTransmitterTest extends TestCase
{
    public function testBinary(): void
    {
        $command = (new BindTransmitter('02092020', 'secret0'))->withSequence(23);

        $bytes = (string) $command;

        self::assertTrue(FrameParser::hasFrame($bytes));
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindTransmitter::class, $frame);
        self::assertEquals('02092020', $frame->systemId);
        self::assertEquals('secret0', $frame->password);
        self::assertEquals(23, $frame->sequence());
        self::assertEquals(Version::V_34, $frame->version);
        self::assertEquals(System::WWW, $frame->system);
        self::assertEquals(NPI::ISDN, $frame->npi);
        self::assertEquals(TON::INTERNATIONAL, $frame->ton);

        $command = (new BindTransmitter('02092020', 'secret0', TON::NETWORK_SPECIFIC, NPI::WAP))->withSequence(24);
        $bytes = (string) $command;
        $frame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindTransmitter::class, $frame);
        self::assertEquals('02092020', $frame->systemId);
        self::assertEquals('secret0', $frame->password);
        self::assertEquals(24, $frame->sequence());
        self::assertEquals(Version::V_34, $frame->version);
        self::assertEquals(System::WWW, $frame->system);
        self::assertEquals(NPI::WAP, $frame->npi);
        self::assertEquals(TON::NETWORK_SPECIFIC, $frame->ton);

        $reply = $frame->reply();
        self::assertInstanceOf(BindTransmitterResp::class, $reply);
        self::assertEquals(24, $reply->sequence());
        self::assertEquals('02092020', $reply->systemId);
        self::assertEquals(CommandStatus::ESME_ROK, $reply->commandStatus);

        $bytes = (string) $reply;

        $responseFrame = FrameParser::parse($bytes);
        self::assertInstanceOf(BindTransmitterResp::class, $responseFrame);
        self::assertEquals(24, $responseFrame->sequence());
        self::assertEquals('02092020', $responseFrame->systemId);
        self::assertEquals(CommandStatus::ESME_ROK, $responseFrame->commandStatus);

        $failedReply = $frame->reply(CommandStatus::ESME_RBINDFAIL);
        self::assertInstanceOf(BindTransmitterResp::class, $failedReply);
        self::assertEquals(24, $failedReply->sequence());
        self::assertEquals('02092020', $failedReply->systemId);
        self::assertEquals(CommandStatus::ESME_RBINDFAIL, $failedReply->commandStatus);
    }
}
