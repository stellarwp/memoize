<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Drivers;

use Closure;
use StellarWP\Arrays\Arr;

class MemoryDriver extends AbstractDriver
{
    /**
     * @var array
     */
    protected static array $cache = [];

    /**
     * @inheritDoc
     */
    public function get(?string $key = null)
    {
        if (!$key) {
            static::$cache = $this->recursivelyResolveClosures(static::$cache);

            return static::$cache;
        }

        $value = Arr::get(static::$cache, $key);

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
        static::$cache = Arr::set(static::$cache, explode('.', $key), $value);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return Arr::has(static::$cache, $key);
    }

    /**
     * @inheritDoc
     */
    public function forget(?string $key = null): void
    {
        if ($key) {
            Arr::forget(static::$cache, $key);
        } else {
            static::$cache = [];
        }
    }
}
