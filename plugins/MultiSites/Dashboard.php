<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites;

use Piwik\API\DataTablePostProcessor;
use Piwik\API\Request;
use Piwik\API\ResponseBuilder;
use Piwik\NumberFormatter;
use Piwik\DataTable;
use Piwik\DataTable\Row\DataTableSummaryRow;
use Piwik\Site;

/**
 * Fetches and formats the response of `MultiSites.getAll` in a way that it can be used by the All Websites
 * widget. Eg sites are moved into groups if one is assigned, stats are calculated for groups, etc.
 */
class Dashboard
{
    /** @var DataTable */
    private $sitesByGroup;

    /**
     * @var int
     */
    private $numSites = 0;

    /**
     * Array of metrics that will be displayed and will be number formatted
     * @var array
     */
    private $displayedMetricColumns = [
        'nb_visits', 'nb_pageviews', 'hits', 'nb_actions', 'revenue',
        'previous_nb_visits', 'previous_nb_pageviews', 'previous_hits', 'previous_nb_actions', 'previous_revenue',
    ];

    /**
     * @param string $period
     * @param string $date
     * @param string|false $segment
     */
    public function __construct($period, $date, $segment)
    {
        $sites = Request::processRequest('MultiSites.getAll', [
            'period' => $period,
            'date' => $date,
            'segment' => $segment,
            'enhanced' => '1',
            // NOTE: have to select everything since with queued filters disabled some metrics won't be renamed to
            // their display name, and so showColumns will end up removing those.
            'showColumns' => '',
            'disable_queued_filters' => '1',
            'filter_limit' => '-1',
            'filter_offset' => '0',
            'totals' => 0
        ], $default = []);

        $sites->deleteRow(DataTable::ID_SUMMARY_ROW);

        /** @var DataTable $pastData */
        $pastData = $sites->getMetadata('pastData');

        $sites->filter(function (DataTable $table) use ($pastData) {
            $pastRow = null;

            foreach ($table->getRows() as $row) {
                $idSite = $row->getColumn('label');
                $site   = Site::getSite($idSite);
                // we cannot queue label and group as we might need them for search and sorting!
                $row->setColumn('label', $site['name']);
                $row->setMetadata('group', $site['group']);

                if ($pastData) {
                    // if we do not update the pastData labels, the evolution cannot be calculated correctly.
                    $pastRow = $pastData->getRowFromLabel($idSite);
                    if ($pastRow) {
                        $pastRow->setColumn('label', $site['name']);
                    }
                }
            }

            if ($pastData && $pastRow) {
                $pastData->setLabelsHaveChanged();
            }
        });

        $this->setSitesTable($sites);
    }

    public function setSitesTable(DataTable $sites)
    {
        $this->sitesByGroup = $this->moveSitesHavingAGroupIntoSubtables($sites);
        $this->rememberNumberOfSites();
    }

    public function getSites($request, $limit)
    {
        $request['filter_limit']  = $limit;
        $request['filter_offset'] = isset($request['filter_offset']) ? $request['filter_offset'] : 0;

        $this->makeSitesFlatAndApplyGenericFilters($this->sitesByGroup, $request);
        $sites = $this->convertDataTableToArrayAndApplyQueuedFilters($this->sitesByGroup, $request);
        $sites = $this->enrichValues($sites);

        return $sites;
    }

    public function getTotals()
    {
        $totals = [
            'nb_pageviews'          => $this->sitesByGroup->getMetadata('total_nb_pageviews'),
            'nb_visits'             => $this->sitesByGroup->getMetadata('total_nb_visits'),
            'hits'                  => $this->sitesByGroup->getMetadata('total_hits'),
            'nb_actions'            => $this->sitesByGroup->getMetadata('total_nb_actions'),
            'revenue'               => $this->sitesByGroup->getMetadata('total_revenue'),
            'previous_nb_pageviews' => $this->sitesByGroup->getMetadata('previous_total_nb_pageviews'),
            'previous_nb_visits'    => $this->sitesByGroup->getMetadata('previous_total_nb_visits'),
            'previous_hits'         => $this->sitesByGroup->getMetadata('previous_total_hits'),
            'previous_nb_actions'   => $this->sitesByGroup->getMetadata('previous_total_nb_actions'),
            'previous_revenue'      => $this->sitesByGroup->getMetadata('previous_total_revenue'),
        ];
        $this->formatMetrics($totals);
        return $totals;
    }

    private function formatMetrics(&$metrics)
    {
        if (\Piwik\Request::fromRequest()->getStringParameter('format_metrics', '0') === '0') {
            return; // do not format metrics if requires unformatted
        }

        $formatter = NumberFormatter::getInstance();
        foreach ($metrics as $metricName => &$value) {
            if (in_array($metricName, $this->displayedMetricColumns)) {
                if (strpos($metricName, 'revenue') !== false) {
                    $currency = isset($metrics['idsite']) ? Site::getCurrencySymbolFor($metrics['idsite']) : '';
                    $value  = $formatter->formatCurrency($value, $currency);
                    continue;
                }
                $value = $formatter->format($value);
            }
        }
    }


    public function getNumSites()
    {
        return $this->numSites;
    }

    public function search($pattern)
    {
        $this->nestedSearch($this->sitesByGroup, $pattern);
        $this->rememberNumberOfSites();
    }

