<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Tests\Unit\Decorators;

use StellarWP\Memoize\Decorators\PrefixedMemoryDriver;
use StellarWP\Memoize\Drivers\MemoryDriver;
use StellarWP\Memoize\Tests\Helper\MemoizeTestCase;

final class PrefixedMemoryDriverTest extends MemoizeTestCase {

    private MemoryDriver $memoryDriver;

    protected function setUp(): void {
        parent::setUp();

        $this->memoryDriver = new MemoryDriver();
    }

    public function testItGetsTheDefaultPrefix(): void {
        $driver = new PrefixedMemoryDriver($this->memoryDriver);

        $this->assertSame( 'stellarwp', $driver->prefix());
    }

    public function testItAllowsSettingDifferentPrefixes(): void {
        $prefixes = [
            'bork',
            'blarg',
            'moo',
            '000',
        ];

        foreach ($prefixes as $prefix) {
            $driver = new PrefixedMemoryDriver($this->memoryDriver, $prefix);

            $this->assertSame($prefix, $driver->prefix());
        }
    }
}
