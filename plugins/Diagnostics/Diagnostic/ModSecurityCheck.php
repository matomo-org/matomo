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

        if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
            $comment = sprintf($this->translator->translate('Installation_SystemCheckModSecurityHelp'),
              $this->translator->translate('Installation_SystemCheckModSecurityOn'),
              "<a href='https://matomo.org/faq/troubleshooting/faq_100/' target='_blank'>FAQ</a>");
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, $comment));
        }

        $comment = sprintf($this->translator->translate('Installation_SystemCheckModSecurityHelp'),
          $this->translator->translate('Installation_SystemCheckModSecurityOff'),
          "<a href='https://matomo.org/faq/troubleshooting/faq_100/' target='_blank'>FAQ</a>");

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
