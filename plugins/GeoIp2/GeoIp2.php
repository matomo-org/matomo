<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2;

use Piwik\Plugins\UserCountry\LocationProvider;

/**
 *
 */
class GeoIp2 extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function deactivate()
    {
        // switch to default provider if GeoIP2 provider was in use
        if (LocationProvider::getCurrentProvider() instanceof \Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2) {
            LocationProvider::setCurrentProvider(LocationProvider\DefaultProvider::ID);
        }
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/GeoIp2/angularjs/geoip2-updater/geoip2-updater.controller.js";
        $jsFiles[] = "plugins/GeoIp2/angularjs/geoip2-updater/geoip2-updater.directive.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "GeoIp2_FatalErrorDuringDownload";
        $translationKeys[] = "GeoIp2_SetupAutomaticUpdatesOfGeoIP";
        $translationKeys[] = "General_Done";
        $translationKeys[] = "General_Save";
        $translationKeys[] = "General_Continue";
    }
}
