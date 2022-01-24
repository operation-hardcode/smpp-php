<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Interaction\Extensions;

use Amp;

interface AfterConnectionClosedExtension
{
    /**
     * @psalm-return Amp\Promise<void>
     */
    public function afterConnectionClosed(?\Throwable $e = null): Amp\Promise;
}
