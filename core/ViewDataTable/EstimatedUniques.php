<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ViewDataTable;

use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\SettingsPiwik;

/**
 * Labels the unique visitors and users columns of views whose values may have been estimated.
 *
 * @internal
 */
final class EstimatedUniques
{
    /**
     * Returns the suffix of a unique visitors or users column, eg `_returning` for `nb_uniq_visitors_returning` and
     * `''` for `nb_users`, or null for any other column. VisitFrequency.get and AIAgents.get suffix their columns
     * like this.
     */
    public static function getColumnSuffix(string $columnName): ?string
    {
        if (!preg_match('/^nb_(?:uniq_visitors|users)(_[a-z_]+)?$/', $columnName, $matches)) {
            return null;
        }

        return $matches[1] ?? '';
    }

    /**
     * Returns true if a unique visitors or users column is missing from a row because its period has visits but
     * no count or estimate, as VisitsSummary.get leaves it out. Its value is unknown, not 0.
     *
     * @param array<string, mixed> $rowColumns
     */
    public static function isLeftOut(array $rowColumns, string $columnName): bool
    {
        $suffix = self::getColumnSuffix($columnName);

        return $suffix !== null
            && !isset($rowColumns[$columnName])
            && !empty($rowColumns['nb_visits' . $suffix])
            && SettingsPiwik::isUniqueVisitorsEstimateEnabled();
    }

    /**
     * Labels for the unique visitors and users columns of periods where they are estimated.
     *
     * @return array<string, string>
     */
    public static function getMetricTranslations(): array
    {
        return [
            'nb_uniq_visitors' => Piwik::translate('VisitsSummary_ColumnNbUniqVisitorsEstimated'),
            'nb_users' => Piwik::translate('VisitsSummary_ColumnNbUsersEstimated'),
        ];
    }

    /**
     * Labels the unique visitors and users metrics of report metadata with `$estimated` for periods where they are
     * estimated. Report metadata doesn't read any data, so this follows the setting and the period only.
     *
     * @param array<string, string> $metrics
     * @param array<string, string> $estimated
     * @return array<string, string>
     */
    public static function labelMetadataMetrics(array $metrics, string $period, array $estimated): array
    {
        if (!SettingsPiwik::isUniqueVisitorsEstimated($period)) {
            return $metrics;
        }

        return array_replace($metrics, array_intersect_key($estimated, $metrics));
    }

    /**
     * Labels each unique visitors and users column of a view with `$estimated` when at least one of its periods
     * has the matching `uniques_estimated` flag set, and with `$counted` otherwise.
     *
     * @param array<string, string> $estimated
     * @param array<string, string>|null $counted the default metric translations when not set
     */
    public static function labelFromData(ViewDataTable $view, array $estimated, ?array $counted = null): void
    {
        $view->config->addTranslations($counted ?? array_intersect_key(Metrics::getDefaultMetricTranslations(), $estimated));

        // a priority filter, so it reads the flag before the columns that weren't requested are removed. A graph
        // runs it once per period, so one estimated period labels the column
        $view->config->filters[] = [function (DataTable $table) use ($view, $estimated) {
            $row = $table->getFirstRow();
            if (!$row) {
                return;
            }

            $view->config->addTranslations(array_filter($estimated, function ($column) use ($row) {
                $flag = PluginsArchiver::UNIQUES_ESTIMATED_METRIC . self::getColumnSuffix($column);
                return !empty($row->getColumn($flag));
            }, ARRAY_FILTER_USE_KEY));
        }, [], true];
    }
}
