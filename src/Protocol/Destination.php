<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

/**
 * @psalm-immutable
 */
final class Destination
{
    public function __construct(
        public readonly string $value,
        public readonly TON $ton = TON::INTERNATIONAL,
        public readonly NPI $npi = NPI::UNKNOWN,
    ) {
    }
}
