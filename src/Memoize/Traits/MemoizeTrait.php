<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Traits;

use Closure;
use InvalidArgumentException;
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
     * @throws InvalidArgumentException If the key is an empty string.
     *
     * @return mixed
     */
    public function get(?string $key)
    {
        if ($key === null) {
            return static::$cached;
        }

        if ($key === '') {
            throw new InvalidArgumentException('Memoize key cannot be an empty string');
        }

        return Arr::get(static::$cached, $key);
    }

    /**
     * Set a value in the cache.
     *
     * @param string $key The cache key using dot notation.
     * @param mixed $value The value to store in the cache.
     *
     * @throws InvalidArgumentException If the key is an empty string.
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Memoize key cannot be an empty string');
        }

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
     * @throws InvalidArgumentException If the key is an empty string.
     *
     * @return boolean
     */
    public function has(string $key): bool
    {
        if ($key === '') {
            throw new InvalidArgumentException('Memoize key cannot be an empty string');
        }

        return Arr::has(static::$cached, $key);
    }

    /**
     * Remove a key from the cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be cleared.
     *
     * @throws InvalidArgumentException If the key is an empty string.
     *
     * @return void
     */
    public function forget(?string $key): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Memoize key cannot be an empty string');
        }

        if ($key !== null) {
            Arr::forget(static::$cached, $key);
        } else {
            static::$cached = [];
        }
    }
}
