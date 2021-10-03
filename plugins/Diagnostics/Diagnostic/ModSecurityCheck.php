<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\Filechecks;
use Piwik\Http;
use Piwik\SettingsServer;
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
        $url = $_SERVER['SERVER_NAME'] . "/matomo.php?q=\'1%20OR%201=1";
        $ch = curl_init();
        // set some cURL options
        $ret = curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_setopt($ch, CURLOPT_HEADER, 1);
        $ret = curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $ret = curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// execute
        $ret = curl_exec($ch);

        if (empty($ret)) {
            // some kind of an error happened
            curl_close($ch); // close cURL handler
        } else {
            $info = curl_getinfo($ch);
            curl_close($ch); // close cURL handler

            if ($info['http_code'] == 403) {
                return true;
            }
        }
        return false;

    }
}