    private function rememberNumberOfSites()
    {
        $this->numSites = $this->sitesByGroup->getRowsCountRecursive();
    }

    private function nestedSearch(DataTable $sitesByGroup, $pattern)
    {
        foreach ($sitesByGroup->getRows() as $index => $site) {
            $label = strtolower($site->getColumn('label'));
            $labelMatches = false !== strpos($label, $pattern);

            if ($site->getMetadata('isGroup')) {
                $subtable = $site->getSubtable();
                $this->nestedSearch($subtable, $pattern);

                if (!$labelMatches && !$subtable->getRowsCount()) {
                    // we keep the group if at least one site within the group matches the pattern
                    $sitesByGroup->deleteRow($index);
                }
            } elseif (!$labelMatches) {
                $group = $site->getMetadata('group');

                if (!$group || false === strpos(strtolower($group), $pattern)) {
                    $sitesByGroup->deleteRow($index);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getLastDate()
    {
        $lastPeriod = $this->sitesByGroup->getMetadata('last_period_date');

        if (!empty($lastPeriod)) {
            $lastPeriod = $lastPeriod->toString();
        } else {
            $lastPeriod = '';
        }

        return $lastPeriod;
    }

    private function convertDataTableToArrayAndApplyQueuedFilters(DataTable $table, $request)
    {
        $request['serialize'] = 0;
        $request['expanded'] = 0;
        $request['totals'] = 0;
        $request['format_metrics'] = \Piwik\Request::fromRequest()->getStringParameter('format_metrics', '1');
        $request['disable_generic_filters'] = 1;

        $responseBuilder = new ResponseBuilder('json', $request);
        return json_decode($responseBuilder->getResponse($table, 'MultiSites', 'getAll'), true);
    }

    private function moveSitesHavingAGroupIntoSubtables(DataTable $sites)
    {
        /** @var DataTableSummaryRow[] $groups */
        $groups = [];

        $sitesByGroup = $this->makeCloneOfDataTableSites($sites);
        $sitesByGroup->enableRecursiveFilters(); // we need to make sure filters get applied to subtables (groups)

        foreach ($sites->getRows() as $site) {
            $group = $site->getMetadata('group');

            if (!empty($group) && !array_key_exists($group, $groups)) {
                $row = new DataTableSummaryRow();
                $row->setColumn('label', $group);
                $row->setMetadata('isGroup', 1);
                $row->setSubtable($this->createGroupSubtable($sites));
                $sitesByGroup->addRow($row);

                $groups[$group] = $row;
            }

            if (!empty($group)) {
                $groups[$group]->getSubtable()->addRow($site);
            } else {
                $sitesByGroup->addRow($site);
            }
        }

        foreach ($groups as $group) {
            // we need to recalculate as long as all rows are there, as soon as some rows are removed
            // we can no longer recalculate the correct value. We might even calculate values for groups
            // that are not returned. If this becomes a problem we need to keep a copy of this to recalculate
            // only actual returned groups.
            $group->recalculate();
        }

        return $sitesByGroup;
    }

    private function createGroupSubtable(DataTable $sites)
    {
        $table = new DataTable();
        $processedMetrics = $sites->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);
        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $processedMetrics);

        return $table;
    }

    private function makeCloneOfDataTableSites(DataTable $sites)
    {
        $sitesByGroup = $sites->getEmptyClone(true);
        // we handle them ourselves for faster performance etc. This way we also avoid to apply them twice.
        $sitesByGroup->disableFilter('ColumnCallbackReplace');
        $sitesByGroup->disableFilter('MetadataCallbackAddMetadata');

        return $sitesByGroup;
    }

    /**
     * Makes sure to not have any subtables anymore.
     *
     * So if $table is
     * array(
     *    site1
     *    site2
     *        subtable => site3
     *                    site4
     *                    site5
     *    site6
     *    site7
     * )
     *
     * it will return
     *
     * array(
     *    site1
     *    site2
     *    site3
     *    site4
     *    site5
     *    site6
     *    site7
     * )
     *
     * in a sorted order
     *
     * @param DataTable $table
     * @param array $request
     */
    private function makeSitesFlatAndApplyGenericFilters(DataTable $table, $request)
    {
        // we handle limit here as we have to apply sort filter, then make sites flat, then apply limit filter.
        $filterOffset = $request['filter_offset'];
        $filterLimit  = $request['filter_limit'];
        unset($request['filter_offset']);
        unset($request['filter_limit']);

        // make sure no limit filter is applied, we will do this manually
        $table->disableFilter('Limit');

        // this will apply the sort filter
        /** @var DataTable $table */
        $genericFilter = new DataTablePostProcessor('MultiSites', 'getAll', $request);
        $table = $genericFilter->applyGenericFilters($table);

        // make sure from now on the sites will be no longer sorted, they were already sorted
        $table->disableFilter('Sort');

        // make sites flat and limit
        $table->filter('Piwik\Plugins\MultiSites\DataTable\Filter\NestedSitesLimiter', [$filterOffset, $filterLimit]);
    }

    private function enrichValues($sites)
    {
        foreach ($sites as &$site) {
            if (!isset($site['idsite'])) {
                continue;
            }

            $site['main_url'] = Site::getMainUrlFor($site['idsite']);

            $this->formatMetrics($site);
        }

        return $sites;
    }
}
