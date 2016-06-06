<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\CliMulti;
use Piwik\Config;
use Piwik\Http;
use Piwik\Translation\Translator;
use Piwik\Url;

/**
 * Check if cron archiving can run through CLI.
 */
class CronArchivingCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckCronArchiveProcess');
        $comment = $this->translator->translate('Installation_SystemCheckCronArchiveProcessCLI') . ': ';

        $process = new CliMulti();

        if ($process->supportsAsync()) {
            $comment .= $this->translator->translate('General_Ok');
        } else {
            $comment .= $this->translator->translate('Installation_NotSupported')
                . ' ' . $this->translator->translate('Goals_Optional');
        }

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, $comment));
    }
}
