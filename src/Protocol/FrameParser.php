<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

use OperationHardcode\Smpp\Buffer;

/**
 * @psalm-type PduHeader = array{length:int, command:int, status:int, sequence:int}
 */
final class FrameParser
{
    private const HEADER_LEN = 16;
    private const HEADER_FORMAT = 'Nlength/Ncommand/Nstatus/Nsequence';

    /**
     * @throws CannotParseFrame
     */
    public static function hasFrame(string $bytes): bool
    {
        if (strlen($bytes) < self::HEADER_LEN) {
            throw CannotParseFrame::withCommandStatus(
                CommandStatus::ESME_RINVMSGLEN
            );
        }

        /** @var PduHeader|false $headers */
        $headers = @unpack(self::HEADER_FORMAT, $bytes);

        if (false === $headers) {
            throw CannotParseFrame::withCommandStatus(
                CommandStatus::ESME_RINVCMDLEN
            );
        }

        return strlen($bytes) >= $headers['length'];
    }

    /**
     * @template T of PDU
     *
     * @param string $bytes
     *
     * @throws CannotParseFrame
     *
     * @return T
     */
    public static function parse(string $bytes): PDU
    {
        if (strlen($bytes) < self::HEADER_LEN) {
            throw CannotParseFrame::withCommandStatus(
                CommandStatus::ESME_RINVMSGLEN
            );
        }

        /** @var PduHeader|false $headers */
        $headers = @unpack(self::HEADER_FORMAT, $bytes);

        // @codeCoverageIgnoreStart
        if (false === $headers) {
            throw CannotParseFrame::withCommandStatus(
                CommandStatus::ESME_RINVCMDLEN
            );

            // @codeCoverageIgnoreEnd
        }

        $commands = self::commands();

        $commandType = $headers['command'];

        if (!isset($commands[$commandType])) {
            throw CannotParseFrame::withCommandStatus(
                CommandStatus::ESME_RINVCMDID,
                $headers['sequence'] ?? 0,
            );
        }

        /** @var PDU $command */
        $command = $commands[$commandType];

        /** @var T */
        return $command::reconstitute(
            CommandStatus::tryFrom($headers['status']) ?: CommandStatus::ESME_ROK,
            new Buffer(substr($bytes, 16, $headers['length'])),
        )->withSequence($headers['sequence']);
    }

    /**
     * @return array<int, class-string<PDU>>
     */
    private static function commands(): array
    {
        return [
            Command::BIND_RECEIVER->value => Command\BindReceiver::class,
            Command::BIND_RECEIVER_RESP->value =>  Command\BindReceiverResp::class,
            Command::BIND_TRANSMITTER->value => Command\BindTransmitter::class,
            Command::BIND_TRANSMITTER_RESP->value => Command\BindTransmitterResp::class,
            Command::BIND_TRANSCEIVER->value => Command\BindTransceiver::class,
            Command::BIND_TRANSCEIVER_RESP->value => Command\BindTransceiverResp::class,
            Command::QUERY_SM->value => Command\QuerySm::class,
            Command::QUERY_SM_RESP->value => Command\QuerySmResp::class,
            Command::SUBMIT_SM->value => Command\SubmitSm::class,
            Command::SUBMIT_SM_RESP->value => Command\SubmitSmResp::class,
            Command::DELIVER_SM->value => Command\DeliverSm::class,
            Command::DELIVER_SM_RESP->value => Command\DeliverSmResp::class,
            Command::UNBIND->value => Command\Unbind::class,
            Command::UNBIND_RESP->value => Command\UnbindResp::class,
            Command::REPLACE_SM->value => Command\ReplaceSm::class,
            Command::REPLACE_SM_RESP->value => Command\ReplaceSmResp::class,
            Command::CANCEL_SM->value => Command\CancelSm::class,
            Command::CANCEL_SM_RESP->value => Command\CancelSmResp::class,
            Command::OUTBIND->value => Command\Outbind::class,
            Command::ENQUIRE_LINK->value => Command\EnquireLink::class,
            Command::ENQUIRE_LINK_RESP->value => Command\EnquireLinkResp::class,
            Command::ALERT_NOTIFICATION->value => Command\AlertNotification::class,
            Command::DATA_SM->value => Command\DataSm::class,
            Command::DATA_SM_RESP->value => Command\DataSmResp::class,
            Command::GENERIC_NACK->value => Command\GenericNack::class,
        ];
    }
}
