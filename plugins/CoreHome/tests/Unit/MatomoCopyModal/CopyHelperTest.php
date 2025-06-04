<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Unit\MatomoCopyModal;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreHome\MatomoCopyModal\CopyHelper;
use Piwik\Plugins\CoreHome\MatomoCopyModal\CopyRequest;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group MatomoCopyModal
 */
class CopyHelperTest extends TestCase
{
    /**
     * @dataProvider getIncrementNameWithNumericalSuffix
     * @param string $name
     * @param string $expected
     * @return void
     */
    public function testIncrementNameWithNumericalSuffix(string $name, string $expected, string $maxLength, bool $expectException)
    {
        if ($expectException) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('The maximum name length cannot be less than the length of the suffix.');
        }

        if ($maxLength === '') {
            $this->assertSame($expected, CopyHelper::incrementNameWithNumericalSuffix($name));
            return;
        }
        $maxLength = intval($maxLength);

        $this->assertSame($expected, CopyHelper::incrementNameWithNumericalSuffix($name, $maxLength), "Name '$name' should be equal to '$expected' when max length is set to '$maxLength'.");
    }

    public function getIncrementNameWithNumericalSuffix(): array
    {
        return [
            ['Foo', 'Foo (1)', '', false],
            ['Foo (1)', 'Foo (2)', '', false],
            ['Foo (2)', 'Foo (3)', '', false],
            ['Foo (3)', 'Foo (4)', '', false],
            ['SomeOtherName', 'SomeOtherName (1)', '', false],
            ['SomeOtherName (1)', 'SomeOtherName (2)', '', false],
            ['SomeOtherName', '', '-2', true],
            ['SomeOtherName', 'SomeOtherName (1)', '-1', false],
            ['SomeOtherName', '', '0', true],
            ['SomeOtherName', '', '1', true],
            ['SomeOtherName', '', '2', true],
            ['SomeOtherName', '', '3', true],
            ['SomeOtherName', '', '4', true],
            ['SomeOtherName', 'S (1)', '5', false],
            ['SomeOtherName', 'SomeOt (1)', '10', false],
            ['SomeOtherName (1)', '', '-2', true],
            ['SomeOtherName (1)', 'SomeOtherName (2)', '-1', false],
            ['SomeOtherName (1)', '', '0', true],
            ['SomeOtherName (1)', '', '1', true],
            ['SomeOtherName (1)', '', '2', true],
            ['SomeOtherName (1)', '', '3', true],
            ['SomeOtherName (1)', '', '4', true],
            ['SomeOtherName (1)', 'S (2)', '5', false],
            ['SomeOtherName (1)', 'SomeOt (2)', '10', false],
            ['SomeOtherName (9)', '', '-2', true],
            ['SomeOtherName (9)', 'SomeOtherName (10)', '-1', false],
            ['SomeOtherName (9)', '', '0', true],
            ['SomeOtherName (9)', '', '1', true],
            ['SomeOtherName (9)', '', '2', true],
            ['SomeOtherName (9)', '', '3', true],
            ['SomeOtherName (9)', '', '4', true],
            ['SomeOtherName (9)', '', '5', true],
            ['SomeOtherName (9)', 'SomeO (10)', '10', false],
        ];
    }
}
