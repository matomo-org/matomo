<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\ArchiveProcessor\Rules;
use Piwik\CliMulti;
use Piwik\CronArchive;
use Piwik\Metrics\Formatter;
use Piwik\Option;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

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
        $label = $this->translator->translate('Installation_SystemCheckCronArchiveProcess') . ' (' .
            $this->translator->translate('Installation_FasterReportLoading') . ')';

        if (SettingsPiwik::isMatomoInstalled()) {
            $isBrowserTriggerEnabled = Rules::isBrowserTriggerEnabled();
            if ($isBrowserTriggerEnabled) {
                $comment = $this->translator->translate('Diagnostics_BrowserTriggeredArchivingEnabled', [
                    '<a href="https://matomo.org/docs/setup-auto-archiving/" target="_blank" rel="noreferrer noopener">', '</a>']);
                $result[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment);

                $archiveLastStarted = Option::get(CronArchive::OPTION_ARCHIVING_STARTED_TS);
                $thirtySixHoursAgoInSeconds = 36 * 3600;
                if ($archiveLastStarted && $archiveLastStarted > (time() - $thirtySixHoursAgoInSeconds)) {
                    // auto archive was used recently... if they maybe only once ran core:archive then eventually it will correct
                    // itself and no longer show this
                    $formatter = new Formatter();
                    $lastStarted = $formatter->getPrettyTimeFromSeconds(time() - $archiveLastStarted, true);
                    $label = $this->translator->translate('Diagnostics_BrowserAndAutoArchivingEnabledLabel');
                    $comment = $this->translator->translate('Diagnostics_BrowserAndAutoArchivingEnabledComment', [
                        '<a href="https://matomo.org/docs/setup-auto-archiving/" target="_blank" rel="noreferrer noopener">', '</a>', $lastStarted]);
                    $result[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment);
                }
            }
        }

        $comment = '';

        $process = new CliMulti();
        if ($process->supportsAsync()) {
            $comment .= $this->translator->translate('General_Ok');
            $status = DiagnosticResult::STATUS_OK;
        } else {
            $reasons = CliMulti\Process::isSupportedWithReason();
            if (empty($reasons)) {
                $reasonText = $this->translator->translate('General_Unknown');
            } else {
                $reasonText = implode(', ', $reasons);
            }
            $comment .= $this->translator->translate('Installation_NotSupported')
                . ' ' . $this->translator->translate('Goals_Optional')
                . ' (' . $this->translator->translate('General_Reasons') . ': ' . $reasonText . ')'
                . $this->translator->translate('General_LearnMore', [' <a target="_blank" href="https://matomo.org/faq/troubleshooting/how-to-make-the-diagnostic-managing-processes-via-cli-to-display-ok/">', '</a>']);
            $status = DiagnosticResult::STATUS_INFORMATIONAL;
        }

        $label = $this->translator->translate('Installation_SystemCheckCronArchiveProcess') . ' - '
            . $this->translator->translate('Installation_SystemCheckCronArchiveProcessCLI');
        $result[] = DiagnosticResult::singleResult($label, $status, $comment);

        return $result;
    }
}
