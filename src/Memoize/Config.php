<?php

declare(strict_types=1);

namespace StellarWP\Memoize;

use StellarWP\Memoize\Drivers\Contracts\DriverInterface;
use StellarWP\Memoize\Drivers\MemoryDriver;

final class Config
{
    /**
     * @var ?DriverInterface
     */
    private static $driver = null;

    /**
     * @var ?string
     */
    private static $namespace = null;

    /**
     * @param DriverInterface $driver
     */
    public static function setDriver(DriverInterface $driver)
    {
        self::$driver = $driver;
    }

    /**
     * @return DriverInterface
     */
    public static function getDriver(): DriverInterface
    {
        if (!self::$driver) {
            self::$driver = new MemoryDriver();
        }

        return self::$driver;
    }

    /**
     * @param string $namespace
     */
    public static function setNamespace(string $namespace)
    {
        self::$namespace = $namespace;
    }

    /**
     * @return string
     */
    public static function getNamespace(): string
    {
        if (!self::$namespace) {
            throw new \RuntimeException('A namespace must be set using ' . __CLASS__ . '::setNamespace()');
        }

        return self::$namespace;
    }
}
