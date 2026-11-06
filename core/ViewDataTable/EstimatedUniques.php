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
     * Labels each unique visitors and users column of a view with `$estimated` when its data was estimated, ie the
     * archive of at least one of its periods has the matching `uniques_estimated` flag set, and with `$counted`
     * otherwise. Days, and weeks or months archived before the estimate was switched on, keep the plain labels.
     *
     * @param array<string, string> $estimated
     * @param array<string, string>|null $counted the default metric translations when not set
     */
    public static function labelFromData(ViewDataTable $view, array $estimated, ?array $counted = null): void
    {
        $view->config->addTranslations($counted ?? array_intersect_key(Metrics::getDefaultMetricTranslations(), $estimated));

        // a priority filter, as it has to run before the flag is removed from the columns that weren't requested
        // a graph runs this once per period, so a column is labelled estimated when any of its periods was
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
