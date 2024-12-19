<?php
namespace StellarWP\Memoize;

use Closure;
use StellarWP\Memoize\Tests\Helper\MemoizeTestCase;
use StellarWP\Memoize\Config;
use StellarWP\Memoize\Memoize;
use StellarWP\Memoize\Drivers\MemoryDriver;
use StellarWP\Memoize\Drivers\WpCacheDriver;

class ClosureTest extends MemoizeTestCase
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
    public function testResolvesClosures($driver)
    {
        Config::setDriver($driver);
        Memoize::set('foo', function () {
            return 'bar';
        });
        $this->assertEquals('bar', Memoize::get('foo'));

        Memoize::set('foo.baz', function () {
            return 'bar';
        });
        $this->assertEquals('bar', Memoize::get('foo.baz'));
    }

    /**
     * @dataProvider memoizationDrivers
     */
    public function testResolvesClosuresRecursively($driver)
    {
        Config::setDriver($driver);
        Memoize::set('one', function () {
            return 'bar';
        });
        Memoize::set('two.baz', function () {
            return 'bar';
        });
        Memoize::set('three.baz.bork', function () {
            return 'bar';
        });
        Memoize::set('three.baz.whee', function () {
            return 'bar';
        });

        $cache = Memoize::get();

        $this->assertEquals('bar', $cache['one']);
        $this->assertEquals('bar', $cache['two']['baz']);
        $this->assertEquals('bar', $cache['three']['baz']['bork']);
        $this->assertEquals('bar', $cache['three']['baz']['whee']);
    }

    /**
     * @dataProvider memoizationDrivers
     */
    public function testResolvesClosuresRecursivelyWhenGrabbingInTheMiddle($driver)
    {
        Config::setDriver($driver);
        Memoize::set('bar.baz.whee', function () {
            return 'bar';
        });
        Memoize::set('bar.baz.bork', 'hello');

        $cache = Memoize::get('bar.baz');

        $this->assertEquals('bar', $cache['whee']);
        $this->assertEquals('hello', $cache['bork']);
    }
}

