<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Plugins\CoreUpdater;
use Piwik\Translation\Translator;

/**
 * Check that Piwik's mod security is off.
 */
class ModSecurityCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckModSecurity');

        $status = DiagnosticResult::STATUS_OK;

        $comment = sprintf($this->translator->translate('Installation_SystemCheckModSecurityHelp'),
          $this->translator->translate('Installation_SystemCheckModSecurityOff'),
          "<a href='https://matomo.org/faq/troubleshooting/faq_100/' target='_blank'>FAQ</a>");
        // mod security is detected

        if ($this->checkModSecurity()) {
            $comment = sprintf($this->translator->translate('Installation_SystemCheckModSecurityHelp'),
              $this->translator->translate('Installation_SystemCheckModSecurityOn'),
              "<a href='https://matomo.org/faq/troubleshooting/faq_100/' target='_blank'>FAQ</a>");

            $status = DiagnosticResult::STATUS_WARNING;
        }


        return array(DiagnosticResult::singleResult($label, $status, $comment));
    }

    private function checkModSecurity()
    {
        // check sql injection
        $protocol = CoreUpdater\Controller::isUpdatingOverHttps() ? 'https://' : 'http://';
        $url = $protocol . $_SERVER['SERVER_NAME'] . "/matomo.php?q=\'1%20OR%201=1";

        $ch = curl_init($url);
        // set some cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch); // close cURL handler

        if ($info['http_code'] === 403) {
            return true;
        }
        return false;

    }
}
