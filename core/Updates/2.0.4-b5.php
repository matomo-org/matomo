<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\MobileMessaging\API as MobileMessagingApi;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Updater;
use Piwik\Config;
use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_2_0_4_b5 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array(
            // ignore existing column name error (1060)
            'ALTER TABLE ' . Common::prefixTable('user')
            . " ADD COLUMN `superuser_access` tinyint(2) unsigned NOT NULL DEFAULT '0' AFTER token_auth" => 1060,
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());

        self::migrateExistingMobileMessagingOptions();
        self::migrateConfigSuperUserToDb();
    }

    private static function migrateExistingMobileMessagingOptions()
    {
        if (MobileMessagingApi::getInstance()->getDelegatedManagement()) {
            return;
        }

        // team_MobileMessagingSettings -> _MobileMessagingSettings as it is no longer guaranteed the Super User's
        // username is always the same

        $optionName     = MobileMessaging::USER_SETTINGS_POSTFIX_OPTION;
        $superUserLogin = Config::getInstance()->superuser['login'];
        $optionPrefixed = $superUserLogin . $optionName;

        // team_MobileMessagingSettings
        $value = Option::get($optionPrefixed);

        if (false !== $value) {
            // _MobileMessagingSettings
            Option::set($optionName, $value);
            Option::delete($optionPrefixed);
        }
    }

    private static function migrateConfigSuperUserToDb()
    {
        $superUser = \Piwik\Config::getInstance()->superuser;
        $userApi   = \Piwik\Plugins\UsersManager\API::getInstance();

        Db::get()->insert(Common::prefixTable('user'), array(
                'login'      => $superUser['login'],
                'password'   => $superUser['password'],
                'alias'      => $superUser['login'],
                'email'      => $superUser['email'],
                'token_auth' => $userApi->getTokenAuth($superUser['login'], $superUser['password']),
                'date_registered'  => Date::now()->getDatetime(),
                'superuser_access' => 1
            )
        );

        \Piwik\Config::getInstance()->General['salt'] = $superUser['salt'];
        \Piwik\Config::getInstance()->superuser       = array();
        \Piwik\Config::getInstance()->forceSave();
    }
}
