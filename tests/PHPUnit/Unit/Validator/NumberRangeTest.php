<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\NumberRange;

/**
 * @group Validator
 * @group NumberRange
 * @group NumberRangeTest
 */
class NumberRangeTest extends \PHPUnit\Framework\TestCase
{
    public function test_validate_successValueNotEmpty()
    {
        $this->validate('5', '4', '5');
        $this->validate('5', '5', '5');
        $this->validate('5', '5', '7');
        $this->validate('5', '4', '6');
        $this->validate(5, 4, '6');
        $this->validate(5.43, 5.30, 5.50);
        $this->validate('5');
        $this->validate('5', 4);
        $this->validate('5', null, '6');
        $this->validate('-5', -10, '-4');
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorNumberTooLow
     */
    public function test_validate_failValueIsTooLow()
    {
        $this->validate(3, 5);
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorNumberTooHigh
     */
    public function test_validate_failValueIsTooHigh()
    {
        $this->validate(10, null, 8);
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorNumberTooHigh
     */
    public function test_validate_failValueIsTooNotInRange()
    {
        $this->validate(10, 5, 8);
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorNumberTooLow
     */
    public function test_validate_failValueIsTooNotInRangeFloat()
    {
        $this->validate(5.43, 5.44, 8);
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorNotANumber
     */
    public function test_validate_failValueIsNotNumber()
    {
        $this->validate('foo');
    }

    private function validate($value, $min = null, $max = null)
    {
        $validator = new NumberRange($min, $max);
        $validator->validate($value);
    }
}
