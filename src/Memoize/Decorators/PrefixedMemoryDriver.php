<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Decorators;

use StellarWP\Memoize\Contracts\DriverInterface;
use StellarWP\Memoize\Drivers\MemoryDriver;

/**
 * A Prefixed Memory Driver Decorator which automatically prefixes
 * all keys with the provided prefix.
 */
final class PrefixedMemoryDriver implements DriverInterface
{
    private MemoryDriver $driver;

    /**
     * The prefix for keys.
     */
    private string $prefix;

    /**
     * @param string $prefix Prefix all keys.
     */
    public function __construct(MemoryDriver $driver, string $prefix = 'stellarwp')
    {
        $this->driver = $driver;
        $this->prefix = $prefix;
    }

    /**
     * Get the set prefix.
     *
     * @return string
     */
    public function prefix(): string
    {
        return $this->prefix;
    }

    /**
     * @inheritDoc
     */
    public function get(?string $key = null)
    {
        return $this->driver->get($this->getKey($key));
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): void
    {
        $this->driver->set($this->getKey($key), $value);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->driver->has($this->getKey($key));
    }

    /**
     * @inheritDoc
     */
    public function forget(?string $key = null): void
    {
        $this->driver->forget($this->getKey($key));
    }

    /**
     * Build a prefixed key.
     *
     * @param string|null $key The original key requested.
     *
     * @return string The prefixed key.
     */
    private function getKey(?string $key = null): ?string
    {
        return $key ? "$this->prefix.$key" : null;
    }
}
