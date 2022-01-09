<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

enum Command: int
{
    case BIND_RECEIVER = 0x00000001;
    case BIND_RECEIVER_RESP = 0x80000001;
    case BIND_TRANSMITTER = 0x00000002;
    case BIND_TRANSMITTER_RESP = 0x80000002;
    case BIND_TRANSCEIVER = 0x00000009;
    case BIND_TRANSCEIVER_RESP = 0x80000009;
    case GENERIC_NACK = 0x80000000;
    case QUERY_SM = 0x00000003;
    case QUERY_SM_RESP = 0x80000003;
    case SUBMIT_SM = 0x00000004;
    case SUBMIT_SM_RESP = 0x80000004;
    case DELIVER_SM = 0x00000005;
    case DELIVER_SM_RESP = 0x80000005;
    case UNBIND = 0x00000006;
    case UNBIND_RESP = 0x80000006;
    case REPLACE_SM = 0x00000007;
    case REPLACE_SM_RESP = 0x80000007;
    case CANCEL_SM = 0x00000008;
    case CANCEL_SM_RESP = 0x80000008;
    case OUTBIND = 0x0000000B;
    case ENQUIRE_LINK = 0x00000015;
    case ENQUIRE_LINK_RESP = 0x80000015;
    case SUBMIT_MULTI = 0x00000021;
    case SUBMIT_MULTI_RESP = 0x80000021;
    case ALERT_NOTIFICATION = 0x00000102;
    case DATA_SM = 0x00000103;
    case DATA_SM_RESP = 0x80000103;
    case UNKNOWN = 0x00000999;

    public static function fromInt(int $id): Command
    {
        $command = self::tryFrom($id);

        if ($command !== null) {
            return $command;
        }

        return Command::UNKNOWN;
    }
}
