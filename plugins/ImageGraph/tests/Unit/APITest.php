<?php

namespace Piwik\Plugins\ImageGraph\tests\Unit;

use Piwik\Plugins\ImageGraph\API;
use ReflectionMethod;

/**
 * @group ImageGraph
 * @group APITest
 */
class APITest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getOrdinateValuesToParse
     */
    public function testParseOrdinateValueReturnsFloat($value, float $expected): void
    {
        $method = new ReflectionMethod(API::class, 'parseOrdinateValue');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $actual = $method->invoke(null, $value);

        $this->assertIsFloat($actual);
        $this->assertSame($expected, $actual);
    }

    public function getOrdinateValuesToParse(): array
    {
        return [
            [2.05, 2.05],
            ['2.05', 2.05],
            [205, 205.0],
            ['01:02:03.50', 3723.5],
            ['', 0.0],
        ];
    }
}
