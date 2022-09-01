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

        if (GeneralConfig::getConfigValue('force_matomo_http_request') == 1) {
            //if config is on, show info
            $comment = $this->translator->translate('Installation_MatomoHttpRequestConfigInfo', $faqLinks);
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_INFORMATIONAL, $comment));
        } elseif (!Http::isUpdatingOverHttps()) {
            // failed to request over https
            $warning = 'We will soon switch to HTTPS by default. Please make sure that HTTPS works on your environment or turn on `force_matomo_http_request`, otherwise it could cause Matomo updates to fail in the future. For more details please read our <a href="https://matomo.org/faq/faq-how-to-disable-https-for-matomo-org-and-api-matomo-org-requests" rel="noreferrer noopener" target="_blank">FAQ</a>.';
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $warning));
        } else {
            // successful using https
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }
    }
}
