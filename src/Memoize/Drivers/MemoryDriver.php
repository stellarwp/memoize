<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers;

use StellarWP\Arrays\Arr;

class MemoryDriver implements Contracts\DriverInterface
{
    /**
     * @var array
     */
    protected static array $cache = [];

    /**
     * @inheritDoc
     */
    public function get(?string $key = null)
    {
        if (!$key) {
            return static::$cache;
        }

        return Arr::get(static::$cache, $key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): void
    {
        static::$cache = Arr::add(static::$cache, $key, $value);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return Arr::has(static::$cache, $key);
    }

    /**
     * @inheritDoc
     */
    public function forget(?string $key = null): void
    {
        if ($key) {
            Arr::forget(static::$cache, $key);
        } else {
            static::$cache = [];
        }
    }
}
