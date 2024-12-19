<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Contracts;

interface MemoizerInterface
{
    /**
     * Get a value from the memoization cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be returned.
     * @return mixed
     */
    public function get(?string $key = null);

    /**
     * Set a value in the memoization cache.
     *
     * @param string $key The cache key using dot notation.
     * @param mixed $value The value to store in the cache.
     * @return void
     */
    public function set(string $key, $value): void;

    /**
     * Check if a key exists in the memoization cache.
     *
     * @param string $key The cache key using dot notation.
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove a key from the memoization cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be cleared.
     * @return void
     */
    public function forget(?string $key = null): void;
}
