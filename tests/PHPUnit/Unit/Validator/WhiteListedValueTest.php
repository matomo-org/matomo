<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Validator;

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
        self::expectNotToPerformAssertions();

        $this->validate('foo');
        $this->validate('bar');
        $this->validate('baz');
        $this->validate('lorem');
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function test_validate_failInvalidFormat($date)
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorXNotWhitelisted');
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

    public function test_construct_throwsExceptionIfParamIsNotAnArray()
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('The whitelisted values need to be an array');
        new WhitelistedValue('foobar');
    }

    private function validate($value)
    {
        $validator = new WhitelistedValue(array('foo', 'bar', 'baz', 'lorem'));
        $validator->validate($value);
    }
}
