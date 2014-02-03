<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Updater;
use Piwik\Config;
use Piwik\UpdaterErrorException;
use Piwik\Updates;

/**
 */
class Updates_2_0_4_b8 extends Updates
{
    static function getSql($schema = 'Myisam')
    {
        return array();
    }

    static function update()
    {
        try {
            self::migrateBrandingConfigToDatabase();
        } catch (\Exception $e) {
            throw new UpdaterErrorException($e->getMessage());
        }
    }

    private static function migrateBrandingConfigToDatabase()
    {
        $config = Config::getInstance();

        $branding = $config->branding;

        if (!empty($branding) && array_key_exists('use_custom_logo', $branding)) {
            $useCustomLogo = $branding['use_custom_logo'];

            $customLogo = new CustomLogo();
            $useCustomLogo ? $customLogo->enable() : $customLogo->disable();
        }

        $config->branding = array();
        $config->forceSave();
    }
}
