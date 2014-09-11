<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\UpdaterErrorException;
use Piwik\Updates;

/**
 */
class Updates_2_0_4_b7 extends Updates
{
    static function getSql()
    {
        return array();
    }

    static function update()
    {
        try {
            self::migrateExistingMobileMessagingOptions();
        } catch (\Exception $e) {
            throw new UpdaterErrorException($e->getMessage());
        }
    }

    private static function migrateExistingMobileMessagingOptions()
    {
        if (Option::get(MobileMessaging::DELEGATED_MANAGEMENT_OPTION) == 'true') {
            return;
        }

        // copy $superUserLogin_MobileMessagingSettings -> _MobileMessagingSettings as settings are managed globally

        $optionName = MobileMessaging::USER_SETTINGS_POSTFIX_OPTION;
        $superUsers = UsersManagerApi::getInstance()->getUsersHavingSuperUserAccess();

        if (empty($superUsers)) {
            return;
        }

        $firstSuperUser = array_shift($superUsers);

        if (empty($firstSuperUser)) {
            return;
        }

        $superUserLogin = $firstSuperUser['login'];
        $optionPrefixed = $superUserLogin . $optionName;

        // $superUserLogin_MobileMessagingSettings
        $value = Option::get($optionPrefixed);

        if (false !== $value) {
            // _MobileMessagingSettings
            Option::set($optionName, $value);
        }
    }
}
