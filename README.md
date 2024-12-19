# Stellar Memoize

[![Tests](https://github.com/stellarwp/memoize/workflows/Tests/badge.svg)](https://github.com/stellarwp/memoize/actions?query=branch%3Amain) [![PHPCS](https://github.com/stellarwp/memoize/actions/workflows/phpcs.yml/badge.svg)](https://github.com/stellarwp/memoize/actions/workflows/phpcs.yml) [![PHPStan](https://github.com/stellarwp/memoize/actions/workflows/phpstan.yml/badge.svg)](https://github.com/stellarwp/memoize/actions/workflows/phpstan.yml) [![Compatibility](https://github.com/stellarwp/memoize/actions/workflows/compatibility.yml/badge.svg)](https://github.com/stellarwp/memoize/actions/workflows/compatibility.yml)

A flexible memoization library for memory caching.

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
use StellarWP\Memoize\Memoize;

Memoize::set('foo', 'bar');

if (Memoize::has('foo')) {
    echo Memoize::get('foo'); // Outputs: bar
}

// Unsets foo from the memoization cache.
Memoize::forget('foo');
```

### Setting a nested structure

Memoize allows you to use dot notation to set, get, and forget values from a nested structure. This allows you to easily add/fetch values and then purge individual items or whole collections of items.

```php
use StellarWP\Memoize\Memoize;

Memoize::set('foo.bar.bork', 'baz');

// This results in the following cache:
// [
//     'foo' => [
//         'bar' => [
//             'bork' => 'baz',
//         ],
//     ],
// ]

// You can fetch the value like so:
$value = Memoize::get('foo.bar.bork');
echo $value; // Outputs: baz

// You can fetch anywhere up the chain:
$value = Memoize::get('foo.bar');
echo $value; // Outputs: [ 'bork' => 'baz' ]

$value = Memoize::get('foo');
echo $value; // Outputs: [ 'bar' => [ 'bork' => 'baz' ] ]

$value = Memoize::get();
echo $value; // Outputs: [ 'foo' => [ 'bar' => [ 'bork' => 'baz' ] ] ]
```

#### Purging a nested structure

```php
use StellarWP\Memoize\Memoize;

Memoize::set('foo.bar.bork', 'baz');
Memoize::forget('foo.bar.bork');

// This results in the following cache:
// [
//     'foo' => [
//         'bar' => [],
//     ],
// ]

Memoize::forget('foo.bar');

// This results in the following cache:
// [
//     'foo' => [],
// ]

Memoize::forget('foo');

// This results in the following cache:
// []

Memoize::forget();

// This results in the following cache:
// []
```

### Caching an expensive execution

```php
use StellarWP\Memoize\Memoize;

function my_expensive_function() {
    $key   = __FUNCTION__;
    $value = Memoize::get($key);

    if ( ! $value ) {
        // Do some crazy expensive stuff to set:

        $value = $thing;

        Memoize::set($key, $value);
    }

    return $value;
}
```

### Caching with closures

Memoize also supports caching closures as values that get resolved when retrieved from the cache.

#### Simple example
```php
use StellarWP\Memoize\Memoize;

Memoize::set('foo', static function () {
    return 'bar';
});

echo Memoize::get('foo'); // Outputs: bar
```

#### Nested example

If you get a key that contains multiple closures in its tree, it will traverse the tree and resolve all of those closures.

```php
use StellarWP\Memoize\Memoize;

Memoize::set('stellarwp.bork', static function () {
    return 'lol';
});

Memoize::set('stellarwp.foo.bar', static function () {
    return 'baz';
});

print_r( Memoize::get('stellarwp') );
// Outputs:
// [
//     'bork' => 'lol',
//     'foo' => [
//         'bar' => 'baz',
//     ],
// ]
```

## Drivers

Memoize comes with a few drivers out of the box, but you can also create your own.

### MemoryDriver

The `MemoryDriver` is a simple in-memory driver is in-memory memoization. Basically, there's a static variable in the driver that holds the memoized values. You can manually specify the use of this driver like so:

```php
use StellarWP\Memoize\Config;
use StellarWP\Memoize\Drivers\MemoryDriver;

Config::setDriver(new MemoryDriver());
```

### WpCacheDriver

The `WpCacheDriver` is the default driver and uses WordPress's `wp_cache_set` and `wp_cache_get` functions and stores all of the memoized values in a single cache entry. Getting and setting memoized values is done by fetching from cache, manipulating (if needed), and saving back to cache (if needed).

```php
use StellarWP\Memoize\Config;
use StellarWP\Memoize\Drivers\WpCacheDriver;

Config::setDriver(new WpCacheDriver());
```
