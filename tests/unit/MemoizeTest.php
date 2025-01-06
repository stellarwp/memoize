<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Tests\Unit;

use StellarWP\Memoize\Contracts\MemoizerInterface;
use StellarWP\Memoize\Drivers\MemoryDriver;
use StellarWP\Memoize\Memoizer;
use StellarWP\Memoize\Tests\Helper\MemoizeTestCase;

final class MemoizeTest extends MemoizeTestCase
{
    private MemoizerInterface $memoizer;

    public function _setUp(): void
    {
        $this->memoizer = new Memoizer(new MemoryDriver());
    }

    public function testSetsSimpleValue(): void
    {
        $this->memoizer->set('foo', 'bar');
        $this->assertEquals('bar', $this->memoizer->get('foo'));
    }

    public function testSetsDeepValue(): void
    {
        $this->memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $this->assertEquals('baz', $this->memoizer->get('foo.bar.bork.blarg.moo'));
    }

    public function testForgetsEverything(): void
    {
        $this->memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $this->memoizer->forget();
        $this->assertFalse($this->memoizer->has('foo.bar.bork.blarg.moo'));
        $this->assertFalse($this->memoizer->has('foo.bar.bork.blarg'));
        $this->assertFalse($this->memoizer->has('foo.bar.bork'));
        $this->assertFalse($this->memoizer->has('foo.bar'));
        $this->assertFalse($this->memoizer->has('foo'));
    }

    public function testForgetsLeaves(): void
    {
        $this->memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $this->memoizer->set('foo.bar.bork.blarg.oink', 'lol');
        $this->memoizer->forget('foo.bar.bork.blarg.moo');
        $this->assertFalse($this->memoizer->has('foo.bar.bork.blarg.moo'));
        $this->assertTrue($this->memoizer->has('foo.bar.bork.blarg.oink'));
    }

    public function testForgetsBranches(): void
    {
        $this->memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $this->memoizer->set('foo.bar.bork.blarg.oink', 'lol');
        $this->memoizer->forget('foo.bar.bork');
        $this->assertFalse($this->memoizer->has('foo.bar.bork'));
        $this->assertTrue($this->memoizer->has('foo.bar'));
    }

    public function testForgetsSimpleValues(): void
    {
        $this->memoizer->set('foo', 'baz');
        $this->memoizer->set('bork', 'lol');
        $this->memoizer->forget('foo');
        $this->assertFalse($this->memoizer->has('foo'));
        $this->assertTrue($this->memoizer->has('bork'));
    }

}

