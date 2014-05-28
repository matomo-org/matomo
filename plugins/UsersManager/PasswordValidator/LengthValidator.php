<?php
namespace Piwik\Plugins\UsersManager\PasswordValidator;

use Piwik\Piwik;
use Piwik\Plugins\UsersManager\PasswordValidator;
use Piwik\Plugins\UsersManager\UsersManager;

/**
 * Class LengthValidator
 * @package Piwik\Plugins\UsersManager\PasswordValidator
 */
class LengthValidator implements PasswordValidator
{
    /**
     * @param string $password
     * @return bool
     */
    public function validate($password)
    {
        return UsersManager::isValidPasswordString($password);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return Piwik::translate(
            'UsersManager_ExceptionInvalidPassword',
            array(
                UsersManager::PASSWORD_MIN_LENGTH,
                UsersManager::PASSWORD_MAX_LENGTH
            )
        );
    }
}
