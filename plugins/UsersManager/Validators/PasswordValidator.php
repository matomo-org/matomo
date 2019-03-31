<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\Validators;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\Exception;

class PasswordValidator extends BaseValidator
{

    /** @var int */
    private $minLength = UsersManager::PASSWORD_DEFAULT_MIN_LENGTH;

    /** @var bool */
    private $isOneUppercaseRequired = false;

    /** @var bool */
    private $isOneLowercaseRequired = false;

    /** @var bool */
    private $isOneNumberRequired = false;

    /** @var bool */
    private $isOneSpecialCharacterRequired = false;

    public function __construct($minLength = UsersManager::PASSWORD_DEFAULT_MIN_LENGTH, $isOneUppercaseRequired = false, $isOneLowercaseRequired = false, $isOneNumberRequired = false, $isOneSpecialCharacterRequired = false)
    {
        $this->minLength = (int) $minLength;
        $this->isOneUppercaseRequired = (bool) $isOneUppercaseRequired;
        $this->isOneLowercaseRequired = (bool) $isOneLowercaseRequired;
        $this->isOneNumberRequired = (bool) $isOneNumberRequired;
        $this->isOneSpecialCharacterRequired = (bool) $isOneSpecialCharacterRequired;
    }

    /**
     * The method to validate a value. If the value has not an expected format, an instance of
     * {@link Piwik\Validators\Exception} should be thrown.
     *
     * @param $value
     *
     * @return bool
     * @throws \Exception
     */
    public function validate($value)
    {
        if (Common::mb_strlen($value) < $this->minLength) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidPassword', [$this->minLength]));
        }

        if ($this->isOneUppercaseRequired && !preg_match('/[A-Z]/', $value)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidPasswordUppercaseLetterRequired'));
        }

        if ($this->isOneLowercaseRequired && !preg_match('/[a-z]/', $value)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidPasswordLowercaseLetterRequired'));
        }

        if ($this->isOneNumberRequired && !preg_match('/[0-9]/', $value)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidPasswordNumberRequired'));
        }

        if ($this->isOneSpecialCharacterRequired && !preg_match('/[^a-zA-Z0-9]/', $value)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidPasswordSpecialCharacterRequired'));
        }

        return true;
    }

}
