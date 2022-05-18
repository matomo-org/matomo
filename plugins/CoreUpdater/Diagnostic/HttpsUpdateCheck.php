<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreUpdater\Diagnostic;

use Piwik\Config\GeneralConfig;
use Piwik\Http;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Translation\Translator;

/**
 * Check the HTTPS update.
 */
class HttpsUpdateCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckUpdateHttps');

        if (GeneralConfig::getConfigValue('force_matomo_ssl_request') === 0) {
            //if config is off, show info
            $comment = $this->translator->translate('Installation_MatomoSslRequestConfigInfo');;
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_INFORMATIONAL, $comment));
        } elseif (Http::isUpdatingOverHttps()) {
            // successful using https
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        } else {
            // failed to request over https
            $comment = $this->translator->translate('Installation_SystemCheckUpdateHttpsNotSupported');
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
        }

    }
}
