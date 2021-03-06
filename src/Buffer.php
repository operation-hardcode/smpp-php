<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp;

use OperationHardcode\Smpp\Protocol\Command;
use OperationHardcode\Smpp\Protocol\CommandStatus;
use PHPinnacle\Buffer\BufferOverflow;
use PHPinnacle\Buffer\ByteBuffer;

final class Buffer extends ByteBuffer
{
    private const HEADER_SIZE = 16;

    public static function new(): Buffer
    {
        return new Buffer();
    }

    public function padding(): Buffer
    {
        return $this->append(chr(0));
    }

    public function appendUint8OrNull(?int $value = null): Buffer
    {
        if ($value === null) {
            return $this->padding();
        }

        return $this->appendUint8($value);
    }

    public function appendString(string|null $value): Buffer
    {
        $value ??= '';

        /** @psalm-var string $bytes */
        $bytes = \pack("a".(\strlen($value) + 1), $value);

        return $this->append($bytes);
    }

    public function consumeString(): string
    {
        $string = '';

        /** @var array<int, int> $bytes */
        $bytes = \unpack('C*', $this->bytes());

        for ($cursor = 1; $cursor <= count($bytes); $cursor++) {
            $code = chr($bytes[$cursor]);

            if (rtrim($code, chr(0)) === '') {
                break;
            }

            $string .= $code;
        }

        try {
            $this->discard($cursor);
        } catch (BufferOverflow) {
            //
        }

        return $string;
    }

    public function toBytes(int $sequence, Command $command, ?CommandStatus $status = null): string
    {
        $body = $this->bytes();

        /** @psalm-var string $headers */
        $headers = pack('NNNN', strlen($body) + self::HEADER_SIZE, $command->value, $status?->status, $sequence);

        return $headers.$body;
    }
}
