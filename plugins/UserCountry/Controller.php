<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Exception;
use Piwik\Common;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\SettingsPiwik;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function getDistinctCountries()
    {
        $view = new View('@UserCountry/getDistinctCountries');

        $view->urlSparklineCountries = $this->getUrlSparkline('getLastDistinctCountriesGraph');
        $view->numberDistinctCountries = $this->getNumberOfDistinctCountries();

        return $view->render();
    }

    public function adminIndex()
    {
        $this->dieIfGeolocationAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();
        $view = new View('@UserCountry/adminIndex');

        $allProviderInfo = LocationProvider::getAllProviderInfo($newline = '<br/>', $includeExtra = true);
        $view->locationProviders = $allProviderInfo;
        $view->currentProviderId = LocationProvider::getCurrentProviderId();
        $view->thisIP = IP::getIpFromHeader();
        $view->hasGeoIp2Provider = Manager::getInstance()->isPluginActivated('GeoIp2');

        // check if there is a working provider (that isn't the default one)
        $isThereWorkingProvider = false;
        foreach ($allProviderInfo as $id => $provider) {
            if ($id != DefaultProvider::ID
                && $provider['status'] == LocationProvider::INSTALLED
            ) {
                $isThereWorkingProvider = true;
                break;
            }
        }
        $view->isThereWorkingProvider = $isThereWorkingProvider;

        $configurations = $setUpGuides = '';
        foreach (LocationProvider::getAllProviders() as $provider) {
            $configurations .= $provider->renderConfiguration();
            $setUpGuides .= $provider->renderSetUpGuide();
        }

        $view->configurations = $configurations;
        $view->setUpGuides = $setUpGuides;
        $this->setBasicVariablesView($view);
        $this->setBasicVariablesAdminView($view);

        return $view->render();
    }

    /**
     * Echo's a pretty formatted location using a specific LocationProvider.
     *
     * Input:
     *   The 'id' query parameter must be set to the ID of the LocationProvider to use.
     *
     * Output:
     *   The pretty formatted location that was obtained. Will be HTML.
     */
    public function getLocationUsingProvider()
    {
        $this->dieIfGeolocationAdminIsDisabled();
        Piwik::checkUserHasSuperUserAccess();

        $providerId = Common::getRequestVar('id');
        $provider = LocationProvider::getProviderById($providerId);
        if (empty($provider)) {
            throw new Exception("Invalid provider ID: '$providerId'.");
        }

        $location = $provider->getLocation(array('ip'                => IP::getIpFromHeader(),
                                                 'lang'              => Common::getBrowserLanguage(),
                                                 'disable_fallbacks' => true));
        $location = LocationProvider::prettyFormatLocation(
            $location, $newline = '<br/>', $includeExtra = true);

        return $location;
    }

    public function getNumberOfDistinctCountries()
    {
        return $this->getNumericValue('UserCountry.getNumberOfDistinctCountries');
    }

    public function getLastDistinctCountriesGraph()
    {
        $view = $this->getLastUnitGraph('UserCountry', __FUNCTION__, "UserCountry.getNumberOfDistinctCountries");
        $view->config->columns_to_display = array('UserCountry_distinctCountries');
        return $this->renderView($view);
    }

    private function dieIfGeolocationAdminIsDisabled()
    {
        if (!UserCountry::isGeoLocationAdminEnabled()) {
            throw new \Exception('Geo location setting page has been disabled.');
        }
    }
}
