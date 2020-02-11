<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\WhitelistedValue;

/**
 * @group Validator
 * @group WhiteListedValue
 * @group WhiteListedValueTest
 */
class WhiteListedValueTest extends \PHPUnit\Framework\TestCase
{
    public function test_validate_successValueNotEmpty()
    {
        $this->validate('foo');
        $this->validate('bar');
        $this->validate('baz');
        $this->validate('lorem');
    }

    /**
     * @dataProvider getInvalidValues
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorXNotWhitelisted
     */
    public function test_validate_failInvalidFormat($date)
    {
        $this->validate($date);
    }

    public function getInvalidValues()
    {
        return array(
            array('BaR'), // value has to match exactly, not case insensitive
            array('invalidvalue'),
            array(''),
            array(false),
            array(null),
        );
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage The whitelisted values need to be an array
     */
    public function test_construct_throwsExceptionIfParamIsNotAnArray()
    {
        new WhitelistedValue('foobar');
    }

    private function validate($value)
    {
        $validator = new WhitelistedValue(array('foo', 'bar', 'baz', 'lorem'));
        $validator->validate($value);
    }

}
