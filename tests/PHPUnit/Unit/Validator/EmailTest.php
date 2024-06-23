<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Validators\Email;

/**
 * @group Validator
 * @group Email
 * @group EmailTest
 */
class EmailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getValidEmails
     */
    public function testValidateSuccessValueIsLikeUri($validEmail)
    {
        self::expectNotToPerformAssertions();

        $this->validate($validEmail);
    }

    public function getValidEmails()
    {
        return [
            array('test@example.com'),
            array('1234567890@example.com'),
            array('test+test@example.com'),
        ];
    }

    /**
     * @dataProvider getFailedEmails
     */
    public function testValidateFailValueIsNotValidEmail($email)
    {
        $this->expectException(\Piwik\Validators\Exception::class);
        $this->expectExceptionMessage('ValidatorErrorNotEmailLike');

        $this->validate($email);
    }

    public function getFailedEmails()
    {
        return [
            array('-tld-cannot-start-from-hypen'),
            array('test@example.com,test2@example.com'),
            array('ąęśćżźł-there-is-no-such-idn'),
            array('xn--fd67as67fdsa'),
            array('!@#-inavlid-chars-in-tld'),
            array('no spaces in tld allowed'),
            array('no--double--hypens--allowed'),
        ];
    }

    private function validate($value)
    {
        $validator = new Email();
        $validator->validate($value);
    }
}
