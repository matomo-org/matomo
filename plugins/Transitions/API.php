<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Transitions;

use Exception;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataArray;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Db;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Live\Model;
use Piwik\Plugins\Transitions\Model as TransitionsModel;
use Piwik\Plugins\Transitions\PreprocessedPages\Dao as PreprocessedPagesDao;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service as PreprocessedPagesService;
use Piwik\Plugins\Transitions\PreprocessedPages\StoredRecordsLoader;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;
use Piwik\Site;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;
use Piwik\Tracker\TableLogAction;

/**
 * Provides API methods for transition reports around a specific page action, and manages the pages whose
 * transition report is pre-processed.
 *
 * @method static \Piwik\Plugins\Transitions\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns transition data for the specified page title.
     *
     * @param string $pageTitle The page title to analyze.
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the
     *                                                    period containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|string $limitBeforeGrouping Maximum number of transition rows to keep before grouping the
     *                                        remainder.
     * @return array Transition metrics and related referrer/action tables for the requested page title.
     */
    public function getTransitionsForPageTitle(string $pageTitle, int $idSite, string $period, string $date, $segment = false, $limitBeforeGrouping = 0)
    {
        return $this->getTransitionsForAction($pageTitle, 'title', $idSite, $period, $date, $segment, $limitBeforeGrouping);
    }

    /**
     * Returns transition data for the specified page URL.
     *
     * @param string $pageUrl The page URL to analyze.
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the
     *                                                    period containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|string $limitBeforeGrouping Maximum number of transition rows to keep before grouping the
     *                                        remainder.
     * @return array Transition metrics and related referrer/action tables for the requested page URL. When the
     *               report is served from the pre-processed data of the page, it also has `preprocessed` = 1 and
     *               `preprocessedAt`, the UTC time (`YYYY-MM-DD HH:MM:SS`) the oldest data used was processed.
     */
    public function getTransitionsForPageUrl(string $pageUrl, int $idSite, string $period, string $date, $segment = false, $limitBeforeGrouping = 0)
    {
        return $this->getTransitionsForAction($pageUrl, 'url', $idSite, $period, $date, $segment, $limitBeforeGrouping);
    }

    /**
     * Returns transition data for the specified page action.
     *
     * @param string $actionName The page URL or title to analyze.
     * @param 'url'|'title' $actionType Whether the action name is a URL or title.
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the
     *                                                    period containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|string $limitBeforeGrouping Maximum number of transition rows to keep before grouping the
     *                                        remainder.
     * @param string $parts Comma-separated list of transition report parts to include, or `all`:
     *                      `internalReferrers`, `followingActions`, `followingActionsWithoutLoops` and
     *                      `externalReferrers`. Requested without `internalReferrers`, `followingActions`
     *                      counts reloads of the action as following actions; `followingActionsWithoutLoops`
     *                      leaves them out, as the full report does, and wins if both are requested.
     * @return array Transition metrics and related referrer/action tables for the requested action. When the
     *               report is served from the pre-processed data of a page URL, it also has `preprocessed` = 1 and
     *               `preprocessedAt`, the UTC time (`YYYY-MM-DD HH:MM:SS`) the oldest data used was processed.
     */
    public function getTransitionsForAction(
        string $actionName,
        string $actionType,
        int $idSite,
        string $period,
        string $date,
        $segment = false,
        $limitBeforeGrouping = 0,
        string $parts = 'all'
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $preprocessedReport = $this->getPreprocessedReport($actionName, $actionType, $idSite, $period, $date, $segment, $limitBeforeGrouping, $parts);
        if ($preprocessedReport !== null) {
            return $preprocessedReport;
        }

        if (!$this->isPeriodAllowed($idSite, $period, $date)) {
            throw new Exception('PeriodNotAllowed');
        }

        if ($limitBeforeGrouping && !is_numeric($limitBeforeGrouping)) {
            throw new Exception('limitBeforeGrouping has to be an integer.');
        }
        //convert string to int
        $limitBeforeGrouping = (int)$limitBeforeGrouping;

        // get idaction of the requested action
        $idaction = $this->deriveIdAction($actionName, $actionType);
        if ($idaction === null) {
            throw new Exception('NoDataForAction');
        }

        // prepare log aggregator
        $site = new Site($idSite);
        $period = Period\Factory::build($period, $date);
        $segment = new Segment(
            $segment ?: '',
            [$idSite],
            $period->getDateTimeStart()->setTimezone($site->getTimezone()),
            $period->getDateTimeEnd()->setTimezone($site->getTimezone())
        );
        $params = new ArchiveProcessor\Parameters($site, $period, $segment);
        $logAggregator = new LogAggregator($params);

        // prepare the report
        $report = [
            'date' => Period\Factory::build($period->getLabel(), $date)->getLocalizedShortString(),
        ];

        try {
            $requestedParts = $this->parseParts($parts);
            if ($requestedParts['internalReferrers']) {
                $this->addInternalReferrers($logAggregator, $report, $idaction, $actionType, $limitBeforeGrouping);
            }
            if ($requestedParts['followingActions']) {
                $this->addFollowingActions($logAggregator, $report, $idaction, $actionType, $limitBeforeGrouping, $requestedParts['includeLoops']);
            }
            if ($requestedParts['externalReferrers']) {
                $this->addExternalReferrers($logAggregator, $report, $idaction, $actionType, $limitBeforeGrouping);
            }

            // derive the number of exits from the other metrics
            if ($requestedParts['all']) {
                $report['pageMetrics']['exits'] = $report['pageMetrics']['pageviews']
                    - $this->getTotalTransitionsToFollowingActions()
                    - $report['pageMetrics']['loops'];
            }
        } catch (\Exception $e) {
            Model::handleMaxExecutionTimeError(
                Db::getReader(),
                $e,
                $segment->getString(),
                $period->getDateStart(),
                $period->getDateEnd(),
                0,
                GeneralConfig::getConfigValue('live_query_max_execution_time'),
                ['method' => 'Transitions.getTransitionsForAction', 'actionName' => $actionName, 'actionType' => $actionType]
            );
            throw $e;
        }

        $this->replaceColumnNames($report);

        return $report;
    }

    /**
     * Builds the report from the records stored for a pre-processed page, or returns null when they don't cover the
     * request, which is then answered from the logs. `max_period_allowed` doesn't apply to stored records that cover
     * the period, or are refreshed; beyond it, nothing is summed from shorter periods or built.
     *
     * The records are found by the page URL, without `log_action`, so they are served also for a page that was removed
     * or whose action was deleted as unused. A URL that the tracker's normalization changes now, for example by removing
     * query parameters or a fragment, or by truncating it, is answered from the logs, as it may have been tracked as
     * another page.
     *
     * @param string|null|false $segment
     * @param int|string $limitBeforeGrouping
     * @return array<string, mixed>|null
     */
    private function getPreprocessedReport(
        string $actionName,
        string $actionType,
        int $idSite,
        string $period,
        string $date,
        $segment,
        $limitBeforeGrouping,
        string $parts
    ): ?array {
        // page titles are not pre-processed; invalid limits are reported by the log query
        if ($actionType !== 'url' || ($limitBeforeGrouping && !is_numeric($limitBeforeGrouping))) {
            return null;
        }

        // reports served from the stored lists show at most the configured rows; an invalid setting only fails
        // requests that stored records would answer
        $limit = (int) $limitBeforeGrouping ?: $this->limitBeforeGrouping;
        try {
            $maxRowsServed = Records::getMaxRowsServed($idSite);
            $configError = null;
        } catch (\Exception $e) {
            $maxRowsServed = $limit;
            $configError = $e;
        }
        if ($limit < 1 || $limit > $maxRowsServed) {
            return null;
        }

        $requestedParts = $this->parseParts($parts);

        $tableParts = [];
        if ($requestedParts['internalReferrers']) {
            $tableParts = [Records::PREVIOUS_PAGES, Records::PREVIOUS_SITE_SEARCHES];
        }
        if ($requestedParts['followingActions']) {
            $tableParts = array_merge($tableParts, array_values(StaticContainer::get(TransitionsModel::class)->getFollowingActionTypes('url')));
        }
        if ($requestedParts['externalReferrers']) {
            $tableParts[] = Records::REFERRERS;
        }

        if (empty($tableParts)) {
            return null;
        }

        try {
            $site = new Site($idSite);
            $periodObject = Period\Factory::build($period, $date);
            $segmentObject = new Segment(
                $segment ?: '',
                [$idSite],
                $periodObject->getDateTimeStart()->setTimezone($site->getTimezone()),
                $periodObject->getDateTimeEnd()->setTimezone($site->getTimezone())
            );
            $dateLabel = Period\Factory::build($periodObject->getLabel(), $date)->getLocalizedShortString();
            $isPeriodAllowed = $this->isPeriodAllowed($idSite, $period, $date);
        } catch (\Exception $e) {
            // invalid input is reported by the log query, after the period check
            return null;
        }

        $page = StaticContainer::get(PreprocessedPagesService::class)->getPageForReport($idSite, $actionName);
        if ($page === null) {
            return null;
        }

        $records = StaticContainer::get(StoredRecordsLoader::class)->load($idSite, $periodObject, $segmentObject, $page['idpage'], $tableParts, $isPeriodAllowed);
        if ($records === null) {
            return null;
        }

        if ($configError !== null) {
            throw $configError;
        }

        // as the log query, for a URL that isn't tracked, whichever parts are requested
        if ($records->getNumeric(Records::PAGEVIEWS) == 0 && $this->deriveIdAction($actionName, $actionType) === null) {
            throw new Exception('NoDataForAction');
        }

        $report = ['date' => $dateLabel];

        if ($requestedParts['internalReferrers']) {
            if ($records->getNumeric(Records::PAGEVIEWS) == 0) {
                throw new Exception('NoDataForAction');
            }

            $report['previousPages'] = $this->makeStoredActionsTable($records->getTable(Records::PREVIOUS_PAGES), $limit);
            $report['previousSiteSearches'] = $this->makeStoredActionsTable($records->getTable(Records::PREVIOUS_SITE_SEARCHES), $limit);
            $report['pageMetrics']['loops'] = $records->getNumeric(Records::LOOPS);
            $report['pageMetrics']['pageviews'] = $records->getNumeric(Records::PAGEVIEWS);
        }

        if ($requestedParts['followingActions']) {
            foreach (StaticContainer::get(TransitionsModel::class)->getFollowingActionTypes('url') as $type => $list) {
                // the stored following pages leave out reloads, which the log query counts as the page itself
                $extraRows = [];
                if ($requestedParts['includeLoops'] && $type == Action::TYPE_PAGE_URL && $records->getNumeric(Records::LOOPS) > 0) {
                    $idaction = $this->deriveIdAction($actionName, $actionType);
                    $extraRows[] = [
                        // the name the log query labels it with; for a deleted action, the page's URL as the tracker
                        // stores it
                        'label' => ($idaction === null ? null : StaticContainer::get(PreprocessedPagesDao::class)->getActionName($idaction))
                            ?? PageUrl::normalizeUrl($page['url'])['url'],
                        'count' => $records->getNumeric(Records::LOOPS),
                    ];
                }

                $report[$list] = $this->makeStoredActionsTable($records->getTable($list), $limit, $extraRows);
            }
        }

        if ($requestedParts['externalReferrers']) {
            $this->addReferrers($report, $this->makeStoredReferrersTable($records->getTable(Records::REFERRERS), $limit));
        }

        if ($requestedParts['all']) {
            $report['pageMetrics']['exits'] = $records->getNumeric(Records::EXITS);
        }

        $this->replaceColumnNames($report);

        $report['preprocessed'] = 1;
        $report['preprocessedAt'] = $records->getTsArchived();

        return $report;
    }

    /**
     * Which parts of the report the `parts` parameter requests. Following actions requested without internal
     * referrers count reloads of the action, unless `followingActionsWithoutLoops` is requested.
     *
     * @return array{all: bool, internalReferrers: bool, followingActions: bool, includeLoops: bool, externalReferrers: bool}
     */
    private function parseParts(string $parts): array
    {
        $all = $parts == 'all';
        $partsArray = explode(',', $parts);
        $withoutLoops = in_array('followingActionsWithoutLoops', $partsArray);

        return [
            'all'               => $all,
            'internalReferrers' => $all || in_array('internalReferrers', $partsArray),
            'followingActions'  => $all || $withoutLoops || in_array('followingActions', $partsArray),
            'includeLoops'      => !$withoutLoops && !$all && !in_array('internalReferrers', $partsArray),
            'externalReferrers' => $all || in_array('externalReferrers', $partsArray),
        ];
    }

    /**
     * A stored list as the log query returns it: the top rows, then `Others` with the rest.
     *
     * @param list<array{label: string|int, count: int}> $extraRows
     */
    private function makeStoredActionsTable(DataTable $stored, int $limit, array $extraRows = []): DataTable
    {
        $table = new DataTable();
        foreach ($this->getTopStoredRows($stored, Metrics::INDEX_NB_ACTIONS, $limit, $extraRows) as $row) {
            $table->addRow(new Row([Row::COLUMNS => [
                'label'                   => $row['label'],
                Metrics::INDEX_NB_ACTIONS => $row['count'],
            ]]));
        }

        return $table;
    }

    /**
     * The stored referrers as {@link queryExternalReferrers()} returns them.
     */
    private function makeStoredReferrersTable(DataTable $stored, int $limit): DataTable
    {
        $table = new DataTable();
        foreach (TransitionsModel::REFERRER_TYPES as $referrerType) {
            $storedRow = $stored->getRowFromLabel((string) $referrerType);
            if (!$storedRow) {
                continue;
            }

            $visits = $this->getStoredCount($storedRow, Metrics::INDEX_NB_VISITS);
            $row = $table->addRow(new Row([Row::COLUMNS => [
                'label'                  => $referrerType,
                Metrics::INDEX_NB_VISITS => $visits,
            ]]));

            $storedReferrers = $storedRow->getSubtable();
            if (!$storedReferrers) {
                continue;
            }

            // referrers without a name are not stored, only counted in the visits of their type
            $unnamedVisits = $visits;
            foreach ($storedReferrers->getRows() as $storedReferrer) {
                $unnamedVisits -= $this->getStoredCount($storedReferrer, Metrics::INDEX_NB_VISITS);
            }

            $referrers = new DataTable();
            foreach ($this->getTopStoredRows($storedReferrers, Metrics::INDEX_NB_VISITS, $limit, [], $unnamedVisits) as $referrer) {
                $label = $referrer['label'];
                if ($referrerType == Common::REFERRER_TYPE_SEARCH_ENGINE && $label === Records::UNKNOWN_SEARCH_ENGINE_LABEL) {
                    $label = Piwik::translate('General_Unknown');
                }

                // the log query returns these counts as strings
                $referrers->addRow(new Row([Row::COLUMNS => [
                    'label'                  => $label,
                    Metrics::INDEX_NB_VISITS => (string) $referrer['count'],
                ]]));
            }
            $row->setSubtable($referrers);
        }

        return $table;
    }

    /**
     * The rows of a stored list with the highest counts, and of equal counts the lowest labels, as the ranking query
     * of the log query keeps them: at most `$limit` rows, then `Others` with the rest and the stored summary row.
     *
     * @param list<array{label: string|int, count: int}> $extraRows rows to rank with the stored ones
     * @param int $unnamedCount count of rows without a label, which are not stored: ranked as one row, which the log
     *                          query leaves out of the top rows and counts in `Others` below them
     * @return list<array{label: string|int, count: int}>
     */
    private function getTopStoredRows(DataTable $stored, int $column, int $limit, array $extraRows = [], int $unnamedCount = 0): array
    {
        $rows = [];
        foreach ($extraRows as $row) {
            $rows[] = $row + ['unnamed' => false];
        }
        foreach ($stored->getRowsWithoutSummaryRow() as $row) {
            $label = $row->getColumn('label');
            $rows[] = [
                'label' => is_int($label) ? $label : (is_scalar($label) ? (string) $label : ''),
                'count' => $this->getStoredCount($row, $column),
                'unnamed' => false,
            ];
        }
        if ($unnamedCount > 0) {
            $rows[] = ['label' => '', 'count' => $unnamedCount, 'unnamed' => true];
        }

        usort($rows, function (array $a, array $b): int {
            return $b['count'] <=> $a['count'] ?: strcmp((string) $a['label'], (string) $b['label']);
        });

        $others = null;
        $summaryRow = $stored->getSummaryRow();
        if ($summaryRow) {
            $others = $this->getStoredCount($summaryRow, $column);
        }
        foreach (array_slice($rows, $limit) as $row) {
            $others = ($others ?? 0) + $row['count'];
        }

        $topRows = [];
        foreach (array_slice($rows, 0, $limit) as $row) {
            if (!$row['unnamed']) {
                $topRows[] = ['label' => $row['label'], 'count' => $row['count']];
            }
        }
        if ($others !== null) {
            $topRows[] = ['label' => TransitionsModel::OTHERS_LABEL, 'count' => $others];
        }

        return $topRows;
    }

    private function getStoredCount(Row $row, int $column): int
    {
        $count = $row->getColumn($column);

        return is_numeric($count) ? (int) $count : 0;
    }

    /**
     * Replaces the count column of the action lists by `referrals`.
     *
     * @param array<string, mixed> $report
     */
    private function replaceColumnNames(array &$report): void
    {
        $reportNames = [
            'previousPages'         => true,
            'previousSiteSearches'  => false,
            'followingPages'        => true,
            'followingSiteSearches' => false,
            'outlinks'              => true,
            'downloads'             => true,
        ];
        foreach ($reportNames as $reportName => $replaceLabel) {
            if (isset($report[$reportName])) {
                $columnNames = [Metrics::INDEX_NB_ACTIONS => 'referrals'];
                if ($replaceLabel) {
                    $columnNames[Metrics::INDEX_NB_ACTIONS] = 'referrals';
                }
                $report[$reportName]->filter('ReplaceColumnNames', [$columnNames]);
            }
        }
    }

    /**
     * Returns the page URLs of a website whose Transitions report is pre-processed during archiving.
     * Requires admin access to the website.
     *
     * @param int $idSite The numeric ID of the website.
     * @return list<array{idpage: int, url: string, created_date: string, created_by: string|null, created_by_superuser: bool}>
     *         The selected pages, newest first. `created_date` is the UTC time the page was added
     *         (`YYYY-MM-DD HH:MM:SS`), `created_by` the login that added it, or null once that user has been deleted,
     *         and for users without super user access when a super user added it, which `created_by_superuser` tells.
     */
    public function getPreprocessedPages(int $idSite): array
    {
        Piwik::checkUserHasAdminAccess($idSite);
        new Site($idSite);

        return StaticContainer::get(PreprocessedPagesService::class)->getPages($idSite);
    }

    /**
     * Adds a page URL whose Transitions report is pre-processed during archiving. Requires admin access to the website.
     *
     * The next `core:archive` runs process its data of the past months set by
     * `[General] rearchive_reports_in_past_last_n_months`. Without cron archiving, past data is not processed.
     *
     * @param int $idSite The numeric ID of the website.
     * @param string $pageUrl The page URL, starting with http:// or https://, at most 4096 characters. Protocol and
     *                        `www.` variants, and query parameters the website excludes, count as the same page. It is
     *                        stored as the tracker stores page URLs, truncated to `[Tracker] page_maximum_length`.
     * @return array{idpage: int, url: string, created_date: string, created_by: string|null, created_by_superuser: bool}
     *         The added page, as returned by `getPreprocessedPages`.
     */
    public function addPreprocessedPage(int $idSite, string $pageUrl): array
    {
        Piwik::checkUserHasAdminAccess($idSite);
        new Site($idSite);

        return StaticContainer::get(PreprocessedPagesService::class)->addPage($idSite, $pageUrl);
    }

    /**
     * Removes a page from the pre-processed pages of a website. Data already processed for it stays available.
     * Fails with a not-found error when the page is not selected for this website. Requires admin access to the website.
     *
     * @param int $idSite The numeric ID of the website.
     * @param int $idPage The ID of the page, as returned by `getPreprocessedPages`.
     */
    public function removePreprocessedPage(int $idSite, int $idPage): void
    {
        Piwik::checkUserHasAdminAccess($idSite);

        StaticContainer::get(PreprocessedPagesService::class)->removePage($idSite, $idPage);
    }

    /**
     * Returns whether a page URL is selected for pre-processed Transitions, with the website's usage of its limit.
     * The URL is matched as `addPreprocessedPage` matches duplicates. Requires admin access to the website.
     *
     * @param int $idSite The numeric ID of the website.
     * @param string $pageUrl The page URL. A URL that `addPreprocessedPage` would reject is reported as not selected.
     * @return array{selected: bool, idPage: int|null, used: int, limit: int, rearchiveMonths: int} `idPage` is the ID
     *         to pass to `removePreprocessedPage`, or null when the page is not selected. `used` is the number of
     *         selected pages and `limit` the maximum for the website. `rearchiveMonths` is how many past months of
     *         data `core:archive` processes for an added page; 0 means only new data, as without cron archiving.
     */
    public function getPreprocessedPageStatus(int $idSite, string $pageUrl): array
    {
        Piwik::checkUserHasAdminAccess($idSite);
        new Site($idSite);

        return StaticContainer::get(PreprocessedPagesService::class)->getPageStatus($idSite, $pageUrl);
    }

    /**
     * Derive the action ID from the request action name and type.
     */
    private function deriveIdAction(string $actionName, string $actionType): ?int
    {
        switch ($actionType) {
            case 'url':
                $originalActionName = $actionName;
                $actionName = Common::unsanitizeInputValue($actionName);

                $id = TableLogAction::getIdActionFromSegment($actionName, 'idaction_url', SegmentExpression::MATCH_EQUAL, 'pageUrl');

                if (!is_numeric($id) || $id < 0) {
                    // an example where this is needed is urls containing < or >
                    $actionName = $originalActionName;
                    $id = TableLogAction::getIdActionFromSegment($actionName, 'idaction_url', SegmentExpression::MATCH_EQUAL, 'pageUrl');
                }

                return is_numeric($id) && $id > 0 ? (int)$id : null;

            case 'title':
                $id = TableLogAction::getIdActionFromSegment($actionName, 'idaction_name', SegmentExpression::MATCH_EQUAL, 'pageTitle');

                if (!is_numeric($id) || $id < 0) {
                    $unknown = ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_TITLE);
                    if (trim($actionName) == trim($unknown)) {
                        return 0;
                    }
                }

                return is_numeric($id) && $id > 0 ? (int)$id : null;

            default:
                throw new Exception('Unknown action type');
        }
    }

    /**
     * Add the internal referrers to the report:
     * previous pages and previous site searches
     *
     * @param array<string, mixed> $report
     * @throws Exception
     */
    private function addInternalReferrers(LogAggregator $logAggregator, array &$report, int $idaction, string $actionType, int $limitBeforeGrouping): void
    {
        $data = $this->queryInternalReferrers($idaction, $actionType, $logAggregator, $limitBeforeGrouping);

        if ($data['pageviews'] == 0) {
            throw new Exception('NoDataForAction');
        }

        $report['previousPages'] = & $data['previousPages'];
        $report['previousSiteSearches'] = & $data['previousSiteSearches'];
        $report['pageMetrics']['loops'] = $data['loops'];
        $report['pageMetrics']['pageviews'] = $data['pageviews'];
    }

    /**
     * Add the following actions to the report:
     * following pages, downloads, outlinks
     *
     * @param array<string, mixed> $report
     */
    private function addFollowingActions(LogAggregator $logAggregator, array &$report, int $idaction, string $actionType, int $limitBeforeGrouping = 0, bool $includeLoops = false): void
    {

        $data = $this->queryFollowingActions(
            $idaction,
            $actionType,
            $logAggregator,
            $limitBeforeGrouping,
            $includeLoops
        );

        foreach ($data as $tableName => $table) {
            $report[$tableName] = $table;
        }
    }

    /**
     * Get information about the following actions (following pages, site searches, outlinks, downloads)
     *
     * @return array<string, DataTable>
     */
    protected function queryFollowingActions(
        int $idaction,
        string $actionType,
        LogAggregator $logAggregator,
        int $limitBeforeGrouping = 0,
        bool $includeLoops = false
    ): array {
        $model = StaticContainer::get(TransitionsModel::class);
        $types = $model->getFollowingActionTypes($actionType);
        $data = $model->queryFollowingActions(
            $logAggregator,
            $idaction,
            $actionType,
            $limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping,
            $includeLoops,
            $this->getLiveQueryTimeLimit()
        );

        $dataTables = $this->makeDataTablesFollowingActions($types, $data);

        return $dataTables;
    }

    /**
     * Get information about external referrers (i.e. search engines, websites & campaigns)
     *
     * @throws Exception
     */
    protected function queryExternalReferrers(int $idaction, string $actionType, LogAggregator $logAggregator, int $limitBeforeGrouping = 0): DataTable
    {

        $data = StaticContainer::get(TransitionsModel::class)->queryExternalReferrers(
            $logAggregator,
            $idaction,
            $actionType,
            $limitBeforeGrouping ?: $this->limitBeforeGrouping,
            $this->getLiveQueryTimeLimit()
        );

        // array is prefilled with available keys and empty values are removed in the end to ensure the order is static
        $referrerData    = [
            Common::REFERRER_TYPE_DIRECT_ENTRY  => [],
            Common::REFERRER_TYPE_SEARCH_ENGINE => [],
            Common::REFERRER_TYPE_SOCIAL_NETWORK => [],
            Common::REFERRER_TYPE_AI_ASSISTANT  => [],
            Common::REFERRER_TYPE_WEBSITE       => [],
            Common::REFERRER_TYPE_CAMPAIGN      => [],
        ];
        $referrerSubData = [];

        foreach ($data as $referrerType => &$subData) {
            $referrerData[$referrerType] = [Metrics::INDEX_NB_VISITS => 0];
            if ($referrerType != Common::REFERRER_TYPE_DIRECT_ENTRY) {
                $referrerSubData[$referrerType] = [];
            }

            foreach ($subData as &$row) {
                if ($referrerType == Common::REFERRER_TYPE_SEARCH_ENGINE && empty($row['referrer_data'])) {
                    $row['referrer_data'] = Piwik::translate('General_Unknown');
                }

                $referrerData[$referrerType][Metrics::INDEX_NB_VISITS] += $row[Metrics::INDEX_NB_VISITS];

                $label = $row['referrer_data'];
                if ($label) {
                    $referrerSubData[$referrerType][$label] = [
                        Metrics::INDEX_NB_VISITS => $row[Metrics::INDEX_NB_VISITS],
                    ];
                }
            }
        }

        // remove empty records
        $referrerData = array_filter($referrerData);

        $array = new DataArray($referrerData, $referrerSubData);
        return $array->asDataTable();
    }

    /**
     * Get information about internal referrers (previous pages & loops, i.e. page refreshes)
     *
     * @return array{pageviews: int, previousPages: DataTable, previousSiteSearches: DataTable, loops: int}
     */
    protected function queryInternalReferrers(int $idaction, string $actionType, LogAggregator $logAggregator, int $limitBeforeGrouping = 0): array
    {
        $keyIsPageUrlAction = TransitionsModel::PARTITION_PAGE;
        $keyIsSiteSearchAction = TransitionsModel::PARTITION_SITE_SEARCH;

        $data = StaticContainer::get(TransitionsModel::class)->queryInternalReferrers(
            $logAggregator,
            $idaction,
            $actionType,
            $limitBeforeGrouping ? $limitBeforeGrouping : $this->limitBeforeGrouping,
            $this->getLiveQueryTimeLimit()
        );

        $loops = 0;
        $nbPageviews = 0;
        $previousPagesDataTable = new DataTable();
        if (isset($data['result'][$keyIsPageUrlAction])) {
            foreach ($data['result'][$keyIsPageUrlAction] as &$page) {
                $nbActions = intval($page[Metrics::INDEX_NB_ACTIONS]);
                $previousPagesDataTable->addRow(new Row([
                    Row::COLUMNS => [
                        'label'                   => $this->getPageLabel($page, Action::TYPE_PAGE_URL),
                        Metrics::INDEX_NB_ACTIONS => $nbActions,
                    ],
                ]));
                $nbPageviews += $nbActions;
            }
        }

        $previousSearchesDataTable = new DataTable();
        if (isset($data['result'][$keyIsSiteSearchAction])) {
            foreach ($data['result'][$keyIsSiteSearchAction] as &$search) {
                $nbActions = intval($search[Metrics::INDEX_NB_ACTIONS]);
                $previousSearchesDataTable->addRow(new Row([
                    Row::COLUMNS => [
                        'label'                   => $search['name'],
                        Metrics::INDEX_NB_ACTIONS => $nbActions,
                    ],
                ]));
                $nbPageviews += $nbActions;
            }
        }

        if (isset($data['result'][0])) {
            foreach ($data['result'][0] as &$referrer) {
                $nbPageviews += intval($referrer[Metrics::INDEX_NB_ACTIONS]);
            }
        }

        if (count($data['excludedFromLimit'])) {
            $loops += intval($data['excludedFromLimit'][0][Metrics::INDEX_NB_ACTIONS]);
            $nbPageviews += $loops;
        }

        return [
            'pageviews'            => $nbPageviews,
            'previousPages'        => $previousPagesDataTable,
            'previousSiteSearches' => $previousSearchesDataTable,
            'loops'                => $loops,
        ];
    }

    /**
     * @param array $pageRecord
     * @param int|string $type
     * @return string|null
     */
    private function getPageLabel(&$pageRecord, $type)
    {
        return StaticContainer::get(TransitionsModel::class)->getActionLabel($pageRecord, (int) $type);
    }

    private function getLiveQueryTimeLimit(): float
    {
        return GeneralConfig::getFloatConfigValue('live_query_max_execution_time', -1.0);
    }

    private int $limitBeforeGrouping = 5;

    private int $totalTransitionsToFollowingPages = 0;

    /**
     * Get the sum of all transitions to following actions (pages, outlinks, downloads).
     * Only works if queryFollowingActions() has been used directly before.
     */
    protected function getTotalTransitionsToFollowingActions(): int
    {
        return $this->totalTransitionsToFollowingPages;
    }

    /**
     * Add the external referrers to the report:
     * direct entries, websites, campaigns, search engines
     *
     * @param array<string, mixed> $report
     */
    private function addExternalReferrers(LogAggregator $logAggregator, array &$report, int $idaction, string $actionType, int $limitBeforeGrouping): void
    {
        $data = $this->queryExternalReferrers(
            $idaction,
            $actionType,
            $logAggregator,
            $limitBeforeGrouping
        );

        $this->addReferrers($report, $data);
    }

    /**
     * Adds the referrers returned by {@link queryExternalReferrers()} and the number of entries to the report.
     *
     * @param array<string, mixed> $report
     */
    private function addReferrers(array &$report, DataTable $data): void
    {
        $report['pageMetrics']['entries'] = 0;
        $report['referrers'] = [];
        foreach ($data->getRows() as $row) {
            /** @var int|string $referrerId */
            $referrerId = $row->getColumn('label');
            $visits = $row->getColumn(Metrics::INDEX_NB_VISITS);
            if ($visits) {
                // load details (i.e. subtables)
                $details = [];
                $subTable = $row->getSubtable();
                if ($subTable) {
                    foreach ($subTable->getRows() as $subRow) {
                        $details[] = [
                            'label'     => $subRow->getColumn('label'),
                            'referrals' => $subRow->getColumn(Metrics::INDEX_NB_VISITS),
                        ];
                    }
                }
                $report['referrers'][] = [
                    'label'     => $this->getReferrerLabel($referrerId),
                    'shortName' => \Piwik\Plugins\Referrers\getReferrerTypeFromShortName($referrerId),
                    'visits'    => $visits,
                    'details'   => $details,
                ];
                $report['pageMetrics']['entries'] += $visits;
            }
        }

        // if there's no data for referrers, ResponseBuilder::handleMultiDimensionalArray
        // does not detect the multi dimensional array and the data is rendered differently, which
        // causes an exception.
        if (count($report['referrers']) == 0) {
            $report['referrers'][] = [
                'label'     => $this->getReferrerLabel(Common::REFERRER_TYPE_DIRECT_ENTRY),
                'shortName' => \Piwik\Plugins\Referrers\getReferrerTypeLabel(Common::REFERRER_TYPE_DIRECT_ENTRY),
                'visits'    => 0,
            ];
        }
    }

    /**
     * @param int|string $referrerId
     */
    private function getReferrerLabel($referrerId): string
    {
        switch ($referrerId) {
            case Common::REFERRER_TYPE_DIRECT_ENTRY:
                return Piwik::translate('Transitions_DirectEntries');
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                return Piwik::translate('Transitions_FromSearchEngines');
            case Common::REFERRER_TYPE_SOCIAL_NETWORK:
                return Piwik::translate('Transitions_FromSocialNetworks');
            case Common::REFERRER_TYPE_AI_ASSISTANT:
                return Piwik::translate('Transitions_FromAIAssistants');
            case Common::REFERRER_TYPE_WEBSITE:
                return Piwik::translate('Transitions_FromWebsites');
            case Common::REFERRER_TYPE_CAMPAIGN:
                return Piwik::translate('Transitions_FromCampaigns');
            default:
                return Piwik::translate('General_Others');
        }
    }

    /**
     * @param array $types
     * @param array $data
     * @return array<string, DataTable>
     */
    protected function makeDataTablesFollowingActions(array $types, array $data): array
    {
        $this->totalTransitionsToFollowingPages = 0;
        $dataTables = array();
        foreach ($types as $type => $recordName) {
            $dataTable = new DataTable();
            if (isset($data[$type])) {
                foreach ($data[$type] as &$record) {
                    $actions = intval($record[Metrics::INDEX_NB_ACTIONS]);
                    $dataTable->addRow(new Row(array(
                                                    Row::COLUMNS => array(
                                                        'label'                   => $this->getPageLabel($record, $type),
                                                        Metrics::INDEX_NB_ACTIONS => $actions,
                                                    ),
                                               )));

                    $this->processTransitionsToFollowingPages($type, $actions);
                }
            }
            $dataTables[$recordName] = $dataTable;
        }
        return $dataTables;
    }

    /**
     * @param int $type
     * @param int $actions
     */
    protected function processTransitionsToFollowingPages($type, $actions): void
    {
        // Downloads and Outlinks are not included as these actions count towards a Visit Exit
        $actionTypesNotExitActions = array(
            Action::TYPE_SITE_SEARCH,
            Action::TYPE_PAGE_TITLE,
            Action::TYPE_PAGE_URL,
        );
        if (in_array($type, $actionTypesNotExitActions)) {
            $this->totalTransitionsToFollowingPages += $actions;
        }
    }


    /**
     * Check if a period is allowed by config settings
     *
     * @param int $idSite The numeric ID of the website the Transitions report is for.
     * @param string $period The period to check, e.g. 'day' or 'range'.
     * @param string $date The date or date range of the period.
     */
    public function isPeriodAllowed(int $idSite, string $period, string $date): bool
    {
        Piwik::checkUserHasViewAccess($idSite);

        $maxPeriodAllowed = Transitions::getPeriodAllowedConfig($idSite);
        if ($maxPeriodAllowed === 'all') {
            return true;
        }

        // If the period is a range then the number of days in the range must be less or equal to the max period allowed
        if ($period === 'range') {
            $range = new Period\Range($period, $date);
            $rangeDays = $range->getDayCount();

            switch ($maxPeriodAllowed) {
                case 'day':
                    return $rangeDays == 1;
                case 'week':
                    return $rangeDays <= 7;
                case 'month':
                    return $rangeDays <= 31;
                case 'year':
                    return $rangeDays <= 365;
            }
        }

        switch ($maxPeriodAllowed) {
            case 'day':
                return $period === 'day';
            case 'week':
                return in_array($period, ['day', 'week']);
            case 'month':
                return in_array($period, ['day', 'week', 'month']);
            case 'year':
                return in_array($period, ['day', 'week', 'month', 'year']);
        }

        return false;
    }
}
