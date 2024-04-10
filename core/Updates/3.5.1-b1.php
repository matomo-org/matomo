<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Filesystem;
use Piwik\Notification;
use Piwik\Plugin\Manager;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_3_5_1_b1 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        // try to deactivate and remove the GeoIP2 plugin from marketplace,
        // which causes problems with GeoIp2 plugin included in core
        // Skip if new file `isoRegionNames.php` is detected within `GeoIP2` directory, as that means filesystem is case
        // insensitive and GeoIP2 plugin has been partially overwritten by GeoIp2 plugin
        try {
            if (is_dir(PIWIK_INCLUDE_PATH . '/plugins/GeoIP2') && !file_exists(PIWIK_INCLUDE_PATH . '/plugins/GeoIP2/data/isoRegionNames.php')) {
                // first remove the plugin files, as trying to deactivate would load the plugin files resulting in a fatal error
                Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . '/plugins/GeoIP2', true);

                // if plugin was activated, trigger deactivation to remove the config entry and set a notification
                if (Manager::getInstance()->isPluginActivated('GeoIP2')) {
                    @Manager::getInstance()->deactivatePlugin('GeoIP2');
                    $notification = new Notification('GeoIP2 plugin from Marketplace has been removed due to compatibility issues. Please check your geolocation settings.');
                    $notification->context = Notification::CONTEXT_WARNING;
                    $notification->type = Notification::TYPE_PERSISTENT;
                    Notification\Manager::notify('GeoIP2_update_warning', $notification);
                }

                // uninstall the plugin to remove it from config
                @Manager::getInstance()->uninstallPlugin('GeoIP2');
            }
        } catch (\Exception $e) {
        }
    }
}
