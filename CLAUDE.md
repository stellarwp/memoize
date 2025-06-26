# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP memoization library for WordPress/PHP projects that provides in-memory caching capabilities. It's designed as a reusable library (not a traditional WordPress plugin) that can be integrated into other projects via Composer.

## Development Commands

### Code Quality
- `composer lint` - Check code against PSR-12 standards
- `composer format` - Auto-fix code style issues
- `composer test:analysis` - Run PHPStan static analysis (level 5)
- `composer compatibility` - Check PHP compatibility (7.4, 8.0, 8.1, 8.2)

### Testing
Tests use Codeception with wp-browser and require SLIC (StellarWP's Local Interactive Containers):
- `slic run unit` - Run unit tests (requires SLIC environment)

## Architecture

The library follows an interface-based driver architecture:

```
src/Memoize/
├── Contracts/          # Core interfaces
│   ├── DriverInterface.php    # Storage driver contract
│   └── MemoizerInterface.php  # Main memoizer contract
├── Drivers/
│   └── MemoryDriver.php       # In-memory storage implementation
├── Memoizer.php               # Main implementation
└── Traits/
    └── MemoizeTrait.php       # Trait for easy integration
```

Key architectural decisions:
- **Driver Pattern**: Storage backends are abstracted behind `DriverInterface`, allowing for future implementations (Redis, database, etc.)
- **Dot Notation**: Supports nested data access (e.g., `user.profile.name`)
- **Closure Support**: Values can be closures that are resolved on first access
- **Integration Options**: Can be used via dependency injection (`Memoizer` class) or trait (`MemoizeTrait`)

## Testing Approach

- Unit tests cover core functionality (get, set, has, forget operations)
- Tests validate nested structures, dot notation, and closure resolution
- Both `Memoizer` class and `MemoizeTrait` are tested
- CI runs tests automatically via GitHub Actions

## Code Standards

- **PHP Version**: 7.4+ required
- **Coding Standard**: PSR-12
- **Static Analysis**: PHPStan level 5
- **Namespace**: `StellarWP\Memoize`

Always run `composer lint` before committing. If there are style issues, use `composer format` to fix them automatically.