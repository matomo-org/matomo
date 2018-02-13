<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\CharacterLength;

/**
 * @group Validator
 * @group NumberRange
 * @group NumberRangeTest
 */
class CharacterLengthTest extends \PHPUnit_Framework_TestCase
{
    public function test_validate_successValueNotEmpty()
    {
        $this->validate('mytest', '2', '10');
        $this->validate('mytest', 4, '6');
        $this->validate('mytest', 6, 6);
        $this->validate('', 0, 6);
        $this->validate('testwewe', 0, 10);
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorCharacterTooShort
     */
    public function test_validate_failValueIsTooShort()
    {
        $this->validate('myte', 5);
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorCharacterTooLong
     */
    public function test_validate_failValueIsTooLong()
    {
        $this->validate('mytestfoo', null, 4);
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorCharacterTooLong
     */
    public function test_validate_failValueIsTooNotInRange()
    {
        $this->validate('mytestfoobar', 5, 8);
    }

    public function test_getHtmlAttributes_noMinNoMax()
    {
        $expected = array();
        $this->assertEquals($expected, $this->getHtmlAttributes());
    }

    public function test_getHtmlAttributes_NoMax()
    {
        $expected = array('maxlength' => 10);
        $this->assertEquals($expected, $this->getHtmlAttributes(null, 10));
    }

    public function test_getHtmlAttributes_NoMin()
    {
        $expected = array('pattern' => '.{15,}');
        $this->assertEquals($expected, $this->getHtmlAttributes(15));
    }

    public function test_getHtmlAttributes_WithMinAndMax()
    {
        $expected = array('pattern' => '.{15,20}');
        $this->assertEquals($expected, $this->getHtmlAttributes(15, 20));
    }

    private function validate($value, $min = null, $max = null)
    {
        $validator = new CharacterLength($min, $max);
        $validator->validate($value);
    }

    private function getHtmlAttributes($min = null, $max = null)
    {
        $validator = new CharacterLength($min, $max);
        return $validator->getHtmlAttributes();
    }
}
