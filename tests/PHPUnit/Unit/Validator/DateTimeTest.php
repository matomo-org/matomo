<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\DateTime;

/**
 * @group Validator
 * @group DateTime
 * @group DateTimeTest
 */
class DateTimeTest extends \PHPUnit\Framework\TestCase
{
    public function testValidateSuccessValueNotEmpty()
    {
        self::expectNotToPerformAssertions();

        $this->validate('2014-05-06 10:13:14');
        $this->validate('2014-05-06T10:13:14');
        $this->validate('2014-05-06 10:13:14Z');
        $this->validate('2014-05-06T10:13:14Z');
    }

    public function testValidateSuccessValueMayBeEmpty()
    {
        self::expectNotToPerformAssertions();

        $this->validate(false);
        $this->validate('');
    }

    /**
     * @dataProvider getWrongFormat
     */
    public function testValidateFailInvalidFormat($date)
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorInvalidDateTimeFormat');
        $this->validate($date);
    }

    public function getWrongFormat()
    {
        return array(
            array('2014-05-0610:13:14'),
            array('2014-05-06 10:13:14 '),
            array('2014/05/06 10:13:14'),
            array('10:13:14'),
            array('2014/05/06'),
            array('10:13:14 2014-05-06'),
            array('1577873410'),
            array('foo'),
            array('0'),
        );
    }

    public function testValidateInvalidDate()
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('General_ExceptionInvalidDateFormat');
        $this->validate('2014-15-26 90:43:32');
    }

    private function validate($value)
    {
        $validator = new DateTime();
        $validator->validate($value);
    }
}
