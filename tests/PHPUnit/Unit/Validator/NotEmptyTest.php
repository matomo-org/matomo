<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        self::expectNotToPerformAssertions();

        $this->validate('5');
        $this->validate(true);
        $this->validate(99);
    }

    /**
     * @dataProvider getFailValues
     */
    public function test_validate_failValueIsEmpty($value)
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorEmptyValue');

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
