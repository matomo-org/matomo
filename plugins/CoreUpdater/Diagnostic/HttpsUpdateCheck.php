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
        $faqLinks = [
          '<a href="https://matomo.org/faq/faq-how-to-disable-https-for-matomo-org-and-api-matomo-org-requests" rel="noreferrer noopener" target="_blank">',
          '</a>'
        ];
        $label = $this->translator->translate('Installation_SystemCheckUpdateHttps');

        if (GeneralConfig::getConfigValue('force_matomo_http_request') === 1) {
            //if config is on, show info
            $comment = $this->translator->translate('Installation_MatomoHttpRequestConfigInfo', $faqLinks);
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_INFORMATIONAL, $comment));
        } elseif (!Http::isUpdatingOverHttps()) {
            // failed to request over https
            $comment = $this->translator->translate('Installation_MatomoHttpsNotSupportWarning', $faqLinks);
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
        } else {

            // successful using https
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }
    }
}
