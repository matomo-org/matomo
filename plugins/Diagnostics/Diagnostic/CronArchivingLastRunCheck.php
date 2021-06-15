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
use Piwik\Config;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Metrics\Formatter;
use Piwik\Option;
use Piwik\Plugins\Intl\DateTimeFormatProvider;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;
use Piwik\Url;

/**
 * Check if cron archiving has run in the last 24-48 hrs.
 */
class CronArchivingLastRunCheck implements Diagnostic
{
    const SECONDS_IN_DAY = 86400;

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
        if (!SettingsPiwik::isMatomoInstalled()) {
            return [];
        }

        $label = $this->translator->translate('Diagnostics_CronArchivingLastRunCheck');
        $commandToRerun = '<code>' . $this->getArchivingCommand() . '</code>';
        $coreArchiveShort = '<code>core:archive</code>';
        $mailto = '<code>MAILTO</code>';

        // check cron archiving has been enabled
        $isBrowserTriggerDisabled = !Rules::isBrowserTriggerEnabled();
        if (!$isBrowserTriggerDisabled) {
            return [];
        }

        // check archiving has been run
        $lastRunTime = (int)Option::get(CronArchive::OPTION_ARCHIVING_FINISHED_TS);
        if (empty($lastRunTime)) {
            $comment = $this->translator->translate('Diagnostics_CronArchivingHasNotRun')
                . '<br/><br/>' . $this->translator->translate('Diagnostics_CronArchivingRunDetails',
                    [$coreArchiveShort, $mailto, $commandToRerun, '<a href="https://matomo.org/docs/setup-auto-archiving/" target="_blank" rel="noreferrer noopener">', '</a>']);
            return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_ERROR, $comment)];
        }

        $lastRunTimePretty = Date::factory($lastRunTime)->getLocalized(DateTimeFormatProvider::DATETIME_FORMAT_LONG);

        $diffTime = self::getTimeSinceLastSuccessfulRun($lastRunTime);

        $formatter = new Formatter();
        $diffTimePretty = $formatter->getPrettyTimeFromSeconds($diffTime);

        $errorComment = $this->translator->translate('Diagnostics_CronArchivingHasNotRunInAWhile', [$lastRunTimePretty, $diffTimePretty])
            . '<br/><br/>' .
            $this->translator->translate(
                'Diagnostics_CronArchivingRunDetails',
                [$coreArchiveShort, $mailto, $commandToRerun, '<a href="https://matomo.org/docs/setup-auto-archiving/" target="_blank" rel="noreferrer noopener">', '</a>']
            );

        // check archiving has been run recently
        if ($diffTime > self::SECONDS_IN_DAY * 2) {
            $result = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_ERROR, $errorComment);
        } else if ($diffTime > self::SECONDS_IN_DAY) {
            $result = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $errorComment);
        } else {
            $comment = $this->translator->translate('Diagnostics_CronArchivingRanSuccessfullyXAgo', $diffTimePretty);
            $result = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, $comment);
        }

        return [$result];
    }

    private function getArchivingCommand()
    {
        if (Url::isValidHost()) {
            $domain = Config::getHostname($checkIfTrusted = true);

            return PIWIK_INCLUDE_PATH . '/console --matomo-domain=' . $domain . ' core:archive';
        }

        return PIWIK_INCLUDE_PATH . '/console core:archive';
    }

    public static function getTimeSinceLastSuccessfulRun($lastRunTime = null)
    {
        if (empty($lastRunTime)) {
            $lastRunTime = (int)Option::get(CronArchive::OPTION_ARCHIVING_FINISHED_TS);
        }

        if (empty($lastRunTime)) {
            return null;
        }

        $now = Date::now()->getTimestamp();
        $diffTime = $now - $lastRunTime;

        return $diffTime;
    }
}
