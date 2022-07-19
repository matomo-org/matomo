<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UsersManager\Validators;

use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\Exception;

class Login extends BaseValidator
{
    const loginMinimumLength = 2;
    const loginMaximumLength = 100;

    private $checkUnique;

    public function __construct($checkUnique = false)
    {
        $this->checkUnique = $checkUnique;
    }

    public function validate($value)
    {
        if (
            !SettingsPiwik::isUserCredentialsSanityCheckEnabled()
            && !empty($value)
        ) {
            return;
        }

        $l = strlen($value);
        if (
            !($l >= self::loginMinimumLength
            && $l <= self::loginMaximumLength
            && (preg_match('/^[A-Za-zÄäÖöÜüß0-9_.@+-]*$/D', $value) > 0))
        ) {
            throw new Exception(Piwik::translate(
                'UsersManager_ExceptionInvalidLoginFormat',
                [self::loginMinimumLength, self::loginMaximumLength]
            ));
        }

        if ($this->checkUnique) {
            $this->isUnique($value);
        }
    }

    /**
     * check if login already exist in database
     * @param $login
     * @throws \Exception
     */
    private function isUnique($login)
    {
        if (APIUsersManager::getInstance()->userExists($login)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionLoginExists', $login));
        }

        if (APIUsersManager::getInstance()->userEmailExists($login)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionLoginExistsAsEmail', $login));
        }
    }
}
