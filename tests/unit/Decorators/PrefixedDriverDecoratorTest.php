<?php

declare(strict_types=1);

namespace StellarWP\Memoize\Tests\Unit\Decorators;

use PHPUnit\Framework\MockObject\MockObject;
use StellarWP\Memoize\Contracts\DriverInterface;
use StellarWP\Memoize\Decorators\PrefixedDriverDecorator;
use StellarWP\Memoize\Tests\Helper\MemoizeTestCase;

final class PrefixedDriverDecoratorTest extends MemoizeTestCase {

    /**
     * @var DriverInterface|(object&MockObject)|MockObject|(DriverInterface&object&MockObject)|(DriverInterface&MockObject)
     */
    private DriverInterface $memoryDriver;
    private PrefixedDriverDecorator $driver;

    protected function setUp(): void {
        parent::setUp();

        $this->memoryDriver = $this->createMock( DriverInterface::class );
        $this->driver       = new PrefixedDriverDecorator($this->memoryDriver);
    }

    public function testItGetsTheDefaultPrefix(): void {
        $driver = new PrefixedDriverDecorator($this->memoryDriver);

        $this->assertSame( 'stellarwp', $driver->prefix());
    }

    public function testItTrimsPrefix(): void {
        $driver = new PrefixedDriverDecorator($this->memoryDriver, ' my_custom_prefix ');

        $this->assertSame( 'my_custom_prefix', $driver->prefix());
    }

    public function testItAllowsSettingDifferentPrefixes(): void {
        $prefixes = [
            'bork',
            'blarg',
            'moo',
            '000',
        ];

        foreach ($prefixes as $prefix) {
            $driver = new PrefixedDriverDecorator($this->memoryDriver, $prefix);

            $this->assertSame($prefix, $driver->prefix());
        }
    }

    public function testSetsSimpleValue(): void {
        $this->memoryDriver->expects( $this->once() )
                           ->method('set')
                           ->with('stellarwp.foo', 'bar');

        $this->driver->set('foo', 'bar');
    }

    public function testSetsDeepValue() {
        $this->memoryDriver->expects( $this->once() )
                           ->method('set')
                           ->with('stellarwp.foo.bar.bork.blarg.moo', 'baz');

        $this->driver->set('foo.bar.bork.blarg.moo', 'baz');
    }

    public function testForgetsEverything() {
        $this->memoryDriver->expects( $this->once() )
                           ->method('set')
                           ->with('stellarwp.foo.bar.bork.blarg.moo', 'baz');

        $this->memoryDriver->expects( $this->once() )
                           ->method('forget')
                           ->with(null);

        $expected = [
            [ 'stellarwp.foo.bar.bork.blarg.moo' ],
            [ 'stellarwp.foo.bar.bork.blarg' ],
            [ 'stellarwp.foo.bar.bork' ],
            [ 'stellarwp.foo.bar' ],
            [ 'stellarwp.foo' ],
        ];

        $count = $this->exactly( count( $expected ) );

        $this->memoryDriver->expects( $count )
                           ->method( 'has' )
                           ->willReturnCallback(
                               function( ...$args ) use ( $count, $expected ) {
                                   // PHPUnit 10+
                                   if ( method_exists( $count, 'numberOfInvocations' ) ) {
                                       $index = $count->numberOfInvocations() - 1;
                                   } else {
                                       $index = $count->getInvocationCount() - 1;
                                   }

                                   $this->assertEquals( $expected[ $index ], $args );

                                   return false;
                               }
                           );

        $this->driver->set('foo.bar.bork.blarg.moo', 'baz');
        $this->driver->forget();
        $this->assertFalse($this->driver->has('foo.bar.bork.blarg.moo'));
        $this->assertFalse($this->driver->has('foo.bar.bork.blarg'));
        $this->assertFalse($this->driver->has('foo.bar.bork'));
        $this->assertFalse($this->driver->has('foo.bar'));
        $this->assertFalse($this->driver->has('foo'));
    }

