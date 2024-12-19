<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers;

use Closure;
use StellarWP\Arrays\Arr;

abstract class AbstractDriver implements Contracts\DriverInterface
{
    /**
     * @inheritDoc
     */
    abstract public function get(?string $key = null);

    /**
     * @inheritDoc
     */
    abstract public function set(string $key, $value): void;

    /**
     * @inheritDoc
     */
    abstract public function has(string $key): bool;

    /**
     * @inheritDoc
     */
    abstract public function forget(?string $key = null): void;

    /**
     * Recursively resolves closures in the cache.
     *
     * @param array $cache The cache to recursively resolve closures in.
     * @return array The cache with closures resolved.
     */
    protected function recursivelyResolveClosures(array $cache): array
    {
        foreach ($cache as $key => $value) {
            if ($value instanceof Closure) {
                $cache[$key] = $value();
            }

            if (is_array($value)) {
                $cache[$key] = $this->recursivelyResolveClosures($value);
            }
        }

        return $cache;
    }
}
