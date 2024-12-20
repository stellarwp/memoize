<?php

namespace StellarWP\Memoize\Tests\Helper;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * @mixin \Codeception\Test\Unit
 * @mixin \PHPUnit\Framework\TestCase
 * @mixin \Codeception\PHPUnit\TestCase
 */
class MemoizeTestCase extends WPTestCase {
    protected $backupGlobals = false;
}

