<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers;

use Closure;
use StellarWP\Arrays\Arr;
use StellarWP\Memoize\Contracts\DriverInterface;

class MemoryDriver implements DriverInterface
{
    use Traits\ClosureResolver;

    /**
     * @var array
     */
    protected array $cache = [];

    /**
     * @inheritDoc
     */
    public function get(?string $key = null)
    {
        if (!$key) {
            $this->cache = $this->recursivelyResolveClosures($this->cache);

            return $this->cache;
        }

        $value = Arr::get($this->cache, $key);

        if (is_array($value)) {
            $value = $this->recursivelyResolveClosures($value);
            $this->set($key, $value);
            return $value;
        }

        if ($value instanceof Closure) {
            $value = $value();
            $this->set($key, $value);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): void
    {
        $this->cache = Arr::set($this->cache, explode('.', $key), $value);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return Arr::has($this->cache, $key);
    }

    /**
     * @inheritDoc
     */
    public function forget(?string $key = null): void
    {
        if ($key) {
            Arr::forget($this->cache, $key);
        } else {
            $this->cache = [];
        }
    }
}
