<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\Config;

/**
 * How far back the data of an added page is rebuilt, which is the core re-archiving window.
 */
final class RebuildWindow
{
    /**
     * Returns `[General] rearchive_reports_in_past_last_n_months` parsed as `Piwik::getEarliestDateToRearchive()` parses
     * it (`6`, `last6`); a value it ignores gives 0, which means only new data is processed.
     */
    public static function getMonths(): int
    {
        $general = Config::getInstance()->General;
        $value = is_array($general) ? $general['rearchive_reports_in_past_last_n_months'] ?? 0 : 0;

        if (!is_scalar($value)) {
            return 0;
        }

        $months = (int) (is_numeric($value) ? $value : str_replace('last', '', (string) $value));

        return max(0, $months);
    }
}
