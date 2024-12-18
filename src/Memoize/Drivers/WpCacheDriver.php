<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers;

use StellarWP\Arrays\Arr;
use StellarWP\Memoize\Config;

class WpCacheDriver implements Contracts\DriverInterface
{
    /**
     * @var string
     */
    protected string $cacheKey = 'stellarwp-memoize';

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
            return $cache;
        }

        return Arr::get($cache, $key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): void
    {
        $cache = $this->getWpCache();
        $cache = Arr::add($cache, $key, $value);
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
