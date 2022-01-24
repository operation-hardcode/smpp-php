<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction\Extensions;

use Amp;
use OperationHardcode\Smpp\Interaction\SmppExecutor;

interface AfterConnectionEstablishedExtension
{
    public function afterConnectionEstablished(SmppExecutor $smppExecutor): Amp\Promise;
}
