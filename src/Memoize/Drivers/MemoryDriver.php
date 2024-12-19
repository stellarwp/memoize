<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers;

use Closure;
use StellarWP\Arrays\Arr;
use StellarWP\Memoize\Contracts\DriverInterface;

final class MemoryDriver implements DriverInterface
{
    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @inheritDoc
     */
    public function get(?string $key = null)
    {
        if (!$key) {
            return $this->cache;
        }

        return Arr::get($this->cache, $key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): void
    {
        if ($value instanceof Closure) {
            $value = $value();
        }

        $this->cache = Arr::set($this->cache, explode('.', $key), $value);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return Arr::has($this->cache, $key);
    }

    /**
     * @inheritDoc
     */
    public function forget(?string $key = null): void
    {
        if ($key) {
            Arr::forget($this->cache, $key);
        } else {
            $this->cache = [];
        }
    }
}
