<?php

declare(strict_types=1);

namespace StellarWP\Memoize;

use StellarWP\Memoize\Contracts\DriverInterface;
use StellarWP\Memoize\Contracts\MemoizerInterface;
use StellarWP\Memoize\Drivers\MemoryDriver;

/**
 * Our concrete implementation of the interface. Other developers could easily replace this
 * with a new system down the road and either build something custom, or build an Adapter
 * from another library that implements this interface.
 *
 * Then, every class in their project that depends on the interface could be replaced without
 * actually touching the code of those classes.
 */
final class Memoizer implements MemoizerInterface
{
    /**
     * @var DriverInterface
     */
    private DriverInterface $driver;

    /**
     * Constructor.
     *
     * @param ?DriverInterface $driver The driver to use for memoization.
     */
    public function __construct(DriverInterface $driver = null)
    {
        $this->driver = $driver ?? new MemoryDriver();
    }

    /**
     * Get a value from the memoization cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be returned.
     * @return mixed
     */
    public function get(?string $key = null)
    {
        return $this->driver->get($key);
    }

    /**
     * Set a value in the memoization cache.
     *
     * @param string $key The cache key using dot notation.
     * @param mixed $value The value to store in the cache.
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->driver->set($key, $value);
    }

    /**
     * Check if a key exists in the memoization cache.
     *
     * @param string $key The cache key using dot notation.
     * @return boolean
     */
    public function has(string $key): bool
    {
        return $this->driver->has($key);
    }

    /**
     * Remove a key from the memoization cache.
     *
     * @param ?string $key The cache key using dot notation. If null, the entire cache will be cleared.
     * @return void
     */
    public function forget(?string $key = null): void
    {
        $this->driver->forget($key);
    }
}