    public function testForgetsLeaves(): void {
        $this->memoryDriver->expects( $this->exactly(2) )
                           ->method( 'set' )
                           ->willReturnCallback(
                               function( string $key, $value ): void {
                                  $this->assertThat($key, $this->logicalOr(
                                      $this->equalTo( 'stellarwp.foo.bar.bork.blarg.moo' ),
                                      $this->equalTo( 'stellarwp.foo.bar.bork.blarg.oink' ),
                                  ));

                                   $this->assertThat($value, $this->logicalOr(
                                       $this->equalTo( 'baz' ),
                                       $this->equalTo( 'lol' ),
                                   ));
                               }
                           );

        $this->memoryDriver->expects( $this->once() )
                           ->method('forget')
                           ->with('stellarwp.foo.bar.bork.blarg.moo');

        $this->memoryDriver->expects( $this->exactly(2) )
                           ->method( 'has' )
                           ->willReturnCallback(
                               function( string $key ): bool {
                                   $this->assertThat($key, $this->logicalOr(
                                       $this->equalTo( 'stellarwp.foo.bar.bork.blarg.moo' ),
                                       $this->equalTo( 'stellarwp.foo.bar.bork.blarg.oink' ),
                                   ));

                                   return false;
                               }
                           );

        $this->driver->set('foo.bar.bork.blarg.moo', 'baz');
        $this->driver->set('foo.bar.bork.blarg.oink', 'lol');
        $this->driver->forget('foo.bar.bork.blarg.moo');
        $this->assertFalse($this->driver->has('foo.bar.bork.blarg.moo'));
        $this->assertFalse($this->driver->has('foo.bar.bork.blarg.oink'));
    }

    public function testForgetsBranches(): void {
        $this->memoryDriver->expects( $this->exactly(2) )
                           ->method( 'set' )
                           ->willReturnCallback(
                               function( string $key, $value ): void {
                                   $this->assertThat($key, $this->logicalOr(
                                       $this->equalTo( 'stellarwp.foo.bar.bork.blarg.moo' ),
                                       $this->equalTo( 'stellarwp.foo.bar.bork.blarg.oink' ),
                                   ));

                                   $this->assertThat($value, $this->logicalOr(
                                       $this->equalTo( 'baz' ),
                                       $this->equalTo( 'lol' ),
                                   ));
                               }
                           );

        $this->memoryDriver->expects( $this->once() )
                           ->method('forget')
                           ->with('stellarwp.foo.bar.bork');

        $this->memoryDriver->expects( $this->exactly(2) )
                           ->method( 'has' )
                           ->willReturnCallback(
                               function( string $key ): bool {
                                   $this->assertThat($key, $this->logicalOr(
                                       $this->equalTo( 'stellarwp.foo.bar.bork' ),
                                       $this->equalTo( 'stellarwp.foo.bar' ),
                                   ));

                                   return false;
                               }
                           );

        $this->driver->set('foo.bar.bork.blarg.moo', 'baz');
        $this->driver->set('foo.bar.bork.blarg.oink', 'lol');
        $this->driver->forget('foo.bar.bork');
        $this->assertFalse($this->driver->has('foo.bar.bork'));
        $this->assertFalse($this->driver->has('foo.bar'));
    }

    public function testForgetsSimpleValues(): void {
        $this->memoryDriver->expects( $this->exactly(2) )
                           ->method( 'set' )
                           ->willReturnCallback(
                               function( string $key, $value ): void {
                                   $this->assertThat($key, $this->logicalOr(
                                       $this->equalTo( 'stellarwp.foo' ),
                                       $this->equalTo( 'stellarwp.bork' ),
                                   ));

                                   $this->assertThat($value, $this->logicalOr(
                                       $this->equalTo( 'baz' ),
                                       $this->equalTo( 'lol' ),
                                   ));
                               }
                           );

        $this->memoryDriver->expects( $this->once() )
                           ->method('forget')
                           ->with('stellarwp.foo');

        $this->memoryDriver->expects( $this->exactly(2) )
                           ->method( 'has' )
                           ->willReturnCallback(
                               function( string $key ): bool {
                                   $this->assertThat($key, $this->logicalOr(
                                       $this->equalTo( 'stellarwp.foo' ),
                                       $this->equalTo( 'stellarwp.bork' ),
                                   ));

                                   return false;
                               }
                           );

        $this->driver->set('foo', 'baz');
        $this->driver->set('bork', 'lol');
        $this->driver->forget('foo');
        $this->assertFalse($this->driver->has('foo'));
        $this->assertFalse($this->driver->has('bork'));
    }
}
