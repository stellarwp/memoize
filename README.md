# Stellar Memoize

[![Tests](https://github.com/stellarwp/memoize/workflows/Tests/badge.svg)](https://github.com/stellarwp/memoize/actions?query=branch%3Amain) [![PHPCS](https://github.com/stellarwp/memoize/actions/workflows/phpcs.yml/badge.svg)](https://github.com/stellarwp/memoize/actions/workflows/phpcs.yml) [![PHPStan](https://github.com/stellarwp/memoize/actions/workflows/phpstan.yml/badge.svg)](https://github.com/stellarwp/memoize/actions/workflows/phpstan.yml) [![Compatibility](https://github.com/stellarwp/memoize/actions/workflows/compatibility.yml/badge.svg)](https://github.com/stellarwp/memoize/actions/workflows/compatibility.yml)

A flexible memoization library for memory caching.

## Table of Contents

- [Memoization](#memoization)
- [Installation](#installation)
- [Notes on examples](#notes-on-examples)
- [Usage](#usage)
  - [Simple example](#simple-example)
  - [Setting a nested structure](#setting-a-nested-structure)
  - [Purging a nested structure](#purging-a-nested-structure)
  - [Caching with closures](#caching-with-closures)
  - [Using with a dependency injection container](#using-with-a-dependency-injection-container)
- [Drivers](#drivers)
  - [MemoryDriver](#memorydriver)

## Memoization

[Memoization](https://en.wikipedia.org/wiki/Memoization) is the process of caching the results of expensive function calls so that they can be reused when the same inputs occur again.

## Installation

It's recommended that you install Memoize as a project dependency via [Composer](https://getcomposer.org/):

```bash
composer require stellarwp/memoize
```

> We _actually_ recommend that this library gets included in your project using [Strauss](https://github.com/BrianHenryIE/strauss).
>
> Luckily, adding Strauss to your `composer.json` is only slightly more complicated than adding a typical dependency, so checkout our [strauss docs](https://github.com/stellarwp/global-docs/blob/main/docs/strauss-setup.md).

## Notes on examples

All namespaces within the examples will be using the `StellarWP\Memoize`, however, if you are using Strauss, you will need to prefix these namespaces with your project's namespace.

## Usage

### Simple example

```php
use StellarWP\Memoize\Memoizer;

$memoizer = new Memoizer();

$memoizer->set('foo', 'bar');

if ($memoizer->has('foo')) {
    echo $memoizer->get('foo'); // Outputs: bar
}

// Unsets foo from the memoization cache.
$memoizer->forget('foo');
```

### Setting a nested structure

Memoize allows you to use dot notation to set, get, and forget values from a nested structure. This allows you to easily add/fetch values and then purge individual items or whole collections of items.

```php
use StellarWP\Memoize\Memoizer;

$memoizer = new Memoizer();

$memoizer->set('foo.bar.bork', 'baz');

// This results in the following cache:
// [
//     'foo' => [
//         'bar' => [
//             'bork' => 'baz',
//         ],
//     ],
// ]

// You can fetch the value like so:
$value = $memoizer->get('foo.bar.bork');
echo $value; // Outputs: baz

// You can fetch anywhere up the chain:
$value = $memoizer->get('foo.bar');
echo $value; // Outputs: [ 'bork' => 'baz' ]

$value = $memoizer->get('foo');
echo $value; // Outputs: [ 'bar' => [ 'bork' => 'baz' ] ]

$value = $memoizer->get();
echo $value; // Outputs: [ 'foo' => [ 'bar' => [ 'bork' => 'baz' ] ] ]
```

#### Purging a nested structure

```php
use StellarWP\Memoize\Memoizer;

$memoizer = new Memoizer();

$memoizer->set('foo.bar.bork', 'baz');
$memoizer->forget('foo.bar.bork');

// This results in the following cache:
// [
//     'foo' => [
//         'bar' => [],
//     ],
// ]

$memoizer->forget('foo.bar');

// This results in the following cache:
// [
//     'foo' => [],
// ]

$memoizer->forget('foo');

// This results in the following cache:
// []

$memoizer->forget();

// This results in the following cache:
// []
```

### Caching with closures

Memoize also supports using closures as values that get resolved before setting in the cache.

```php
use StellarWP\Memoize\Memoizer;

$memoizer = new Memoizer();

$memoizer->set('foo', static function () {
    return 'bar';
});

echo $memoizer->get('foo'); // Outputs: bar
```

### Using with a dependency injection container

#### Project class

```php
<?php

declare(strict_types=1);

namespace StellarWP\MyProject;

use StellarWP\Memoize\MemoizerInterface;

// Dependencies automatically auto-wired due to the definitions in ServiceProvider.php via
// $this->container->get( MyProjectClass::class )

/**
 * An example class inside your project using the Memoize library.
 */
class MyProjectClass
{

    private MemoizerInterface $memoizer;

    public function __construct( MemoizerInterface $memoizer )
    {
        $this->memoizer = $memoizer;
    }

    public function get( string $name ): string
    {
        $result = $this->memoizer->get( $name );

        if ( ! $result ) {
            $result = 'some very expensive operation';

            $this->memoizer->set( $name, $result );
        }

        return $result;
    }

    public function delete( string $name ): bool
    {
        $this->memoizer->forget( $name );

        // Run delete operation...

        return true;
    }
}
```

#### Service Provider

```php
<?php

declare(strict_types=1);

namespace StellarWP\Memoize;

use StellarWP\ContainerContract\ContainerInterface;
use StellarWP\Memoize\Contracts\DriverInterface;
use StellarWP\Memoize\Contracts\MemoizerInterface;
use StellarWP\Memoize\Drivers\MemoryDriver;

/**
 * Container ServiceProvider to tell the DI Container how to build everything when another
 * instance is requested from the Container that uses our interface.
 *
 * @example $this->container->get( MyProjectClass::class );
 */
final class ServiceProvider
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        $this->container->singleton( DriverInterface::class, MemoryDriver::class );
        $this->container->bind( MemoizerInterface::class, Memoizer::class );
    }
}
```

## Drivers

Memoize comes with a single driver out of the box, but you can also create your own using the `StellarWP\Memoize\Contracts\DriverInterface`.

### MemoryDriver

The `MemoryDriver` is a simple in-memory driver. Basically, contains an array property in the driver that holds the memoized values. You can manually specify the use of this driver or any other driver like so:

```php
use StellarWP\Memoize\Memoizer;
use StellarWP\Memoize\Drivers\MemoryDriver;

$memoizer = new Memoizer(new MemoryDriver());
```
## Decorators

Memoize comes with a `PrefixedDriverDecorator` which automatically adds a prefix to your all your keys.

> 💡 The default prefix for the decorator is `stellarwp`, if not specifically configured.

```php
use StellarWP\Memoize\Memoizer;
use StellarWP\Memoize\Drivers\MemoryDriver;
use StellarWP\Memoize\Decorators\PrefixedDriverDecorator;
use lucatume\DI52\Container;

$decorator = new PrefixedDriverDecorator( new MemoryDriver(), 'myplugin' );
$memoizer  = new Memoizer( $decorator );

// Or, with a Container like DI52.
$this->container->when( PrefixedDriverDecorator::class )
                ->needs( '$prefix')
                ->give( static fn(): string => 'mypluginslug' );

$this->container->singletonDecorators( DriverInterface::class, [
    MemoryDriver::class,
    PrefixedDriverDecorator::class
] );

$this->container->bind( MemoizerInterface::class, Memoizer::class );

$memoizer = $this->container->get( MemoizerInterface::class );

// Under the hood, this is automatically stored as mypluginslug.foo.
$memoizer->set( 'foo', 'bar' );

// Under the hood, this is automatically stored as mypluginslug.my.custom.key.
$memoizer->set( 'my.custom.key', 'value' );
$value = $memoizer->get( 'my.custom.key' );
```
