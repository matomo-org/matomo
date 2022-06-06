<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Validators;

use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Plugins\UsersManager\API as APIUsersManager;

class Login extends BaseValidator
{
    protected $login;
    const loginMinimumLength = 2;
    const loginMaximumLength = 100;


    public function validate($value)
    {
        if (!SettingsPiwik::isUserCredentialsSanityCheckEnabled()
          && !empty($value)
        ) {
            return;
        }

        $l = strlen($value);
        if (!($l >= self::loginMinimumLength
          && $l <=  self::loginMaximumLength
          && (preg_match('/^[A-Za-zÄäÖöÜüß0-9_.@+-]*$/D', $value) > 0))
        ) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidLoginFormat',
              array(self::loginMinimumLength, self::loginMaximumLength)));
        }

        $this->login = $value;
        return $this;
    }

    public function isUnique()
    {
        if (empty($this->login)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidLoginFormat',
              array(self::loginMinimumLength, self::loginMaximumLength)));
        }

        if (APIUsersManager::getInstance()->userExists($this->login)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionLoginExists', $this->login));
        }

        if (APIUsersManager::getInstance()->userEmailExists($this->login)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionLoginExistsAsEmail', $this->login));
        }
    }
}