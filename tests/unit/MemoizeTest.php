<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Tests\Unit;

use InvalidArgumentException;
use StellarWP\Memoize\Contracts\MemoizerInterface;
use StellarWP\Memoize\Drivers\MemoryDriver;
use StellarWP\Memoize\Memoizer;
use StellarWP\Memoize\Tests\Helper\MemoizeTestCase;
use StellarWP\Memoize\Traits\MemoizeTrait;

final class MemoizeTest extends MemoizeTestCase
{
    /**
     * Data provider for memoize drivers.
     *
     * @return array<string, array{0: MemoizerInterface}>
     */
    public function driverProvider(): array
    {
        $memoizeTrait = new class implements MemoizerInterface {
            use MemoizeTrait;
        };

        return [
            'MemoryDriver' => [new Memoizer(new MemoryDriver())],
            'MemoizeTrait' => [$memoizeTrait],
        ];
    }

    /**
     * @dataProvider driverProvider
     */
    public function testSetsSimpleValue(MemoizerInterface $memoizer): void
    {
        $memoizer->set('foo', 'bar');
        $this->assertEquals('bar', $memoizer->get('foo'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testSetsDeepValue(MemoizerInterface $memoizer): void
    {
        $memoizer->set('foo.bar.bork.blarg.moo', 'baz');
        $this->assertEquals('baz', $memoizer->get('foo.bar.bork.blarg.moo'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testForgetsEverything(MemoizerInterface $memoizer): void
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
    public function testForgetsLeaves(MemoizerInterface $memoizer): void
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
    public function testForgetsBranches(MemoizerInterface $memoizer): void
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
    public function testForgetsSimpleValues(MemoizerInterface $memoizer): void
    {
        $memoizer->set('foo', 'baz');
        $memoizer->set('bork', 'lol');
        $memoizer->forget('foo');
        $this->assertFalse($memoizer->has('foo'));
        $this->assertTrue($memoizer->has('bork'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testItAllowsEmptyNonNullValues(MemoizerInterface $memoizer): void
    {
        $memoizer->set('0', 'baz');
        $memoizer->set('false', 'lol');
        $this->assertTrue($memoizer->has('0'));
        $this->assertTrue($memoizer->has('false'));
        $this->assertSame('baz', $memoizer->get('0'));
        $this->assertSame('lol', $memoizer->get('false'));

        $memoizer->forget('0');
        $memoizer->forget('false');
        $this->assertFalse($memoizer->has('0'));
        $this->assertFalse($memoizer->has('false'));
    }

    /**
     * @dataProvider driverProvider
     */
    public function testItThrowsExceptionSettingEmptyString(MemoizerInterface $memoizer): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Memoize key cannot be an empty string');
        $memoizer->set('', 'baz');
    }

    /**
     * @dataProvider driverProvider
     */
    public function testItThrowsExceptionGettingEmptyString(MemoizerInterface $memoizer): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Memoize key cannot be an empty string');
        $memoizer->get('');
    }

    /**
     * @dataProvider driverProvider
     */
    public function testItThrowsExceptionHasEmptyString(MemoizerInterface $memoizer): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Memoize key cannot be an empty string');
        $memoizer->has('');
    }

    /**
     * @dataProvider driverProvider
     */
    public function testItThrowsExceptionForgettingEmptyString(MemoizerInterface $memoizer): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Memoize key cannot be an empty string');
        $memoizer->forget('');
    }

}
