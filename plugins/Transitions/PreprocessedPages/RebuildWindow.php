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
     * Returns `[General] rearchive_reports_in_past_last_n_months` read as `Piwik::getEarliestDateToRearchive()` reads
     * it (`6` or `last6`); 0 means only new data is processed.
     */
    public static function getMonths(): int
    {
        $general = Config::getInstance()->General;
        $value = is_array($general) ? $general['rearchive_reports_in_past_last_n_months'] ?? 0 : 0;

        if (is_string($value)) {
            $value = str_replace('last', '', $value);
        }

        $months = is_numeric($value) ? (int) $value : 0;

        return max(0, $months);
    }
}
