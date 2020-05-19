<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Piwik\API\Request;

class UserUpdater
{

    /**
     * Use this method if you have to update the user without having the ability to ask the user for a password confirmation
     * @param $userLogin
     * @param bool $password
     * @param bool $email
     * @param bool $_isPasswordHashed
     * @throws \Exception
     */
    public function updateUserWithoutCurrentPassword(
        $userLogin,
        $password = false,
        $email = false,
        $_isPasswordHashed = false
    )
    {
        API::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = false;
        try {
            Request::processRequest('UsersManager.updateUser', [
                'userLogin' => $userLogin,
                'password' => $password,
                'email' => $email,
                '_isPasswordHashed' => $_isPasswordHashed,
            ], $default = []);
            API::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = true;
        } catch (\Exception $e) {
            API::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = true;
            throw $e;
        }
    }

    public function setSuperUserAccessWithoutCurrentPassword($userLogin, $hasSuperUserAccess)
    {
        API::$SET_SUPERUSER_ACCESS_REQUIRE_PASSWORD_CONFIRMATION = false;
        try {
            Request::processRequest('UsersManager.setSuperUserAccess', [
                'userLogin' => $userLogin,
                'hasSuperUserAccess' => $hasSuperUserAccess,
            ], $default = []);
        } finally {
            API::$SET_SUPERUSER_ACCESS_REQUIRE_PASSWORD_CONFIRMATION = true;
        }
    }
}