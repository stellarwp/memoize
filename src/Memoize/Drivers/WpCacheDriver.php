<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers;

use Closure;
use StellarWP\Arrays\Arr;
use StellarWP\Memoize\Config;

class WpCacheDriver implements Contracts\DriverInterface
{
    use Traits\ClosureResolver;

    /**
     * @var string
     */
    protected string $cacheKey = 'stellarwp-memoize';

    /**
     * @var string
     */
    protected string $closure_cache_pointer = 'STELLARWP_MEMOIZE_CLOSURE';

    /**
     * Holds the closures in memory because we cannot store them in wp_cache.
     *
     * @var array
     */
    protected static array $closure_cache = [];

    /**
     * @return string
     */
    protected function getCacheKey(): string
    {
        return Config::getNamespace() . '-' . $this->cacheKey;
    }

    /**
     * @return array
     */
    protected function getWpCache(): array
    {
        $cache = wp_cache_get($this->getCacheKey());

        if (!$cache) {
            $cache = [];
        }

        return $cache;
    }

    /**
     * @inheritDoc
     */
    public function get(?string $key = null)
    {
        $cache = $this->getWpCache();

        if (!$key) {
            if (count(static::$closure_cache) > 0) {
                static::$closure_cache = $this->recursivelyResolveClosures(static::$closure_cache);
                $cache = Arr::merge_recursive($cache, static::$closure_cache);

                static::$closure_cache = [];

                wp_cache_set($this->getCacheKey(), $cache);
            }

            return $cache;
        }

        $value         = Arr::get($cache, $key);
        $closure_value = Arr::get(static::$closure_cache, $key, null);

        if (is_array($closure_value)) {
            $closure_value = $this->recursivelyResolveClosures($closure_value);
            $value = Arr::merge_recursive($value, $closure_value);
            $this->set($key, $value);
            Arr::forget(static::$closure_cache, $key);
            return $value;
        } elseif ($closure_value) {
            $value = $closure_value();
            $this->set($key, $value);
            Arr::forget(static::$closure_cache, $key);
            return $value;
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): void
    {
        // If the value is a Closure, we need to store it in memory cache and store a value
        // in wp_cache that indicates we should look for it there.
        if ($value instanceof Closure) {
            static::$closure_cache = Arr::set(static::$closure_cache, explode('.', $key), $value);
            return;
        }

        $cache = $this->getWpCache();
        $cache = Arr::set($cache, explode('.', $key), $value);
        wp_cache_set($this->getCacheKey(), $cache);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        $cache = $this->getWpCache();
        return Arr::has($cache, $key);
    }

    /**
     * @inheritDoc
     */
    public function forget(?string $key = null): void
    {
        $cache = $this->getWpCache();

        if ($key) {
            Arr::forget($cache, $key);
        } else {
            $cache = [];
        }

        wp_cache_set($this->getCacheKey(), $cache);
    }
}
