<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

final class CannotParseFrame extends \RuntimeException
{
    public readonly CommandStatus $status;
    public readonly int $sequence;

    public function __construct(CommandStatus $status, int $sequence = 0, string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->status = $status;
        $this->sequence = $sequence;
    }

    public static function withCommandStatus(CommandStatus $status, int $sequence = 0): CannotParseFrame
    {
        return new CannotParseFrame($status, $sequence);
    }
}
