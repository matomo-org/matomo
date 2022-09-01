<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Check that the memory limit is enough.
 */
class MemoryLimitCheck implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var int
     */
    private $minimumMemoryLimit;

    public function __construct(Translator $translator, $minimumMemoryLimit)
    {
        $this->translator = $translator;
        $this->minimumMemoryLimit = $minimumMemoryLimit;
    }

    public function execute()
    {
        $label = $this->translator->translate('Installation_SystemCheckMemoryLimit');

        SettingsServer::raiseMemoryLimitIfNecessary();

        $memoryLimit = SettingsServer::getMemoryLimitValue();
        $comment = $memoryLimit . 'M';

        if(false === $memoryLimit) {
            $status = DiagnosticResult::STATUS_OK;
            $comment = $this->translator->translate('Installation_SystemCheckMemoryNoMemoryLimitSet');
        } else if ($memoryLimit >= $this->minimumMemoryLimit) {
            $status = DiagnosticResult::STATUS_OK;
        } else {
            $status = DiagnosticResult::STATUS_WARNING;
            $comment .= sprintf(
                '<br />%s<br />%s',
                $this->translator->translate('Installation_SystemCheckMemoryLimitHelp'),
                $this->translator->translate('Installation_RestartWebServer')
            );
        }

        return array(DiagnosticResult::singleResult($label, $status, $comment));
    }
}
