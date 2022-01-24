<?php

declare(strict_types=1);

namespace OperationHardcode\Smpp\Protocol;

/**
 * @psalm-immutable
 */
final class Destination
{
    public function __construct(
        public readonly string|null $value,
        public readonly TON|null $ton = TON::INTERNATIONAL,
        public readonly NPI|null $npi = NPI::UNKNOWN,
    ) {
    }
}
