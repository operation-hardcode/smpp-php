<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

/**
 * @method static CommandStatus ESME_ROK()
 * @method static CommandStatus ESME_RINVMSGLEN()
 * @method static CommandStatus ESME_RINVCMDLEN()
 * @method static CommandStatus ESME_RINVCMDID()
 * @method static CommandStatus ESME_RINVBNDSTS()
 * @method static CommandStatus ESME_RALYBND()
 * @method static CommandStatus ESME_RINVPRTFLG()
 * @method static CommandStatus ESME_RINVREGDLVFLG()
 * @method static CommandStatus ESME_RSYSERR()
 * @method static CommandStatus ESME_RINVSRCADR()
 * @method static CommandStatus ESME_RINVDSTADR()
 * @method static CommandStatus ESME_RINVMSGID()
 * @method static CommandStatus ESME_RBINDFAIL()
 * @method static CommandStatus ESME_RINVPASWD()
 * @method static CommandStatus ESME_RINVSYSID()
 * @method static CommandStatus ESME_RCANCELFAIL()
 * @method static CommandStatus ESME_RREPLACEFAIL()
 * @method static CommandStatus ESME_RMSGQFUL()
 * @method static CommandStatus ESME_RINVSERTYP()
 * @method static CommandStatus ESME_RINVNUMDESTS()
 * @method static CommandStatus ESME_RINVDLNAME()
 * @method static CommandStatus ESME_RINVDESTFLAG()
 * @method static CommandStatus ESME_RINVSUBREP()
 * @method static CommandStatus ESME_RINVESMCLASS()
 * @method static CommandStatus ESME_RCNTSUBDL()
 * @method static CommandStatus ESME_RSUBMITFAIL()
 * @method static CommandStatus ESME_RINVSRCTON()
 * @method static CommandStatus ESME_RINVSRCNPI()
 * @method static CommandStatus ESME_RINVDSTTON()
 * @method static CommandStatus ESME_RINVDSTNPI()
 * @method static CommandStatus ESME_RINVSYSTYP()
 * @method static CommandStatus ESME_RINVREPFLAG()
 * @method static CommandStatus ESME_RINVNUMMSGS()
 * @method static CommandStatus ESME_RTHROTTLED()
 * @method static CommandStatus ESME_RINVSCHED()
 * @method static CommandStatus ESME_RINVEXPIRY()
 * @method static CommandStatus ESME_RINVDFTMSGID()
 * @method static CommandStatus ESME_RX_T_APPN()
 * @method static CommandStatus ESME_RX_P_APPN()
 * @method static CommandStatus ESME_RX_R_APPN()
 * @method static CommandStatus ESME_RQUERYFAIL()
 * @method static CommandStatus ESME_RINVOPTPARSTREAM()
 * @method static CommandStatus ESME_ROPTPARNOTALLWD()
 * @method static CommandStatus ESME_RINVPARLEN()
 * @method static CommandStatus ESME_RMISSINGOPTPARAM()
 * @method static CommandStatus ESME_RINVOPTPARAMVAL()
 * @method static CommandStatus ESME_RDELIVERYFAILURE()
 * @method static CommandStatus ESME_RUNKNOWNERR()
 * @method static CommandStatus ESME_UNKNOWN()
 */
final class CommandStatus
{
    private const ESME_ROK = 0x00000000;
    private const ESME_RINVMSGLEN = 0x00000001;
    private const ESME_RINVCMDLEN = 0x00000002;
    private const ESME_RINVCMDID = 0x00000003;
    private const ESME_RINVBNDSTS = 0x00000004;
    private const ESME_RALYBND = 0x00000005;
    private const ESME_RINVPRTFLG = 0x00000006;
    private const ESME_RINVREGDLVFLG = 0x00000007;
    private const ESME_RSYSERR = 0x00000008;
    private const ESME_RINVSRCADR = 0x0000000A;
    private const ESME_RINVDSTADR = 0x0000000B;
    private const ESME_RINVMSGID = 0x0000000C;
    private const ESME_RBINDFAIL = 0x0000000D;
    private const ESME_RINVPASWD = 0x0000000E;
    private const ESME_RINVSYSID = 0x0000000F;
    private const ESME_RCANCELFAIL = 0x00000011;
    private const ESME_RREPLACEFAIL = 0x00000013;
    private const ESME_RMSGQFUL = 0x00000014;
    private const ESME_RINVSERTYP = 0x00000015;
    private const ESME_RINVNUMDESTS = 0x00000033;
    private const ESME_RINVDLNAME = 0x00000034;
    private const ESME_RINVDESTFLAG = 0x00000040;
    private const ESME_RINVSUBREP = 0x00000042;
    private const ESME_RINVESMCLASS = 0x00000043;
    private const ESME_RCNTSUBDL = 0x00000044;
    private const ESME_RSUBMITFAIL = 0x00000045;
    private const ESME_RINVSRCTON = 0x00000048;
    private const ESME_RINVSRCNPI = 0x00000049;
    private const ESME_RINVDSTTON = 0x00000050;
    private const ESME_RINVDSTNPI = 0x00000051;
    private const ESME_RINVSYSTYP = 0x00000053;
    private const ESME_RINVREPFLAG = 0x00000054;
    private const ESME_RINVNUMMSGS = 0x00000055;
    private const ESME_RTHROTTLED = 0x00000058;
    private const ESME_RINVSCHED = 0x00000061;
    private const ESME_RINVEXPIRY = 0x00000062;
    private const ESME_RINVDFTMSGID = 0x00000063;
    private const ESME_RX_T_APPN = 0x00000064;
    private const ESME_RX_P_APPN = 0x00000065;
    private const ESME_RX_R_APPN = 0x00000066;
    private const ESME_RQUERYFAIL = 0x00000067;
    private const ESME_RINVOPTPARSTREAM = 0x000000C0;
    private const ESME_ROPTPARNOTALLWD = 0x000000C1;
    private const ESME_RINVPARLEN = 0x000000C2;
    private const ESME_RMISSINGOPTPARAM = 0x000000C3;
    private const ESME_RINVOPTPARAMVAL = 0x000000C4;
    private const ESME_RDELIVERYFAILURE = 0x000000FE;
    private const ESME_RUNKNOWNERR = 0x000000FF;
    private const ESME_UNKNOWN = 0x000001FF;

    public function __construct(
        public readonly int $status,
        public readonly string $description,
        public readonly string $name = self::ESME_UNKNOWN,
    ) {
    }

    public function is(CommandStatus|int $status): bool
    {
        if ($status instanceof CommandStatus) {
            $status = $status->status;
        }

        return $status === $this->status;
    }

    public static function __callStatic(string $name, array $arguments): CommandStatus
    {
        $r = new \ReflectionClass(CommandStatus::class);

        /** @var int|false $const */
        $const = $r->getConstant($name);

        if (false === $const) {
            throw new \InvalidArgumentException(sprintf('Unknown status "%s" was passed.', $name));
        }

        return new CommandStatus($const, self::describe($const), $name);
    }

    public static function fromInt(int $status): CommandStatus
    {
        $statuses = (new \ReflectionClass(CommandStatus::class))->getConstants();

        $name = array_flip($statuses)[$status] ?? 'ESME_UNKNOWN';

        return new CommandStatus($status, self::describe($status), $name);
    }

    private static function describe(int $error): string
    {
        return match ($error) {
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
