<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\UsersManager\Model as UserModel;
use Piwik\Plugins\LanguagesManager\Model as LangModel;
use Piwik\Translation\Translator;

/**
 * Helper class allowing to run a callback function with the given user's preferred language
 * temporarily set for the translator.
 *
 * This is handy for cases such as sending a reminder or notification email from the scheduled task
 * where the system is not running under the given user. Previously set language is restored after the callback is run.
 *
 * Usage:
 *
 * LanguageHelper::doWithUserLanguage('someUsernameOrEmail', function() { ... code to run ... });
 */
class LanguagesHelper
{
    private static function getUserFromEmailOrLogin(string $emailOrLogin): ?array
    {
        $userModel = new UserModel();

        $user = null;
        if ($userModel->userExists($emailOrLogin)) {
            $user = $userModel->getUser($emailOrLogin);
        } elseif ($userModel->userEmailExists($emailOrLogin)) {
            $user = $userModel->getUserByEmail($emailOrLogin);
        }

        return $user;
    }

    public static function doWithUserLanguage(string $emailOrLogin, callable $callback)
    {
        $user = self::getUserFromEmailOrLogin($emailOrLogin);

        if (!$user) {
            return $callback();
        }

        $langModel = new LangModel();
        $userLanguage = $langModel->getLanguageForUser($user['login']);

        $translator = StaticContainer::get(Translator::class);

        $backupLanguage = $translator->getCurrentLanguage();
        if (empty($backupLanguage)) {
            // if no language was set yet, ensure to restore the default language
            $backupLanguage = $translator->getDefaultLanguage();
        }

        if (!empty($userLanguage)) {
            // temporarily overwrite the language to perform the callback action
            $translator->setCurrentLanguage($userLanguage);
        }

        try {
            $result = $callback();
        } catch (\Throwable $ex) {
            throw $ex;
        } finally {
            $translator->setCurrentLanguage($backupLanguage);
        }

        return $result;
    }
}
