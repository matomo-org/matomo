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
use Piwik\Url;

/**
 * Check if an update via HTTPS is possible
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
        $faqLink = [
          '<a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/faq-how-to-disable-https-for-matomo-org-and-api-matomo-org-requests') . '" rel="noreferrer noopener" target="_blank">',
          '</a>'
        ];
        $label = $this->translator->translate('Installation_SystemCheckUpdateHttps');

        if (GeneralConfig::getConfigValue('force_matomo_http_request') == 1) {
            // If the config option to force http is enabled then show 'not recommended' message
            $comment = $this->translator->translate('Installation_MatomoHttpRequestConfigInfo', $faqLink);
            return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_INFORMATIONAL, $comment)];
        }

        if (!Http::isUpdatingOverHttps()) {
            // https is not available, show error
            $error = $this->translator->translate('Installation_MatomoHttpsNotSupported', $faqLink);
            return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_ERROR, $error)];
        }

        // Success, https is available
        return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK)];
    }
}
