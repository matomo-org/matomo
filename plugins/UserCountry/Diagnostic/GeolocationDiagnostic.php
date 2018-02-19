<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\UserCountry\Diagnostic;

use Piwik\Config;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
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
        $isRecommendedProvider = in_array($currentProviderId, array(LocationProvider\GeoIp2\Php::ID));
        $isProviderInstalled = (isset($allProviders[$currentProviderId]['status']) && $allProviders[$currentProviderId]['status'] == LocationProvider::INSTALLED);

        if ($isRecommendedProvider && $isProviderInstalled) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }

        $isGeoIPLegacyProvider = LocationProvider::getCurrentProvider() instanceof LocationProvider\GeoIp;

        if ($isGeoIPLegacyProvider && $isProviderInstalled) {
            $discontinuedWarning = '<div>Support of GeoIP Legacy location providers has been deprecated and will be removed in Matomo 4.</div><strong>Please switch to GeoIP 2 soon!</strong>';
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $discontinuedWarning));
        }

        if ($isProviderInstalled) {
            $comment = $this->translator->translate('UserCountry_GeoIpLocationProviderNotRecomnended') . ' ';
            $comment .= $this->translator->translate('UserCountry_GeoIpLocationProviderDesc_ServerBased2', array(
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
