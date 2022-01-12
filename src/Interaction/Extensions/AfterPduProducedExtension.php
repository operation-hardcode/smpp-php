<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction\Extensions;

use Amp;
use OperationHardcode\Smpp\Interaction\SmppExecutor;
use OperationHardcode\Smpp\Protocol\PDU;

interface AfterPduProducedExtension
{
    /**
     * @psalm-return Amp\Promise<void>
     */
    public function afterPduProduced(PDU $pdu, SmppExecutor $smppExecutor): Amp\Promise;
}
