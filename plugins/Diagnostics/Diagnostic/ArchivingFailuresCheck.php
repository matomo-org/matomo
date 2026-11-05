<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\DataAccess\ArchiveFailures;
use Piwik\Date;
use Piwik\NumberFormatter;
use Piwik\Plugins\Intl\DateTimeFormatProvider;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Check for archives that core:archive failed to process.
 */
class ArchivingFailuresCheck implements Diagnostic
{
    private Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        if (!SettingsPiwik::isMatomoInstalled()) {
            return [];
        }

        $counts = (new ArchiveFailures())->getFailureCounts();
        if ($counts['waiting'] === 0 && $counts['givenUp'] === 0) {
            return [];
        }

        $numberFormatter = NumberFormatter::getInstance();
        $comment = $this->translator->translate('Diagnostics_ArchivingFailuresComment', [
            $numberFormatter->formatNumber($counts['waiting']),
            $numberFormatter->formatNumber($counts['givenUp']),
            Date::factory($counts['oldestFailure'])->getLocalized(DateTimeFormatProvider::DATETIME_FORMAT_LONG),
            '<code>./console core:archive-failures</code>',
        ]);

        $status = $counts['givenUp'] > 0 ? DiagnosticResult::STATUS_WARNING : DiagnosticResult::STATUS_OK;

        return [DiagnosticResult::singleResult($this->translator->translate('Diagnostics_ArchivingFailuresCheck'), $status, $comment)];
    }
}
