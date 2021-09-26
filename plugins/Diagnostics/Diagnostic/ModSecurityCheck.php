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

        $modSecurity = in_array('mod_rewrite', get_loaded_extensions()) !== false;

        $status = $modSecurity ? DiagnosticResult::STATUS_WARNING : DiagnosticResult::STATUS_OK;
        $comment = sprintf($this->translator->translate('Installation_SystemCheckModSecurityHelp'),
          $modSecurity ? "On" : "Off / (Not Detected)",
          "<a href='https://matomo.org/faq/troubleshooting/faq_100/' target='_blank'>FAQ</a>");

        return array(DiagnosticResult::singleResult($label, $status, $comment));
    }
}
