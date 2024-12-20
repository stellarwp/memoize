<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Tests\Unit;

use StellarWP\Memoize\Contracts\MemoizerInterface;
use StellarWP\Memoize\Decorators\PrefixedMemoryDriver;
use StellarWP\Memoize\Drivers\MemoryDriver;
use StellarWP\Memoize\Memoizer;
use StellarWP\Memoize\Tests\Helper\MemoizeTestCase;

final class MemoizeTest extends MemoizeTestCase
{
    /**
     * Data provider for memoize drivers.
     *
     * @return array<string, array{0: MemoizerInterface}>
     */
    public function driverProvider(): array
    {
        return [
            'MemoryDriver' => [new Memoizer(new MemoryDriver())],
            'PrefixedMemoryDriver' => [new Memoizer(new PrefixedMemoryDriver(new MemoryDriver()))],
        ];
    }

    /**
     * @dataProvider driverProvider
     */
    public function testSetsSimpleValue(MemoizerInterface $memoizer)
    {
        $memoizer->set('foo', 'bar');
        $this->assertEquals('bar', $memoizer->get('foo'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testSetsDeepValue(MemoizerInterface $memoizer)
    {
        $memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $this->assertEquals('baz', $memoizer->get('foo.bar.bork.blarg.moo'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testForgetsEverything(MemoizerInterface $memoizer)
    {
        $memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $memoizer->forget();
        $this->assertFalse($memoizer->has('foo.bar.bork.blarg.moo'));
        $this->assertFalse($memoizer->has('foo.bar.bork.blarg'));
        $this->assertFalse($memoizer->has('foo.bar.bork'));
        $this->assertFalse($memoizer->has('foo.bar'));
        $this->assertFalse($memoizer->has('foo'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testForgetsLeaves(MemoizerInterface $memoizer)
    {
        $memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $memoizer->set('foo.bar.bork.blarg.oink', 'lol');
        $memoizer->forget('foo.bar.bork.blarg.moo');
        $this->assertFalse($memoizer->has('foo.bar.bork.blarg.moo'));
        $this->assertTrue($memoizer->has('foo.bar.bork.blarg.oink'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testForgetsBranches(MemoizerInterface $memoizer)
    {
        $memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $memoizer->set('foo.bar.bork.blarg.oink', 'lol');
        $memoizer->forget('foo.bar.bork');
        $this->assertFalse($memoizer->has('foo.bar.bork'));
        $this->assertTrue($memoizer->has('foo.bar'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testForgetsSimpleValues(MemoizerInterface $memoizer)
    {
        $memoizer->set('foo', 'baz');
        $memoizer->set('bork', 'lol');
        $memoizer->forget('foo');
        $this->assertFalse($memoizer->has('foo'));
        $this->assertTrue($memoizer->has('bork'));
    }
}
