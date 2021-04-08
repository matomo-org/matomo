<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use PHPUnit\Framework\TestCase;
use Piwik\Validators\AlphaNumSpaces;

/**
 * @group Validator
 * @group AlphaNumSpaces
 * @group AlphaNumSpacesTest
 */
class AlphaNumSpacesTest extends TestCase
{
    public function test_validate_success_english_strings()
    {
        $this->validate('onlyalpha');
        $this->validate(123);
        $this->validate('abc 123');
        $this->validate('ABC 123 and 0');

        $this->assertTrue(true);
    }

    public function test_validate_success_special_chars()
    {
        $this->validate('Äpfel äpfel und stühle');
        $this->validate('워드  信件 λέξη từ ngữ');
        $this->validate('áâäæãåāëíûî');

        $this->assertTrue(true);
    }

    /**
     * @dataProvider wrongStrings
     */
    public function test_validate_fail_with_wrong_strings($string)
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorNoValidAlphaNumSpaces');

        $this->validate($string);
    }

    public function wrongStrings()
    {
        return [
            ['!'],
            ['abc!'],
            ['12-12'],
            ['something / &'],
            ['not_ok']
        ];
    }

    private function validate($value)
    {
        $validator = new AlphaNumSpaces();
        $validator->validate($value);
    }
}
