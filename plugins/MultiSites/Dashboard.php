<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites;

use Piwik\API\ResponseBuilder;
use Piwik\Config;
use Piwik\Metrics\Formatter;
use Piwik\Period;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\DataTable\Row\DataTableSummaryRow;
use Piwik\Plugins\API\ProcessedReport;
use Piwik\Site;
use Piwik\View;

/**
 * Fetches and formats the response of `MultiSites.getAll` in a way that it can be used by the All Websites AngularJS
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
     * @param string $period
     * @param string $date
     * @param string|false $segment
     */
    public function __construct($period, $date, $segment)
    {
        $sites = API::getInstance()->getAll($period, $date, $segment, $_restrictSitesToLogin = false,
                                            $enhanced = true, $searchTerm = false,
                                            $showColumns = array('nb_visits', 'nb_pageviews', 'revenue'));
        $sites->deleteRow(DataTable::ID_SUMMARY_ROW);

        /** @var DataTable $pastData */
        $pastData = $sites->getMetadata('pastData');

        $sites->filter(function (DataTable $table) use ($pastData) {
            foreach ($table->getRows() as $row) {
                $idSite = $row->getColumn('label');
                $site   = Site::getSite($idSite);
                // we cannot queue label and group as we might need them for search and sorting!
                $row->setColumn('label', $site['name']);
                $row->setMetadata('group', $site['group']);

                // if we do not update the pastData labels, the evolution cannot be calculated correctly.
                $pastRow = $pastData->getRowFromLabel($idSite);
                if ($pastRow) {
                    $pastRow->setColumn('label', $site['name']);
                }
            }

            $pastData->setLabelsHaveChanged();
        });

        $this->setSitesTable($sites);
    }

    public function setSitesTable(DataTable $sites)
    {
        $this->numSites     = $sites->getRowsCount();
        $this->sitesByGroup = $this->moveSitesHavingAGroupIntoSubtables($sites);
    }

    public function getSites($request, $limit)
    {
        $request['filter_limit'] = $limit;

        $sitesExpanded = $this->convertDataTableToArrayAndApplyFilters($this->sitesByGroup, $request);
        $sitesFlat     = $this->makeSitesFlat($sitesExpanded);
        $sitesFlat     = $this->applyLimitIfNeeded($sitesFlat, $limit);
        $sitesFlat     = $this->enrichValues($sitesFlat);

        return $sitesFlat;
    }

    public function getTotals()
    {
        return array(
            'nb_pageviews'       => $this->sitesByGroup->getMetadata('total_nb_pageviews'),
            'nb_visits'          => $this->sitesByGroup->getMetadata('total_nb_visits'),
            'revenue'            => $this->sitesByGroup->getMetadata('total_revenue'),
            'nb_visits_lastdate' => $this->sitesByGroup->getMetadata('total_nb_visits_lastdate') ? : 0,
        );
    }

    public function getNumSites()
    {
        return $this->numSites;
    }

    public function search($pattern)
    {
        $this->nestedSearch($this->sitesByGroup, $pattern);

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

    private function convertDataTableToArrayAndApplyFilters(DataTable $table, $request)
    {
        $request['serialize'] = 0;
        $request['expanded'] = 1;
        $request['totals'] = 0;
        $request['format_metrics'] = 1;

        // filter_sort_column does not work correctly is a bug in MultiSites.getAll
        if (!empty($request['filter_sort_column']) && $request['filter_sort_column'] === 'nb_pageviews') {
            $request['filter_sort_column'] = 'Actions_nb_pageviews';
        } elseif (!empty($request['filter_sort_column']) && $request['filter_sort_column'] === 'revenue') {
            $request['filter_sort_column'] = 'Goal_revenue';
        }

        $responseBuilder = new ResponseBuilder('php', $request);
        $rows = $responseBuilder->getResponse($table, 'MultiSites', 'getAll');

        return $rows;
    }

    private function moveSitesHavingAGroupIntoSubtables(DataTable $sites)
    {
        /** @var DataTableSummaryRow[] $groups */
        $groups = array();

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
     * So if $sites is
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
     * @param $sites
     * @return array
     */
    private function makeSitesFlat($sites)
    {
        $flatSites = array();

        foreach ($sites as $site) {
            if (!empty($site['subtable'])) {
                if (isset($site['idsubdatatable'])) {
                    unset($site['idsubdatatable']);
                }

                $subtable = $site['subtable'];
                unset($site['subtable']);
                $flatSites[] = $site;
                foreach ($subtable as $siteWithinGroup) {
                    $flatSites[] = $siteWithinGroup;
                }
            } else {
                $flatSites[] = $site;
            }
        }

        return $flatSites;
    }

    private function applyLimitIfNeeded($sites, $limit)
    {
        // why do we need to apply a limit again? because we made sitesFlat and it may contain many more sites now
        if ($limit > 0) {
            $sites = array_slice($sites, 0, $limit);
        }

        return $sites;
    }

    private function enrichValues($sites)
    {
        $formatter = new Formatter();

        foreach ($sites as &$site) {
            if (!isset($site['idsite'])) {
                continue;
            }

            if (isset($site['revenue'])) {
                $site['revenue']  = $formatter->getPrettyMoney($site['revenue'], $site['idsite']);
            }
            $site['main_url'] = Site::getMainUrlFor($site['idsite']);
        }

        return $sites;
    }
}
