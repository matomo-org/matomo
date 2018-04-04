<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Notification;
use Piwik\Option;
use Piwik\Plugin;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp2;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_3_4_1_b1 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        // save timestamp as from now on region codes will be converted to ISO
        Option::set(GeoIp2::SWITCH_TO_ISO_REGIONS_OPTION_NAME, time());

        $pluginManager = Plugin\Manager::getInstance();

        // Disable GeoIP2 plugin as it won't work correctly anymore
        if ($pluginManager->isPluginActivated('GeoIP2')) {
            $pluginManager->deactivatePlugin('GeoIP2');

            // notify user that geoip2 plugin has been disabled
            $notification = new Notification('GeoIP2 plugin has been disabled as support of GeoIP2 is now available in core. Please check your geo location settings');
            $notification->type = Notification::TYPE_PERSISTENT;
            Notification\Manager::notify('geoip2disabled', $notification);
        }

        // Try to directly switch to new GeoIP2 provider if old GeoIP2 plugin was used
        if (LocationProvider::getCurrentProvider() === 'geoip2_php') {
            if (LocationProvider::getProviderById(GeoIp2\Php::ID)) {
                LocationProvider::setCurrentProvider(GeoIp2\Php::ID);
            } else {
                LocationProvider::setCurrentProvider(LocationProvider\DefaultProvider::ID);
            }
        }

    }
}
