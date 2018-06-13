<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\UserCountry\Diagnostic;

use Piwik\Config;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Translation\Translator;

/**
 * Check the geolocation setup.
 */
class GeolocationDiagnostic implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $isPiwikInstalling = !Config::getInstance()->existsLocalConfig();
        if ($isPiwikInstalling) {
            // Skip the diagnostic if Piwik is being installed
            return array();
        }

        $label = $this->translator->translate('UserCountry_Geolocation');

        $currentProviderId = LocationProvider::getCurrentProviderId();
        $allProviders = LocationProvider::getAllProviderInfo();
        $isNotRecommendedProvider = in_array($currentProviderId, array(
            LocationProvider\DefaultProvider::ID,
            LocationProvider\GeoIp\ServerBased::ID,
            GeoIp2\ServerModule::ID));
        $isProviderInstalled = (isset($allProviders[$currentProviderId]['status']) && $allProviders[$currentProviderId]['status'] == LocationProvider::INSTALLED);

        if (!$isNotRecommendedProvider && $isProviderInstalled) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }

        if ($isProviderInstalled) {
            $comment = $this->translator->translate('UserCountry_GeoIpLocationProviderNotRecomnended') . ' ';
            $message = Manager::getInstance()->isPluginActivated('GeoIp2') ? 'GeoIp2_LocationProviderDesc_ServerModule2' : 'UserCountry_GeoIpLocationProviderDesc_ServerBased2';
            $comment .= $this->translator->translate($message, array(
                '<a href="https://matomo.org/docs/geo-locate/" rel="noreferrer" target="_blank">', '', '', '</a>'
            ));
        } else {
            $comment = $this->translator->translate('UserCountry_DefaultLocationProviderDesc1') . ' ';
            $comment .= $this->translator->translate('UserCountry_DefaultLocationProviderDesc2', array(
                '<a href="https://matomo.org/docs/geo-locate/" rel="noreferrer" target="_blank">', '', '', '</a>'
            ));
        }

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
