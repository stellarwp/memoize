<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Traits;

use Closure;
use StellarWP\Arrays\Arr;
use StellarWP\Memoize\Contracts\DriverInterface;
use StellarWP\Memoize\Contracts\MemoizerInterface;

/**
 * A memory based memoizer trait.
 *
 * @mixin MemoizerInterface
 * @mixin DriverInterface
 */
trait MemoizeTrait
{
    /**
     * @var array<string, mixed>
     */
    protected static array $cached = [];

    /**
     * Get a value from the cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be returned.
     *
     * @return mixed
     */
    public function get(?string $key = null)
    {
        if (!$key) {
            return static::$cached;
        }

        return Arr::get(static::$cached, $key);
    }

    /**
     * Set a value in the cache.
     *
     * @param string $key The cache key using dot notation.
     * @param mixed $value The value to store in the cache.
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        if ($value instanceof Closure) {
            $value = $value();
        }

        static::$cached = Arr::set(static::$cached, explode('.', $key), $value);
    }

    /**
     * Check if a key exists in the cache.
     *
     * @param string $key The cache key using dot notation.
     *
     * @return boolean
     */
    public function has(string $key): bool
    {
        return Arr::has(static::$cached, $key);
    }

    /**
     * Remove a key from the cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be cleared.
     *
     * @return void
     */
    public function forget(?string $key = null): void
    {
        if ($key) {
            Arr::forget(static::$cached, $key);
        } else {
            static::$cached = [];
        }
    }
}
