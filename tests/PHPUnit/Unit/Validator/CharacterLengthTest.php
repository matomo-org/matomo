<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\CharacterLength;

/**
 * @group Validator
 * @group CharacterLength
 * @group CharacterLengthTest
 */
class CharacterLengthTest extends \PHPUnit\Framework\TestCase
{
    public function testValidateSuccessValueNotEmpty()
    {
        self::expectNotToPerformAssertions();

        $this->validate('mytest', '2', '10');
        $this->validate('mytest', 4, '6');
        $this->validate('mytest', 6, 6);
        $this->validate('', 0, 6);
        $this->validate('testwewe', 0, 10);
    }

    public function testValidateFailValueIsTooShort()
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorCharacterTooShort');
        $this->validate('myte', 5);
    }

    public function testValidateFailValueIsTooLong()
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorCharacterTooLong');
        $this->validate('mytestfoo', null, 4);
    }

    public function testValidateFailValueIsTooNotInRange()
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorCharacterTooLong');
        $this->validate('mytestfoobar', 5, 8);
    }

    private function validate($value, $min = null, $max = null)
    {
        $validator = new CharacterLength($min, $max);
        $validator->validate($value);
    }
}
