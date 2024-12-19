<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers\Traits;

use Closure;

trait ClosureResolver
{
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
