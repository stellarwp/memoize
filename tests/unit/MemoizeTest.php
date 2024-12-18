<?php
namespace StellarWP\Memoize;

use StellarWP\Memoize\Tests\Helper\MemoizeTestCase;
use StellarWP\Memoize\Config;
use StellarWP\Memoize\Memoize;
use StellarWP\Memoize\Drivers\MemoryDriver;
use StellarWP\Memoize\Drivers\WpCacheDriver;

class MemoizeTest extends MemoizeTestCase
{

    public function _setUp(): void
    {
        Config::setNamespace('test');
    }

    public function _tearDown(): void
    {
        Memoize::forget();
    }

    public function memoizationDrivers()
    {
        return [
            'MemoryDriver' => ['driver' => new MemoryDriver()],
            'WpCacheDriver' => ['driver' => new WpCacheDriver()],
        ];
    }

    /**
     * @dataProvider memoizationDrivers
     */
    public function testSetsSimpleValue($driver)
    {
        Config::setDriver($driver);
        Memoize::set('foo', 'bar');
        $this->assertEquals('bar', Memoize::get('foo'));
    }

    /**
     * @dataProvider memoizationDrivers
     */
    public function testSetsDeepValue($driver)
    {
        Config::setDriver($driver);
        Memoize::set('foo.bar.bork.blarg.moo', 'baz');
        $this->assertEquals('baz', Memoize::get('foo.bar.bork.blarg.moo'));
    }

    /**
     * @dataProvider memoizationDrivers
     */
    public function testForgetsEverything($driver)
    {
        Config::setDriver($driver);
        Memoize::set('foo.bar.bork.blarg.moo', 'baz');
        Memoize::forget();
        $this->assertFalse(Memoize::has('foo.bar.bork.blarg.moo'));
        $this->assertFalse(Memoize::has('foo.bar.bork.blarg'));
        $this->assertFalse(Memoize::has('foo.bar.bork'));
        $this->assertFalse(Memoize::has('foo.bar'));
        $this->assertFalse(Memoize::has('foo'));
    }

    /**
     * @dataProvider memoizationDrivers
     */
    public function testForgetsLeaves($driver)
    {
        Config::setDriver($driver);
        Memoize::set('foo.bar.bork.blarg.moo', 'baz');
        Memoize::set('foo.bar.bork.blarg.oink', 'lol');
        Memoize::forget('foo.bar.bork.blarg.moo');
        $this->assertFalse(Memoize::has('foo.bar.bork.blarg.moo'));
        $this->assertTrue(Memoize::has('foo.bar.bork.blarg.oink'));
    }

    /**
     * @dataProvider memoizationDrivers
     */
    public function testForgetsBranches($driver)
    {
        Config::setDriver($driver);
        Memoize::set('foo.bar.bork.blarg.moo', 'baz');
        Memoize::set('foo.bar.bork.blarg.oink', 'lol');
        Memoize::forget('foo.bar.bork');
        $this->assertFalse(Memoize::has('foo.bar.bork'));
        $this->assertTrue(Memoize::has('foo.bar'));
    }

    /**
     * @dataProvider memoizationDrivers
     */
    public function testForgetsSimpleValues($driver)
    {
        Config::setDriver($driver);
        Memoize::set('foo', 'baz');
        Memoize::set('bork', 'lol');
        Memoize::forget('foo');
        $this->assertFalse(Memoize::has('foo'));
        $this->assertTrue(Memoize::has('bork'));
    }

}

