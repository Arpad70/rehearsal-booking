<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @method void assertEquals($expected, $actual, string $message = '')
 * @method void assertNotEquals($expected, $actual, string $message = '')
 * @method void assertSame($expected, $actual, string $message = '')
 * @method void assertNotSame($expected, $actual, string $message = '')
 * @method void assertTrue($condition, string $message = '')
 * @method void assertFalse($condition, string $message = '')
 * @method void assertNull($actual, string $message = '')
 * @method void assertNotNull($actual, string $message = '')
 * @method void assertEmpty($actual, string $message = '')
 * @method void assertNotEmpty($actual, string $message = '')
 * @method void assertCount(int $expectedCount, $haystack, string $message = '')
 * @method void assertContains($needle, $haystack, string $message = '')
 * @method void assertNotContains($needle, $haystack, string $message = '')
 * @method void assertStringContainsString(string $needle, string $haystack, string $message = '')
 * @method void assertStringNotContainsString(string $needle, string $haystack, string $message = '')
 * @method void assertGreaterThan($expected, $actual, string $message = '')
 * @method void assertLessThan($expected, $actual, string $message = '')
 * @method void assertIsArray($actual, string $message = '')
 * @method void assertIsString($actual, string $message = '')
 * @method void assertIsInt($actual, string $message = '')
 * @method void assertIsBool($actual, string $message = '')
 */
abstract class TestCase extends BaseTestCase
{
}

