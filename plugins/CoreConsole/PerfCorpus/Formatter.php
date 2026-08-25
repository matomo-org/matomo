<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * Human-readable numbers for the dry-run estimate and the live progress display. Deliberately
 * local rather than going through Piwik\Metrics\Formatter: these are operator-facing terminal
 * figures, not localised report values, and they must stay readable in a fixed-width column.
 */
class Formatter
{
    /**
     * 1,819,500,000
     */
    public static function rows(int $count): string
    {
        return number_format($count);
    }

    /**
     * 1.82G - for progress lines where the full number will not fit.
     */
    public static function shortRows(int $count): string
    {
        foreach ([['G', 1000000000], ['M', 1000000], ['k', 1000]] as [$suffix, $unit]) {
            if ($count >= $unit) {
                $scaled = $count / $unit;
                // Three significant figures, so the column width stays put: 150k, 72.0M, 1.91G.
                $decimals = $scaled >= 100 ? 0 : ($scaled >= 10 ? 1 : 2);

                return sprintf('%.' . $decimals . 'f%s', $scaled, $suffix);
            }
        }

        return (string) $count;
    }

    /**
     * 501 GB. Uses decimal units, matching how disk and instance sizes are quoted by AWS.
     */
    public static function bytes(int $bytes): string
    {
        foreach ([['TB', 1000000000000], ['GB', 1000000000], ['MB', 1000000], ['kB', 1000]] as [$suffix, $unit]) {
            if ($bytes >= $unit) {
                return sprintf('%.1f %s', $bytes / $unit, $suffix);
            }
        }

        return $bytes . ' B';
    }

    /**
     * 4h 12m - always the two largest units, so the width stays stable.
     */
    public static function duration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }

        if ($seconds < 3600) {
            return sprintf('%dm %02ds', intdiv($seconds, 60), $seconds % 60);
        }

        if ($seconds < 86400) {
            return sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
        }

        return sprintf('%dd %02dh', intdiv($seconds, 86400), intdiv($seconds % 86400, 3600));
    }

    /**
     * 184k rows/s
     */
    public static function rate(float $rowsPerSecond): string
    {
        return self::shortRows((int) round($rowsPerSecond)) . ' rows/s';
    }

    /**
     * A fixed-width bar: [====------] The width is the number of cells, not characters printed.
     */
    public static function bar(float $fraction, int $width = 20): string
    {
        $fraction = max(0.0, min(1.0, $fraction));
        $filled = (int) round($fraction * $width);

        return str_repeat('#', $filled) . str_repeat('.', $width - $filled);
    }

    public static function percent(float $fraction): string
    {
        return sprintf('%5.1f%%', max(0.0, min(1.0, $fraction)) * 100);
    }
}
