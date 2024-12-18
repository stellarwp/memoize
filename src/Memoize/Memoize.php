<?php

declare(strict_types=1);

namespace StellarWP\Memoize;

final class Memoize
{
    /**
     * Get a value from the memoization cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be returned.
     * @return mixed
     */
    public static function get(?string $key = null)
    {
        return Config::getDriver()->get($key);
    }

    /**
     * Set a value in the memoization cache.
     *
     * @param string $key The cache key using dot notation.
     * @param mixed $value The value to store in the cache.
     * @return void
     */
    public static function set(string $key, $value): void
    {
        Config::getDriver()->set($key, $value);
    }

    /**
     * Check if a key exists in the memoization cache.
     *
     * @param string $key The cache key using dot notation.
     * @return boolean
     */
    public static function has(string $key): bool
    {
        return Config::getDriver()->has($key);
    }

    /**
     * Remove a key from the memoization cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be cleared.
     * @return void
     */
    public static function forget(?string $key = null): void
    {
        Config::getDriver()->forget($key);
    }
}
