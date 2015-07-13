<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Filechecks;
use Piwik\Translation\Translator;

/**
 * Check the files integrity.
 */
class FileIntegrityCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckFileIntegrity');

        $messages = Filechecks::getFileIntegrityInformation();
        $ok = array_shift($messages);

        if (empty($messages)) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }

        if ($ok) {
            $status = DiagnosticResult::STATUS_WARNING;
            return array(DiagnosticResult::singleResult($label, $status, $messages[0]));
        }

        $comment = $this->translator->translate('General_FileIntegrityWarningExplanation');

        // Keep only the 20 first lines else it becomes unmanageable
        if (count($messages) > 20) {
            $messages = array_slice($messages, 0, 20);
            $messages[] = '...';
        }
        $comment .= '<br/><br/><pre style="overflow-x: scroll;max-width: 600px;">'
            . implode("\n", $messages) . '</pre>';

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
