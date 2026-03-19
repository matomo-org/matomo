<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager;

class UserLoginHelper
{
    /**
     * @return array<string, mixed>|null
     */
    public static function findUserByLoginOrEmail(string $loginOrEmail): ?array
    {
        $model = new Model();

        if ($model->userExists($loginOrEmail)) {
            return $model->getUser($loginOrEmail);
        }

        if ($model->userEmailExists($loginOrEmail)) {
            $user = $model->getUserByEmail($loginOrEmail);
            if (!empty($user)) {
                return $user;
            }
        }

        return null;
    }

    public static function findCanonicalLoginByLoginOrEmail(string $loginOrEmail): ?string
    {
        if (empty($loginOrEmail)) {
            return null;
        }

        $model = new Model();
        if ($model->userExists($loginOrEmail)) {
            return $loginOrEmail;
        }

        if ($model->userEmailExists($loginOrEmail)) {
            $user = $model->getUserByEmail($loginOrEmail);
            if (!empty($user['login']) && is_string($user['login'])) {
                return $user['login'];
            }
        }

        return null;
    }

    public static function normalizeLoginOrEmailToLogin(string $loginOrEmail): string
    {
        $login = self::findCanonicalLoginByLoginOrEmail($loginOrEmail);
        if (!empty($login)) {
            return $login;
        }

        return $loginOrEmail;
    }
}
