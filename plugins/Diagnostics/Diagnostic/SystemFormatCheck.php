<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Check that the memory limit is enough.
 */
class SystemFormatCheck implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var int
     */

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $label = $this->translator->translate('Installation_SystemCheckProcessorFormat');

        if(PHP_INT_SIZE===8) {
            $status = DiagnosticResult::STATUS_OK;
            $comment = $this->translator->translate('Installation_SystemCheckProcessorFormat64');
        } else{
            $status = DiagnosticResult::STATUS_WARNING;
            $comment = $this->translator->translate('Installation_SystemCheckProcessorFormat32');
        }

        return array(DiagnosticResult::singleResult($label, $status, $comment));
    }
}
