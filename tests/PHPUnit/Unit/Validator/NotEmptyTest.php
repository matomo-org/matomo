<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\NotEmpty;

/**
 * @group Validator
 * @group NotEmpty
 * @group NotEmptyTest
 */
class NotEmptyTest extends \PHPUnit\Framework\TestCase
{
    public function test_validate_successValueNotEmpty()
    {
        $this->validate('5');
        $this->validate(true);
        $this->validate(99);
        $this->assertTrue(true);
    }

    /**
     * @dataProvider getFailValues
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage General_ValidatorErrorEmptyValue
     */
    public function test_validate_failValueIsEmpty($value)
    {
        $this->validate($value);
    }

    public function getFailValues()
    {
        return array(
            array(false), array(''), array(0)
        );
    }

    private function validate($value)
    {
        $validator = new NotEmpty();
        $validator->validate($value);
    }
}
