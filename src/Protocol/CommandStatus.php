<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

enum CommandStatus: int
{
    case ESME_ROK = 0x00000000;
    case ESME_RINVMSGLEN = 0x00000001;
    case ESME_RINVCMDLEN = 0x00000002;
    case ESME_RINVCMDID = 0x00000003;
    case ESME_RINVBNDSTS = 0x00000004;
    case ESME_RALYBND = 0x00000005;
    case ESME_RINVPRTFLG = 0x00000006;
    case ESME_RINVREGDLVFLG = 0x00000007;
    case ESME_RSYSERR = 0x00000008;
    case ESME_RINVSRCADR = 0x0000000A;
    case ESME_RINVDSTADR = 0x0000000B;
    case ESME_RINVMSGID = 0x0000000C;
    case ESME_RBINDFAIL = 0x0000000D;
    case ESME_RINVPASWD = 0x0000000E;
    case ESME_RINVSYSID = 0x0000000F;
    case ESME_RCANCELFAIL = 0x00000011;
    case ESME_RREPLACEFAIL = 0x00000013;
    case ESME_RMSGQFUL = 0x00000014;
    case ESME_RINVSERTYP = 0x00000015;
    case ESME_RINVNUMDESTS = 0x00000033;
    case ESME_RINVDLNAME = 0x00000034;
    case ESME_RINVDESTFLAG = 0x00000040;
    case ESME_RINVSUBREP = 0x00000042;
    case ESME_RINVESMCLASS = 0x00000043;
    case ESME_RCNTSUBDL = 0x00000044;
    case ESME_RSUBMITFAIL = 0x00000045;
    case ESME_RINVSRCTON = 0x00000048;
    case ESME_RINVSRCNPI = 0x00000049;
    case ESME_RINVDSTTON = 0x00000050;
    case ESME_RINVDSTNPI = 0x00000051;
    case ESME_RINVSYSTYP = 0x00000053;
    case ESME_RINVREPFLAG = 0x00000054;
    case ESME_RINVNUMMSGS = 0x00000055;
    case ESME_RTHROTTLED = 0x00000058;
    case ESME_RINVSCHED = 0x00000061;
    case ESME_RINVEXPIRY = 0x00000062;
    case ESME_RINVDFTMSGID = 0x00000063;
    case ESME_RX_T_APPN = 0x00000064;
    case ESME_RX_P_APPN = 0x00000065;
    case ESME_RX_R_APPN = 0x00000066;
    case ESME_RQUERYFAIL = 0x00000067;
    case ESME_RINVOPTPARSTREAM = 0x000000C0;
    case ESME_ROPTPARNOTALLWD = 0x000000C1;
    case ESME_RINVPARLEN = 0x000000C2;
    case ESME_RMISSINGOPTPARAM = 0x000000C3;
    case ESME_RINVOPTPARAMVAL = 0x000000C4;
    case ESME_RDELIVERYFAILURE = 0x000000FE;
    case ESME_RUNKNOWNERR = 0x000000FF;
    case ESME_UNKNOWN = 0x000001FF;

    public static function fromInt(int $scalar): CommandStatus
    {
        $status = self::tryFrom($scalar);

        if ($status !== null) {
            return $status;
        }

        return CommandStatus::ESME_UNKNOWN;
    }

    public function describe(): string
    {
        return match ($this) {
            self::ESME_ROK => 'No Error',
            self::ESME_RINVMSGLEN => 'Message Length is invalid',
            self::ESME_RINVCMDLEN => 'Command Length is invalid',
            self::ESME_RINVCMDID => 'Invalid Command ID',
            self::ESME_RINVBNDSTS => 'Incorrect BIND Status for given command',
            self::ESME_RALYBND => 'ESME Already in Bound State',
            self::ESME_RINVPRTFLG => 'Invalid Priority Flag',
            self::ESME_RINVREGDLVFLG => 'Invalid Registered Delivery Flag',
            self::ESME_RSYSERR => 'System Error',
            self::ESME_RINVSRCADR => 'Invalid Source Address',
            self::ESME_RINVDSTADR => 'Invalid Dest Addr',
            self::ESME_RINVMSGID => 'Message ID is invalid',
            self::ESME_RBINDFAIL => 'Bind Failed',
            self::ESME_RINVPASWD => 'Invalid Password',
            self::ESME_RINVSYSID => 'Invalid System ID',
            self::ESME_RCANCELFAIL => 'Cancel SM Failed',
            self::ESME_RREPLACEFAIL => 'Replace SM Failed',
            self::ESME_RMSGQFUL => 'Message Queue Full',
            self::ESME_RINVSERTYP => 'Invalid Service Type',
            self::ESME_RINVNUMDESTS => 'Invalid number of destinations',
            self::ESME_RINVDLNAME => 'Invalid Distribution List name',
            self::ESME_RINVDESTFLAG => 'Destination flag is invalid (submit_multi)',
            self::ESME_RINVSUBREP => 'Invalid ‘submit with replace’ request (i.e. submit_sm with replace_if_present_flag set)',
            self::ESME_RINVESMCLASS => 'Invalid esm_class field data',
            self::ESME_RCNTSUBDL => 'Cannot Submit to Distribution List',
            self::ESME_RSUBMITFAIL => 'submit_sm or submit_multi failed',
            self::ESME_RINVSRCTON => 'Invalid Source address TON',
            self::ESME_RINVSRCNPI => 'Invalid Source address NPI',
            self::ESME_RINVDSTTON => 'Invalid Destination address TON',
            self::ESME_RINVDSTNPI => 'Invalid Destination address NPI',
            self::ESME_RINVSYSTYP => 'Invalid system_type field',
            self::ESME_RINVREPFLAG => 'Invalid replace_if_present flag',
            self::ESME_RINVNUMMSGS => 'Invalid number of messages',
            self::ESME_RTHROTTLED => 'Throttling error (ESME has exceeded allowed message limits)',
            self::ESME_RINVSCHED => 'Invalid Scheduled Delivery Time',
            self::ESME_RINVEXPIRY => 'Invalid message validity period (Expiry time)',
            self::ESME_RINVDFTMSGID => 'Predefined Message Invalid or Not Found',
            self::ESME_RX_T_APPN => 'ESME Receiver Temporary App Error Code',
            self::ESME_RX_P_APPN => 'ESME Receiver Permanent App Error Code',
            self::ESME_RX_R_APPN => 'ESME Receiver Reject Message Error Code',
            self::ESME_RQUERYFAIL => 'query_sm request failed',
            self::ESME_RINVOPTPARSTREAM => 'Error in the optional part of the PDU Body',
            self::ESME_ROPTPARNOTALLWD => 'Optional Parameter not allowed',
            self::ESME_RINVPARLEN => 'Invalid Parameter Length',
            self::ESME_RMISSINGOPTPARAM => 'Expected Optional Parameter missing',
            self::ESME_RINVOPTPARAMVAL => 'Invalid Optional Parameter Value',
            self::ESME_RDELIVERYFAILURE => 'Delivery Failure (used for data_sm_resp)',
            default => 'Unknown Error',
        };
    }
}
