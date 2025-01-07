<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers;

use StellarWP\Memoize\Contracts\DriverInterface;
use StellarWP\Memoize\Traits\MemoizeTrait;

final class MemoryDriver implements DriverInterface
{
    use MemoizeTrait;
}
