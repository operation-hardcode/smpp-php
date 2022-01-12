<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction\Extensions;

use Amp;
use OperationHardcode\Smpp\Interaction\SmppExecutor;

interface AfterConnectionClosedExtension
{
    /**
     * @psalm-return Amp\Promise<void>
     */
    public function afterConnectionClosed(SmppExecutor $smppExecutor): Amp\Promise;
}
