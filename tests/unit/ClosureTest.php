<?php
namespace StellarWP\Memoize;

use StellarWP\Memoize\Tests\Helper\MemoizeTestCase;
use StellarWP\Memoize\Contracts\MemoizerInterface;
use StellarWP\Memoize\Drivers\MemoryDriver;

final class ClosureTest extends MemoizeTestCase
{
    private MemoizerInterface $memoizer;

    public function _setUp(): void
    {
        $this->memoizer = new Memoizer(new MemoryDriver());
    }

    public function testResolvesClosures()
    {
        $this->memoizer->set('foo', function () {
            return 'bar';
        });
        $this->assertEquals('bar', $this->memoizer->get('foo'));

        $this->memoizer->set('foo.baz', function () {
            return 'bar';
        });
        $this->assertEquals('bar', $this->memoizer->get('foo.baz'));
    }

    public function testResolvesClosuresRecursively()
    {
        $this->memoizer->set('one', function () {
            return 'bar';
        });
        $this->memoizer->set('two.baz', function () {
            return 'bar';
        });
        $this->memoizer->set('three.baz.bork', function () {
            return 'bar';
        });
        $this->memoizer->set('three.baz.whee', function () {
            return 'bar';
        });

        $cache = $this->memoizer->get();

        $this->assertEquals('bar', $cache['one']);
        $this->assertEquals('bar', $cache['two']['baz']);
        $this->assertEquals('bar', $cache['three']['baz']['bork']);
        $this->assertEquals('bar', $cache['three']['baz']['whee']);
    }

    public function testResolvesClosuresRecursivelyWhenGrabbingInTheMiddle()
    {
        $this->memoizer->set('bar.baz.whee', function () {
            return 'bar';
        });
        $this->memoizer->set('bar.baz.bork', 'hello');

        $cache = $this->memoizer->get('bar.baz');

        $this->assertEquals('bar', $cache['whee']);
        $this->assertEquals('hello', $cache['bork']);
    }
}

