<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Unit\EntityDuplicator;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreHome\EntityDuplicator\EntityDuplicatorHelper;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group EntityDuplicator
 */
class EntityDuplicatorHelperTest extends TestCase
{
    /**
     * @dataProvider getGetIncrementNameWithNumericalSuffixData
     * @param string $name
     * @param string $expected
     * @param string $maxLength String to allow empty string to indicate using the default max length
     * @param bool $expectException
     * @return void
     */
    public function testIncrementNameWithNumericalSuffix(string $name, string $expected, string $maxLength, bool $expectException)
    {
        if ($expectException) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('The maximum name length cannot be less than the length of the suffix.');
        }

        if ($maxLength === '') {
            $this->assertSame($expected, EntityDuplicatorHelper::incrementNameWithNumericalSuffix($name), "Name '$name' should be equal to '$expected' when not providing max length.");
            return;
        }
        $maxLength = intval($maxLength);

        $this->assertSame($expected, EntityDuplicatorHelper::incrementNameWithNumericalSuffix($name, $maxLength), "Name '$name' should be equal to '$expected' when max length is set to '$maxLength'.");
    }

    public function getGetIncrementNameWithNumericalSuffixData(): array
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
            ['Some1Name', 'Some1Name (1)', '', false],
            ['Some1Name (1)', 'Some1Name (2)', '', false],
            ['NameContainingMultiple12Digits', 'NameContainingMultiple12Digits (1)', '', false],
            ['NameContainingMultiple12Digits (1)', 'NameContainingMultiple12Digits (2)', '', false],
            ['AnotherNameContainingMultiple12Digits34', 'AnotherNameContainingMultiple12Digits34 (1)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (1)', 'AnotherNameContainingMultiple12Digits34 (2)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (2)', 'AnotherNameContainingMultiple12Digits34 (3)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (3)', 'AnotherNameContainingMultiple12Digits34 (4)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (4)', 'AnotherNameContainingMultiple12Digits34 (5)', '', false],
        ];
    }

    /**
     * @dataProvider getGetUniqueNameComparedToListData
     * @param string $name
     * @param array $names
     * @param string $expected
     * @param string $maxLength String to allow empty string to indicate using the default max length
     * @param bool $expectException
     * @return void
     */
    public function testGetUniqueNameComparedToList(string $name, array $names, string $expected, string $maxLength, bool $expectException)
    {
        if ($expectException) {
            $this->expectException(\Exception::class);
            if ($maxLength !== '-1' && intval($maxLength) < 1) {
                $this->expectExceptionMessage('The maximum name length cannot be less than 1 character.');
            } else {
                $this->expectExceptionMessage('The maximum name length cannot be less than the length of the suffix.');
            }
        }

        if ($maxLength === '') {
            $this->assertSame($expected, EntityDuplicatorHelper::getUniqueNameComparedToList($name, $names), "Name '$name' should be equal to '$expected' when not providing max length for existing names:" . implode(', ', $names));
            return;
        }
        $maxLength = intval($maxLength);

        $this->assertSame($expected, EntityDuplicatorHelper::getUniqueNameComparedToList($name, $names, $maxLength), "Name '$name' should be equal to '$expected' when max length is set to '$maxLength' for existing names:" . implode(', ', $names));
    }

    public function getGetUniqueNameComparedToListData(): array
    {
        return [
            ['Foo', [], 'Foo', '', false],
            ['Foo', ['Foo (2)'], 'Foo', '', false],
            ['Foo', ['Foo (1)'], 'Foo', '', false],
            ['Foo', ['Foo (1)', 'Foo (2)'], 'Foo', '', false],
            ['Foo', ['Foo (1)', 'Foo (2)', 'Foo (3)'], 'Foo', '', false],
            ['Foo', ['Foo (1)', 'Foo (2)', 'Foo (3)'], 'Foo', '', false],
            ['Foo (1)', [], 'Foo (1)', '', false],
            ['Foo (1)', ['Foo (2)'], 'Foo (1)', '', false],
            ['Foo (1)', ['Foo (1)'], 'Foo (2)', '', false],
            ['Foo (1)', ['Foo (1)', 'Foo (2)'], 'Foo (3)', '', false],
            ['Foo (1)', ['Foo (1)', 'Foo (2)', 'Foo (3)'], 'Foo (4)', '', false],
            ['Foo (2)', [], 'Foo (2)', '', false],
            ['Foo (2)', ['Foo (1)'], 'Foo (2)', '', false],
            ['Foo (2)', ['Foo (3)'], 'Foo (2)', '', false],
            ['Foo (2)', ['Foo (1)', 'Foo (2)'], 'Foo (3)', '', false],
            ['Foo (2)', ['Foo (1)', 'Foo (2)', 'Foo (3)'], 'Foo (4)', '', false],
            ['Foo (3)', [], 'Foo (3)', '', false],
            ['Foo (3)', ['Foo (1)'], 'Foo (3)', '', false],
            ['Foo (3)', ['Foo (2)'], 'Foo (3)', '', false],
            ['Foo (3)', ['Foo (3)'], 'Foo (4)', '', false],
            ['Foo (3)', ['Foo (1)', 'Foo (2)'], 'Foo (3)', '', false],
            ['Foo (3)', ['Foo (1)', 'Foo (2)', 'Foo (3)'], 'Foo (4)', '', false],
            ['SomeOtherName', [], 'SomeOtherName', '', false],
            ['SomeOtherName', ['SomeOtherName'], 'SomeOtherName (1)', '', false],
            ['SomeOtherName (1)', [], 'SomeOtherName (1)', '', false],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], 'SomeOtherName (2)', '', false],
            ['SomeOtherName', [], '', '-2', true],
            ['SomeOtherName', ['SomeOtherName'], '', '-2', true],
            ['SomeOtherName', [], 'SomeOtherName', '-1', false],
            ['SomeOtherName', ['SomeOtherName'], 'SomeOtherName (1)', '-1', false],
            ['SomeOtherName', [], 'SomeOtherName', '-1', false],
            ['SomeOtherName', ['SomeOtherName'], 'SomeOtherName (1)', '-1', false],
            ['SomeOtherName', [], '', '0', true],
            ['SomeOtherName', ['SomeOtherName'], '', '0', true],
            ['SomeOtherName', [], 'S', '1', false],
            ['SomeOtherName', ['SomeOtherName'], 'S', '1', false],
            ['SomeOtherName', ['S'], '', '1', true],
            ['SomeOtherName', [], 'So', '2', false],
            ['SomeOtherName', ['SomeOtherName'], 'So', '2', false],
            ['SomeOtherName', ['So'], '', '2', true],
            ['SomeOtherName', [], 'Som', '3', false],
            ['SomeOtherName', ['SomeOtherName'], 'Som', '3', false],
            ['SomeOtherName', ['Som'], '', '3', true],
            ['SomeOtherName', [], 'Some', '4', false],
            ['SomeOtherName', ['SomeOtherName'], 'Some', '4', false],
            ['SomeOtherName', ['Some'], '', '4', true],
            ['SomeOtherName', [], 'SomeO', '5', false],
            ['SomeOtherName', ['SomeOtherName'], 'SomeO', '5', false],
            ['SomeOtherName', ['SomeO'], 'S (1)', '5', false],
            ['SomeOtherName', [], 'SomeOtherN', '10', false],
            ['SomeOtherName', ['SomeOtherName'], 'SomeOtherN', '10', false],
            ['SomeOtherName', ['SomeOtherN'], 'SomeOt (1)', '10', false],
            ['SomeOtherName (1)', [], '', '-2', true],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], '', '-2', true],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], 'SomeOtherName (2)', '-1', false],
            ['SomeOtherName (1)', [], '', '0', true],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], '', '0', true],
            ['SomeOtherName (1)', [], 'S', '1', false],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], 'S', '1', false],
            ['SomeOtherName (1)', ['S'], '', '1', true],
            ['SomeOtherName (1)', [], 'So', '2', false],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], 'So', '2', false],
            ['SomeOtherName (1)', ['So'], '', '2', true],
            ['SomeOtherName (1)', [], 'Som', '3', false],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], 'Som', '3', false],
            ['SomeOtherName (1)', ['Som'], '', '3', true],
            ['SomeOtherName (1)', [], 'Some', '4', false],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], 'Some', '4', false],
            ['SomeOtherName (1)', ['Some'], '', '4', true],
            ['SomeOtherName (1)', [], 'SomeO', '5', false],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], 'SomeO', '5', false],
            ['SomeOtherName (1)', ['SomeO'], 'S (1)', '5', false],
            ['S (1)', ['S (1)'], 'S (2)', '5', false],
            ['SomeOtherName (1)', [], 'SomeOtherN', '10', false],
            ['SomeOtherName (1)', ['SomeOtherName (1)'], 'SomeOtherN', '10', false],
            ['SomeOtherName (1)', ['SomeOtherN'], 'SomeOt (1)', '10', false],
            ['SomeOt (1)', ['SomeOt (1)'], 'SomeOt (2)', '10', false],
            ['SomeOtherName (9)', [], '', '-2', true],
            ['SomeOtherName (9)', [], 'SomeOtherName (9)', '-1', false],
            ['SomeOtherName (9)', ['SomeOtherName (9)'], 'SomeOtherName (10)', '-1', false],
            ['SomeOtherName (9)', [], '', '0', true],
            ['SomeOtherName (9)', [], 'S', '1', false],
            ['SomeOtherName (9)', ['SomeOtherName (9)'], 'S', '1', false],
            ['SomeOtherName (9)', ['S'], '', '1', true],
            ['SomeOtherName (9)', [], 'So', '2', false],
            ['SomeOtherName (9)', ['SomeOtherName (9)'], 'So', '2', false],
            ['SomeOtherName (9)', ['So'], '', '2', true],
            ['SomeOtherName (9)', [], 'Som', '3', false],
            ['SomeOtherName (9)', ['SomeOtherName (9)'], 'Som', '3', false],
            ['SomeOtherName (9)', ['Som'], '', '3', true],
            ['SomeOtherName (9)', [], 'Some', '4', false],
            ['SomeOtherName (9)', ['SomeOtherName (9)'], 'Some', '4', false],
            ['SomeOtherName (9)', ['Some'], '', '4', true],
            ['SomeOtherName (9)', [], 'SomeO', '5', false],
            ['SomeOtherName (9)', ['SomeOtherName (9)'], 'SomeO', '5', false],
            ['SomeOtherName (9)', ['SomeO'], 'S (1)', '5', false],
            ['S (9)', ['S (9)'], 'S (1)', '5', true],
            ['SomeOtherName (9)', [], 'SomeOtherN', '10', false],
            ['SomeOtherName (9)', ['SomeOtherN'], 'SomeOt (1)', '10', false],
            ['SomeOt (9)', ['SomeOt (9)'], 'SomeO (10)', '10', false],
            ['Some1Name', [], 'Some1Name', '', false],
            ['Some1Name', ['Some1Name'], 'Some1Name (1)', '', false],
            ['Some1Name (1)', [], 'Some1Name (1)', '', false],
            ['Some1Name (1)', ['Some1Name (1)'], 'Some1Name (2)', '', false],
            ['NameContainingMultiple12Digits', [], 'NameContainingMultiple12Digits', '', false],
            ['NameContainingMultiple12Digits', ['NameContainingMultiple12Digits'], 'NameContainingMultiple12Digits (1)', '', false],
            ['NameContainingMultiple12Digits (1)', [], 'NameContainingMultiple12Digits (1)', '', false],
            ['NameContainingMultiple12Digits (1)', ['NameContainingMultiple12Digits (1)'], 'NameContainingMultiple12Digits (2)', '', false],
            ['AnotherNameContainingMultiple12Digits34', [], 'AnotherNameContainingMultiple12Digits34', '', false],
            ['AnotherNameContainingMultiple12Digits34', ['AnotherNameContainingMultiple12Digits34'], 'AnotherNameContainingMultiple12Digits34 (1)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (1)', [], 'AnotherNameContainingMultiple12Digits34 (1)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (1)', ['AnotherNameContainingMultiple12Digits34 (1)'], 'AnotherNameContainingMultiple12Digits34 (2)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (2)', [], 'AnotherNameContainingMultiple12Digits34 (2)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (2)', ['AnotherNameContainingMultiple12Digits34 (2)'], 'AnotherNameContainingMultiple12Digits34 (3)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (3)', [], 'AnotherNameContainingMultiple12Digits34 (3)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (3)', ['AnotherNameContainingMultiple12Digits34 (3)'], 'AnotherNameContainingMultiple12Digits34 (4)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (4)', [], 'AnotherNameContainingMultiple12Digits34 (4)', '', false],
            ['AnotherNameContainingMultiple12Digits34 (4)', ['AnotherNameContainingMultiple12Digits34 (4)'], 'AnotherNameContainingMultiple12Digits34 (5)', '', false],
        ];
    }
}
