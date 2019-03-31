<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\tests\Unit\Validators;

use Piwik\Plugins\UsersManager\Validators\PasswordValidator;

/**
 * @group UsersManager
 * @group UsersManagerTest
 * @group PasswordValidator
 * @group Plugins
 */
class PasswordValidatorTest extends \PHPUnit_Framework_TestCase
{

    /** @var PasswordValidator */
    private $passwordValidator;

    public function setUp()
    {
        $this->passwordValidator = new PasswordValidator(15, true, true, true, true);
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage UsersManager_ExceptionInvalidPassword
     */
    public function test_validate_notLongEnough()
    {
        $this->passwordValidator->validate('test');
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage UsersManager_ExceptionInvalidPasswordUppercaseLetterRequired
     */
    public function test_validate_notOneUppercaseLetter()
    {
        $this->passwordValidator->validate('sometestpassword');
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage UsersManager_ExceptionInvalidPasswordLowercaseLetterRequired
     */
    public function test_validate_notOneLowercaseLetter()
    {
        $this->passwordValidator->validate('SOMETESTPASSWORD');
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage UsersManager_ExceptionInvalidPasswordNumberRequired
     */
    public function test_validate_notOneNumberLetter()
    {
        $this->passwordValidator->validate('someTestPassword');
    }

    /**
     * @expectedException \Piwik\Validators\Exception
     * @expectedExceptionMessage UsersManager_ExceptionInvalidPasswordSpecialCharacterRequired
     */
    public function test_validate_notOneSpecialCharacter()
    {
        $this->passwordValidator->validate('someTestPassword19');
    }

    public function test_validate_validPasswords()
    {
        $this->passwordValidator->validate('someTest{Password19');
        $this->passwordValidator->validate('2%IVR4$Mw%8drTGJD!$IljgvFOr0@YWxRLb0QBt!G6Kf3');
        $this->passwordValidator->validate('somTestPsswrd!0');
    }

}
