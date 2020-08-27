<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Development;
use Piwik\FileIntegrity;
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

        if(Development::isEnabled()) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, '(Disabled in development mode)'));
        }

        list($ok, $messages) = FileIntegrity::getFileIntegrityInformation();

        if ($ok) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, implode('<br/>', $messages)));
        }

        $comment = $this->translator->translate('General_FileIntegrityWarning');

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
