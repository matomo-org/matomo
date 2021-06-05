<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\UserCountry\Diagnostic;

use Piwik\Config;
use Piwik\Piwik;
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
        $isMatomoInstalling = !Config::getInstance()->existsLocalConfig();
        if ($isMatomoInstalling) {
            // Skip the diagnostic if Matomo is being installed
            return array();
        }

        $label = $this->translator->translate('UserCountry_Geolocation');

        $currentProviderId = LocationProvider::getCurrentProviderId();
        $allProviders = LocationProvider::getAllProviderInfo();

        $providerStatus = $allProviders[$currentProviderId]['status'] ?? LocationProvider::NOT_INSTALLED;

        $providerWarning = $allProviders[$currentProviderId]['usageWarning'] ?? null;
        $statusMessage = $allProviders[$currentProviderId]['statusMessage'] ?? null;

        if ($providerStatus === LocationProvider::BROKEN) {
            $message = Piwik::translate('UserCountry_GeolocationProviderBroken', '<strong>' . $allProviders[$currentProviderId]['title'] . '</strong>');
            if ($statusMessage) {
                $message .= '<br /><br />' . $statusMessage;
            }
            return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_ERROR, $message)];
        }

        if ($providerStatus === LocationProvider::NOT_INSTALLED) {
            $provider = $allProviders[$currentProviderId] ?? null;

            if ($provider) {
                $message = Piwik::translate('UserCountry_GeolocationProviderBroken', '<strong>' . $allProviders[$currentProviderId]['title'] . '</strong>');
            } else {
                $message = Piwik::translate('UserCountry_GeolocationProviderUnavailable', '<strong>' . LocationProvider::getCurrentProviderId() . '</strong>');
            }

            return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_ERROR, $message)];
        }

        if (!empty($providerWarning)) {
            return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $providerWarning)];
        }

        $availableInfo = LocationProvider::getProviderById($currentProviderId)->getSupportedLocationInfo();
        $message = sprintf("%s (%s)", $currentProviderId, implode(', ', array_keys(array_filter($availableInfo))));

        return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, $message)];
    }
}
