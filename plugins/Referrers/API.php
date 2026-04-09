<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers;

use Exception;
use Piwik\API\Request;
use Piwik\Archive;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Referrers\Columns\Metrics\VisitorsFromReferrerPercent;
use Piwik\Plugins\Referrers\DataTable\Filter\GroupDifferentSocialWritings;
use Piwik\Site;
use Piwik\Tracker\Action;

/**
 * The Referrers API lets you access reports about websites, search engines, keywords, social networks,
 * AI assistants, and campaigns used to access your website.
 *
 * For example, "getKeywords" returns all search engine keywords (with <a href='https://developer.matomo.org/api-reference/reporting-api#api-response-metric-definitions' rel='noreferrer' target='_blank'>general analytics metrics</a> for each keyword), "getWebsites" returns referrer websites (along with the full Referrer URL if the parameter &expanded=1 is set).
 * "getReferrerType" returns the Referrer overview report. "getCampaigns" returns the list of all campaigns (and all campaign keywords if the parameter &expanded=1 is set).
 * "getSocials" returns social network referrers, and "getAIAssistants" returns AI assistant referrers.
 *
 * @method static \Piwik\Plugins\Referrers\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the referrer overview report with distinct referrer counts and percentage metrics.
     *
     * @param int|string $idSite The site ID to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param string|string[]|false $columns Specific columns to include, or `false` to return all columns.
     * @return DataTable Referrer overview rows with summary counts and processed percentage metrics.
     */
    public function get($idSite, string $period, string $date, ?string $segment = null, $columns = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTableReferrersType = $this->getReferrerType($idSite, $period, $date, $segment);
        $dataTable = $this->createReferrerTypeTable($dataTableReferrersType);

        $archive = Archive::build($idSite, $period, $date, $segment);

        $numericArchives = $archive->getDataTableFromNumeric([
            Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME,
            Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME,
            Archiver::METRIC_DISTINCT_AI_ASSISTANT_RECORD_NAME,
            Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME,
            Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME,
            Archiver::METRIC_DISTINCT_URLS_RECORD_NAME,
            Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME,
        ]);
        $this->mergeNumericArchives($dataTable, $numericArchives);

        $totalVisits = array_sum($dataTableReferrersType->getColumn(Metrics::INDEX_NB_VISITS));

        $dataTable->filter(function (DataTable $table) use ($totalVisits) {
            /** @var ProcessedMetric[] $processedMetrics */
            $processedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];

            $percentColumns = [
                'Referrers_visitorsFromDirectEntry',
                'Referrers_visitorsFromSearchEngines',
                'Referrers_visitorsFromAIAssistants',
                'Referrers_visitorsFromCampaigns',
                'Referrers_visitorsFromSocialNetworks',
                'Referrers_visitorsFromWebsites',
            ];
            foreach ($percentColumns as $column) {
                $processedMetrics[] = new VisitorsFromReferrerPercent($column . '_percent', $column, $totalVisits);
            }

            $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $processedMetrics);
        });

        if (!empty($requestedColumns)) {
            $requestedColumns = Piwik::getArrayFromApiParameter($columns);
            $dataTable->filter(DataTable\Filter\ColumnDelete::class, [[], $requestedColumns]);
        }

        return $dataTable;
    }

    /**
     * @param int|string|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    protected function getDataTable(string $name, $idSite, string $period, string $date, ?string $segment, bool $expanded = false, ?int $idSubtable = null)
    {
        return Archive::createDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, false, $idSubtable);
    }

    /**
     * Returns a report describing visit information for each possible referrer type. The
     * result is a datatable whose subtables are the reports for each parent row's referrer type.
     *
     * The subtable reports include keywords, social networks, AI assistants, websites, and campaigns.
     *
     * @param int|string $idSite The site ID to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param bool|int|string $typeReferrer Deprecated referrer type filter to restrict the returned rows.
     * @param int|null $idSubtable Referrer type ID to load directly instead of the overview report.
     * @param bool $expanded `true` to load subtables eagerly, `false` to return only top-level rows.
     * @param bool $_setReferrerTypeLabel `true` to replace referrer type IDs with human-readable labels.
     * @return DataTable|DataTable\Map Report rows for each referrer type, or the selected referrer-type subreport.
     */
    public function getReferrerType(
        $idSite,
        string $period,
        string $date,
        ?string $segment = null,
        $typeReferrer = false,
        ?int $idSubtable = null,
        bool $expanded = false,
        bool $_setReferrerTypeLabel = true
    ) {
        Piwik::checkUserHasViewAccess($idSite);

        $this->checkSingleSite($idSite, 'getReferrerType');

        // if idSubtable is supplied, interpret idSubtable as referrer type and return correct report
        if ($idSubtable !== null) {
            $result = false;
            switch ($idSubtable) {
                case Common::REFERRER_TYPE_SEARCH_ENGINE:
                    $result = $this->getKeywords($idSite, $period, $date, $segment);
                    break;
                case Common::REFERRER_TYPE_SOCIAL_NETWORK:
                    $result = $this->getSocials($idSite, $period, $date, $segment);
                    break;
                case Common::REFERRER_TYPE_AI_ASSISTANT:
                    $result = $this->getAIAssistants($idSite, $period, $date, $segment);
                    break;
                case Common::REFERRER_TYPE_WEBSITE:
                    $result = $this->getWebsites($idSite, $period, $date, $segment);
                    break;
                case Common::REFERRER_TYPE_CAMPAIGN:
                    $result = $this->getCampaigns($idSite, $period, $date, $segment);
                    break;
                default: // invalid idSubtable, return whole report
                    break;
            }

            if ($result) {
                // Remove subtable IDs to avoid infinite recursion: the grandchildren would be
                // the original getReferrerType report again, looping when requesting a flat report.
                return $this->removeSubtableIds($result);
            }
        }

        // get visits by referrer type
        $dataTable = $this->getDataTable(Archiver::REFERRER_TYPE_RECORD_NAME, $idSite, $period, $date, $segment);

        // checks for  && $typeReferrer !== 'false' && $typeReferrer !== '0' added to cover intention when
        // it is passed as a string in a GET or POST parameter
        if ($typeReferrer !== false && $typeReferrer !== 'false' && $typeReferrer !== '0') { // filter for a specific referrer type
            $dataTable->filter('Pattern', ['label', $typeReferrer]);
        }

        // set subtable IDs for each row to the label (which holds the int referrer type)
        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\SetGetReferrerTypeSubtables', [$idSite, $period, $date, $segment, $expanded]);

        $dataTable->filter('AddSegmentByLabelMapping', [
            'referrerType',
            [
                Common::REFERRER_TYPE_DIRECT_ENTRY   => 'direct',
                Common::REFERRER_TYPE_CAMPAIGN       => 'campaign',
                Common::REFERRER_TYPE_SEARCH_ENGINE  => 'search',
                Common::REFERRER_TYPE_SOCIAL_NETWORK => 'social',
                Common::REFERRER_TYPE_AI_ASSISTANT => 'ai',
                Common::REFERRER_TYPE_WEBSITE        => 'website',
            ],
        ]);

        // set referrer type column to readable value
        if ($_setReferrerTypeLabel) {
            $dataTable->filter(DataTable\Filter\ColumnCallbackAddMetadata::class, ['label', 'referrer_type']);
            $dataTable->filter('ColumnCallbackReplace', ['label', __NAMESPACE__ . '\getReferrerTypeLabel']);
        }

        return $dataTable;
    }

    /**
     * @param int|string|int[] $idSite
     */
    private function checkSingleSite($idSite, string $method): void
    {
        $idSites = Site::getIdSitesFromIdSitesString($idSite, false, true);

        if (count($idSites) > 1 || 'all' === $idSite) {
            throw new Exception("Referrers.$method with multiple sites is not supported (yet).");
        }
    }

    /**
     * Returns a flattened report containing all referrer subtables merged into one table.
     *
     * @param int|string $idSite The site ID to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable Flattened referrer report with subtables merged into the main table.
     */
    public function getAll($idSite, string $period, string $date, ?string $segment = null)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $this->checkSingleSite($idSite, 'getAll');
        /** @var DataTable|DataTable\Map $dataTable */
        $dataTable = Request::processRequest('Referrers.getReferrerType', [
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'segment' => $segment,
            'expanded' => true,
            'disable_generic_filters' => true,
            'disable_queued_filters' => true,
            '_setReferrerTypeLabel' => 0,
        ], []);

        if ($dataTable instanceof DataTable\Map) {
            throw new Exception("Referrers.getAll with multiple sites or dates is not supported (yet).");
        }

        $dataTable = $dataTable->mergeSubtables($labelColumn = 'referer_type', $useMetadataColumn = true);
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        return $dataTable;
    }

    /**
     * Returns search keywords that brought visits to the requested website.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param bool $expanded `true` to load keyword subtables eagerly.
     * @param bool $flat `true` to flatten subtables into the main table.
     * @return DataTable|DataTable\Map Search keyword rows for the requested period.
     */
    public function getKeywords($idSite, string $period, string $date, ?string $segment = null, bool $expanded = false, bool $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive(Archiver::KEYWORDS_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat);

        if ($flat) {
            $dataTable->filterSubtables('Piwik\Plugins\Referrers\DataTable\Filter\SearchEnginesFromKeywordId', [$dataTable]);
        } else {
            $dataTable->filter('AddSegmentByLabel', ['referrerKeyword', '', $allowEmptyValue = true]);
            $dataTable->queueFilter('PrependSegment', ['referrerType==search;']);
        }

        $dataTable->queueFilter('Piwik\Plugins\Referrers\DataTable\Filter\KeywordNotDefined');

        return $dataTable;
    }

    public const LABEL_KEYWORD_NOT_DEFINED = "";

    /**
     * @ignore
     */
    public static function getKeywordNotDefinedString(): string
    {
        return Piwik::translate('General_NotDefined', Piwik::translate('General_ColumnKeyword'));
    }

    /**
     * @ignore
     */
    public static function getCleanKeyword(?string $label): ?string
    {
        return $label == self::LABEL_KEYWORD_NOT_DEFINED
            ? self::getKeywordNotDefinedString()
            : $label;
    }

    /**
     * Returns the search engines associated with a specific keyword subtable.
     *
     * @param int|string $idSite The site ID to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param int $idSubtable Keyword subtable ID to expand.
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Search engine rows for the selected keyword.
     */
    public function getSearchEnginesFromKeywordId($idSite, string $period, string $date, int $idSubtable, ?string $segment = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = $this->getDataTable(Archiver::KEYWORDS_RECORD_NAME, $idSite, $period, $date, $segment, false, $idSubtable);
        $keywords  = $this->getKeywords($idSite, $period, $date, $segment);
        $keyword   = $keywords->getRowFromIdSubDataTable($idSubtable)->getColumn('label');

        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\SearchEnginesFromKeywordId', [$keywords, $idSubtable]);
        $dataTable->filter('AddSegmentByLabel', ['referrerName']);
        $dataTable->queueFilter('PrependSegment', ['referrerKeyword==' . $keyword . ';referrerType==search;']);

        return $dataTable;
    }

    /**
     * Returns search engines that referred visits to the requested website.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param bool $expanded `true` to load keyword subtables eagerly.
     * @param bool $flat `true` to flatten subtables into the main table.
     * @return DataTable|DataTable\Map Search engine rows for the requested period.
     */
    public function getSearchEngines($idSite, string $period, string $date, ?string $segment = null, bool $expanded = false, bool $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = Archive::createDataTableFromArchive(Archiver::SEARCH_ENGINES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat);

        if ($flat) {
            $dataTable->filter('ColumnCallbackAddMetadata', [
                'label',
                'url',
                function ($url) {
                    return SearchEngine::getInstance()->getUrlFromName($url);
                },
            ]);
            $dataTable->filter('MetadataCallbackAddMetadata', [
                'url',
                'logo',
                function ($url) {
                    return SearchEngine::getInstance()->getLogoFromUrl($url);
                },
            ]);
            $dataTable->filterSubtables(
                'Piwik\Plugins\Referrers\DataTable\Filter\KeywordsFromSearchEngineId',
                [$dataTable]
            );
        } else {
            $dataTable->filter('AddSegmentByLabel', ['referrerName']);
            $dataTable->queueFilter('PrependSegment', ['referrerType==search;']);
            $dataTable->queueFilter('ColumnCallbackAddMetadata', [
                'label',
                'url',
                function ($url) {
                    return SearchEngine::getInstance()->getUrlFromName($url);
                },
            ]);
            $dataTable->queueFilter('MetadataCallbackAddMetadata', [
                'url',
                'logo',
                function ($url) {
                    return SearchEngine::getInstance()->getLogoFromUrl($url);
                },
            ]);
        }

        return $dataTable;
    }

    /**
     * Returns keywords for a specific search engine subtable.
     *
     * @param int|string $idSite The site ID to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param int $idSubtable Search engine subtable ID to expand.
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Keyword rows for the selected search engine.
     */
    public function getKeywordsFromSearchEngineId($idSite, string $period, string $date, int $idSubtable, ?string $segment = null)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getDataTable(Archiver::SEARCH_ENGINES_RECORD_NAME, $idSite, $period, $date, $segment, false, $idSubtable);

        // get the search engine and create the URL to the search result page
        $searchEngines = $this->getSearchEngines($idSite, $period, $date, $segment);
        $searchEngines->applyQueuedFilters();
        $row  = $searchEngines->getRowFromIdSubDataTable($idSubtable);

        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\KeywordsFromSearchEngineId', [$searchEngines, $idSubtable]);
        $dataTable->filter('AddSegmentByLabel', ['referrerKeyword']);

        if (!empty($row)) {
            $searchEngine = $row->getColumn('label');
            $dataTable->queueFilter('PrependSegment', ['referrerName==' . $searchEngine . ';referrerType==search;']);
        }

        return $dataTable;
    }

    /**
     * Returns campaigns that referred visits to the requested website.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param bool $expanded `true` to load campaign keyword subtables eagerly.
     * @return DataTable|DataTable\Map Campaign rows for the requested period.
     */
    public function getCampaigns($idSite, string $period, string $date, ?string $segment = null, bool $expanded = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = $this->getDataTable(Archiver::CAMPAIGNS_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);

        $dataTable->filter('AddSegmentByLabel', ['referrerName']);
        $dataTable->queueFilter('PrependSegment', ['referrerType==campaign;']);

        return $dataTable;
    }

    /**
     * Returns campaign keywords for a specific campaign subtable.
     *
     * @param int|string $idSite The site ID to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param int $idSubtable Campaign subtable ID to expand.
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Campaign keyword rows for the selected campaign.
     */
    public function getKeywordsFromCampaignId($idSite, string $period, string $date, int $idSubtable, ?string $segment = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $campaigns = $this->getCampaigns($idSite, $period, $date, $segment);
        $campaigns->applyQueuedFilters();
        $row = $campaigns->getRowFromIdSubDataTable($idSubtable);
        $campaign = $row ? $row->getColumn('label') : '';

        $dataTable = $this->getDataTable(Archiver::CAMPAIGNS_RECORD_NAME, $idSite, $period, $date, $segment, false, $idSubtable);
        $dataTable->filter('AddSegmentByLabel', ['referrerKeyword']);
        $dataTable->queueFilter('PrependSegment', ['referrerName==' . $campaign . ';referrerType==campaign;']);
        return $dataTable;
    }

    /**
     * Returns referring websites for the requested website.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param bool $expanded `true` to load URL subtables eagerly.
     * @param bool $flat `true` to flatten subtables into the main table.
     * @return DataTable|DataTable\Map Referring website rows for the requested period.
     */
    public function getWebsites($idSite, string $period, string $date, ?string $segment = null, bool $expanded = false, bool $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = Archive::createDataTableFromArchive(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat, null);

        if ($flat) {
            $dataTable->filterSubtables('Piwik\Plugins\Referrers\DataTable\Filter\UrlsFromWebsiteId');
        } else {
            $dataTable->filter('AddSegmentByLabel', ['referrerName']);
        }

        return $dataTable;
    }

    /**
     * Returns individual referrer URLs for a specific website subtable.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param int $idSubtable Website subtable ID to expand.
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Referrer URL rows for the selected website.
     */
    public function getUrlsFromWebsiteId($idSite, string $period, string $date, int $idSubtable, ?string $segment = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $dataTable = $this->getDataTable(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\UrlsFromWebsiteId');
        $dataTable->filter('MetadataCallbackAddMetadata', [
            'url', 'segment', function ($url) {
                return 'referrerUrl==' . urlencode($url);
            },
        ]);

        return $dataTable;
    }

    /**
     * Returns report comparing the number of visits and related metrics for social network referrers.
     * It uses the dedicated social archive and backfills missing rows from website referrer data when needed.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param bool $expanded `true` to load URL subtables eagerly.
     * @param bool $flat `true` to flatten subtables into the main table.
     * @return DataTable|DataTable\Map Social network referrer rows for the requested period.
     */
    public function getSocials($idSite, string $period, string $date, ?string $segment = null, bool $expanded = false, bool $flat = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = Archive::createDataTableFromArchive(Archiver::SOCIAL_NETWORKS_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat);

        $dataTable->filter(GroupDifferentSocialWritings::class);

        $dataTable->filter('ColumnCallbackAddMetadata', [
            'label', 'url', function ($name) {
                return Social::getInstance()->getMainUrlFromName($name);
            },
        ]);

        $dataTable = $this->completeSocialTablesWithOldReports($dataTable, $idSite, $period, $date, $segment, $expanded, $flat);

        $dataTable->filter('MetadataCallbackAddMetadata', [
            'url',
            'logo',
            function ($url) {
                return Social::getInstance()->getLogoFromUrl($url);
            },
        ]);

        $dataTable->filter('AddSegmentByLabel', ['referrerName']);
        $dataTable->queueFilter('PrependSegment', ['referrerType==social;']);

        return $dataTable;
    }


    /**
     * Returns report comparing the number of visits and related metrics for AI assistant referrers.
     * It uses the dedicated AI assistant archive and backfills missing rows from website referrer data when needed.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param bool $expanded `true` to load secondary-dimension subtables eagerly.
     * @param bool $flat `true` to flatten subtables into the main table.
     * @param 'entryPageTitle'|'entryPageUrl'|null $secondaryDimension Secondary dimension to group AI assistant
     *                                                                 rows by. Defaults to `entryPageUrl`.
     * @return DataTable|DataTable\Map AI assistant referrer rows for the requested period.
     */
    public function getAIAssistants($idSite, string $period, string $date, ?string $segment = null, bool $expanded = false, bool $flat = false, ?string $secondaryDimension = null)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $archiveName = Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME;

        if ($secondaryDimension === 'entryPageTitle') {
            $archiveName = Archiver::AI_ASSISTANTS_ENTRY_TITLE_RECORD_NAME;
        }

        $dataTable = Archive::createDataTableFromArchive($archiveName, $idSite, $period, $date, $segment, $expanded, $flat);

        $dataTable->filter('ColumnCallbackAddMetadata', [
            'label', 'url', function ($name) {
                return AIAssistant::getInstance()->getMainUrlFromName($name);
            },
        ]);

        $dataTable = $this->completeAIAssistantTablesWithOldReports($dataTable, $idSite, $period, $date, $segment, $expanded);

        $dataTable->filter('MetadataCallbackAddMetadata', [
            'url',
            'logo',
            function ($url) {
                return AIAssistant::getInstance()->getLogoFromUrl($url);
            },
        ]);

        if ($flat) {
            $dataTable->filterSubtables('Piwik\Plugins\Referrers\DataTable\Filter\UrlsForAIAssistant');
            // don't link flattened report
            $dataTable->filterSubtables('ColumnCallbackDeleteMetadata', ['url']);
            $dataTable->filter('ColumnCallbackDeleteMetadata', ['url']);
        }

        $dataTable->filter('AddSegmentByLabel', ['referrerName']);
        $dataTable->queueFilter('PrependSegment', ['referrerType==ai;']);

        return $dataTable;
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @param int|string|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    private function completeSocialTablesWithOldReports($dataTable, $idSite, string $period, string $date, ?string $segment, bool $expanded, bool $flat)
    {
        return $this->combineDataTables($dataTable, function () use ($idSite, $period, $date, $segment, $expanded, $flat) {
            $dataTableFiltered = Archive::createDataTableFromArchive(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, false);

            $this->filterWebsitesForSocials($dataTableFiltered, $idSite, $period, $date, $segment, $expanded, $flat);

            return $dataTableFiltered;
        });
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @param int|string|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    private function completeAIAssistantTablesWithOldReports($dataTable, $idSite, string $period, string $date, ?string $segment, bool $expanded)
    {
        return $this->combineDataTables($dataTable, function () use ($idSite, $period, $date, $segment, $expanded) {
            $dataTableFiltered = Archive::createDataTableFromArchive(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, false);

            $this->filterWebsitesForAIAssistants($dataTableFiltered);

            return $dataTableFiltered;
        });
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @return DataTable|DataTable\Map
     */
    protected function combineDataTables($dataTable, callable $callbackForAdditionalData)
    {
        $isMap = false;
        $hasEmptyTable = false;
        if ($dataTable instanceof DataTable\Map) {
            $isMap = true;
            $dataTables = $dataTable->getDataTables();
        } else {
            $dataTables = [$dataTable];
        }

        foreach ($dataTables as $table) {
            if ($table instanceof DataTable && !$table->getRowsCountWithoutSummaryRow()) {
                $hasEmptyTable = true;
                break;
            }
        }

        if ($hasEmptyTable) {
            $dataTablesForCompletion = $callbackForAdditionalData();

            if (!$isMap) {
                $dataTable = $dataTablesForCompletion;
            } else {
                $filteredTables = $dataTablesForCompletion->getDataTables();
                foreach ($dataTables as $label => $table) {
                    if ($table instanceof DataTable && !$table->getRowsCountWithoutSummaryRow() && !empty($filteredTables[$label])) {
                        $dataTable->addTable($filteredTables[$label], $label);
                    }
                }
            }
        }

        return $dataTable;
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @param int|string|int[] $idSite
     */
    protected function filterWebsitesForSocials($dataTable, $idSite, string $period, string $date, ?string $segment, bool $expanded, bool $flat): void
    {
        $dataTable->filter('ColumnCallbackDeleteRow', [
            'label', function ($url) {
                return !Social::getInstance()->isSocialUrl($url);
            },
        ]);
        $dataTable->filter('ColumnCallbackAddMetadata', [
            'label', 'url', function ($url) {
                return Social::getInstance()->getMainUrl($url);
            },
        ]);
        $dataTable->filter('GroupBy', [
            'label', function ($url) {
                return Social::getInstance()->getSocialNetworkFromDomain($url);
            },
        ]);

        $this->setSocialIdSubtables($dataTable);
        $this->removeSubtableMetadata($dataTable);

        if ($flat) {
            $this->buildExpandedTableForFlattenGetSocials($idSite, $period, $date, $segment, $expanded, $dataTable);
        }
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     */
    protected function filterWebsitesForAIAssistants($dataTable): void
    {
        $dataTable->filter('ColumnCallbackDeleteRow', [
            'label', function ($url) {
                return !AIAssistant::getInstance()->isAIAssistantUrl($url);
            },
        ]);
        $dataTable->filter('ColumnCallbackAddMetadata', [
            'label', 'url', function ($url) {
                return AIAssistant::getInstance()->getMainUrl($url);
            },
        ]);
        $dataTable->filter('GroupBy', [
            'label', function ($url) {
                return AIAssistant::getInstance()->getAIAssistantFromDomain($url);
            },
        ]);

        $this->removeSubtableMetadata($dataTable);
    }

    /**
     * Returns report containing individual referrer URLs for a specific social networking
     * site.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param int|null $idSubtable This ID does not reference a real DataTable record. Instead, it
     *                             is the array index of an item in the Socials list file.
     *                             The urls are filtered by the social network at this index.
     *                             If null, no filtering is done and every social URL is returned.
     * @return DataTable|DataTable\Map Social referrer URL rows for the selected social network.
     */
    public function getUrlsForSocial($idSite, string $period, string $date, ?string $segment = null, ?int $idSubtable = null)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = $this->getDataTable(Archiver::SOCIAL_NETWORKS_RECORD_NAME, $idSite, $period, $date, $segment, true, $idSubtable);

        if (!$idSubtable) {
            $dataTable = $dataTable->mergeSubtables();
        }

        $dataTable = $this->combineDataTables($dataTable, function () use ($idSite, $period, $date, $segment, $idSubtable) {
            $dataTableFiltered = $this->getDataTable(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, true);

            $socialNetworks = array_values(Social::getInstance()->getDefinitions());
            $social = $socialNetworks[$idSubtable - 1] ?? false;

            // filter out everything but social network indicated by $idSubtable
            $dataTableFiltered->filter(
                'ColumnCallbackDeleteRow',
                [
                    'label',
                    function ($url) use ($social) {
                        return !Social::getInstance()->isSocialUrl($url, $social);
                    },
                ]
            );

            return $dataTableFiltered->mergeSubtables();
        });

        $dataTable->filter('AddSegmentByLabel', ['referrerUrl']);
        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\UrlsForSocial');
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    /**
     * Returns report containing individual entry page URLs for a specific AI assistant.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param int|null $idSubtable This ID does not reference a real DataTable record. Instead, it
     *                             is the array index of an item in the AI assistant list file.
     *                             The urls are filtered by the AI assistant at this index.
     *                             If null, no filtering is done and every AI assistant URL is returned.
     * @return DataTable|DataTable\Map Entry page URL rows for the selected AI assistant.
     */
    public function getEntryPageUrlsForAIAssistant($idSite, string $period, string $date, ?string $segment = null, ?int $idSubtable = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $aiAssistants = $this->getAIAssistants($idSite, $period, $date, $segment);
        $aiAssistants->applyQueuedFilters();
        $row       = $aiAssistants->getRowFromIdSubDataTable($idSubtable);
        $assistant = $row ? $row->getColumn('label') : '';

        $dataTable = $this->getDataTable(Archiver::AI_ASSISTANTS_ENTRY_URL_RECORD_NAME, $idSite, $period, $date, $segment, true, $idSubtable);

        if (!$idSubtable) {
            $dataTable = $dataTable->mergeSubtables();
        }

        $dataTable->filter('AddSegmentByLabel', ['entryPageUrl']);
        $dataTable->queueFilter('PrependSegment', ['referrerName==' . $assistant . ';referrerType==ai;']);
        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\UrlsForAIAssistant');
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    /**
     * Returns report containing individual entry page names for a specific AI assistant.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @param int|null $idSubtable This ID does not reference a real DataTable record. Instead, it
     *                              is the array index of an item in the AI assistant list file.
     *                              The titles are filtered by the AI assistant at this index.
     *                              If null, no filtering is done and every AI assistant title is returned.
     * @return DataTable|DataTable\Map Entry page title rows for the selected AI assistant.
     */
    public function getEntryPageTitlesForAIAssistant($idSite, string $period, string $date, ?string $segment = null, ?int $idSubtable = null)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $aiAssistants = $this->getAIAssistants($idSite, $period, $date, $segment);
        $aiAssistants->applyQueuedFilters();
        $row       = $aiAssistants->getRowFromIdSubDataTable($idSubtable);
        $assistant = $row ? $row->getColumn('label') : '';

        $dataTable = $this->getDataTable(Archiver::AI_ASSISTANTS_ENTRY_TITLE_RECORD_NAME, $idSite, $period, $date, $segment, true, $idSubtable);

        if (!$idSubtable) {
            $dataTable = $dataTable->mergeSubtables();
        }

        $dataTable->filter('AddSegmentByLabel', ['entryPageTitle']);
        $dataTable->queueFilter('PrependSegment', ['referrerName==' . $assistant . ';referrerType==ai;']);
        $dataTable->filter(function (DataTable $table) {
            $emptyUrlRRow = $table->getRowFromLabel('');

            if ($emptyUrlRRow) {
                $emptyUrlRRow->setColumn('label', ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_TITLE));
            }
        });
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    /**
     * Returns the number of distinct search engines in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of distinct search engines.
     */
    public function getNumberOfDistinctSearchEngines($idSite, string $period, string $date, ?string $segment = null)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    /**
     * Returns the number of distinct social networks in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of distinct social networks.
     */
    public function getNumberOfDistinctSocialNetworks($idSite, string $period, string $date, ?string $segment = null)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    /**
     * Returns the number of distinct search keywords in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of distinct keywords.
     */
    public function getNumberOfDistinctKeywords($idSite, string $period, string $date, ?string $segment = null)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    /**
     * Returns the number of distinct campaigns in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of distinct campaigns.
     */
    public function getNumberOfDistinctCampaigns($idSite, string $period, string $date, ?string $segment = null)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    /**
     * Returns the number of distinct referring websites in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of distinct referring websites.
     */
    public function getNumberOfDistinctWebsites($idSite, string $period, string $date, ?string $segment = null)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    /**
     * Returns the number of distinct AI assistants in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of distinct AI assistants.
     */
    public function getNumberOfDistinctAIAssistants($idSite, string $period, string $date, ?string $segment = null)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_AI_ASSISTANT_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    /**
     * Returns the number of distinct referrer URLs in the requested period.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Numeric archive result containing the number of distinct referrer URLs.
     */
    public function getNumberOfDistinctWebsitesUrls($idSite, string $period, string $date, ?string $segment = null)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_URLS_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    /**
     * @param int|string|int[] $idSite
     * @return DataTable|DataTable\Map
     */
    private function getNumeric(string $name, $idSite, string $period, string $date, ?string $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        return $archive->getDataTableFromNumeric($name);
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     */
    private function removeSubtableMetadata($dataTable): void
    {
        $dataTable->filter(function (DataTable $table) {
            foreach ($table->getRows() as $row) {
                $row->deleteMetadata('idsubdatatable_in_db');
            }
        });
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     */
    private function setSocialIdSubtables($dataTable): void
    {
        $dataTable->filter(function (DataTable $table) {
            foreach ($table->getRows() as $row) {
                $socialName = $row->getColumn('label');

                $i = 1; // start at one because idSubtable=0 is equivalent to idSubtable=false
                foreach (Social::getInstance()->getDefinitions() as $name) {
                    if ($name == $socialName) {
                        $row->setNonLoadedSubtableId($i);
                        break;
                    }

                    ++$i;
                }
            }
        });
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @return DataTable|DataTable\Map
     */
    private function removeSubtableIds($dataTable)
    {
        $dataTable->filter(function (DataTable $table) {
            foreach ($table->getRows() as $row) {
                $row->removeSubtable();
            }
        });

        return $dataTable;
    }

    /**
     * @param int|string|int[] $idSite
     * @param DataTable|DataTable\Map $dataTable
     */
    private function buildExpandedTableForFlattenGetSocials($idSite, string $period, string $date, ?string $segment, bool $expanded, $dataTable): void
    {
        $urlsTable = Archive::createDataTableFromArchive(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, true);
        $urlsTable->filter('ColumnCallbackDeleteRow', [
            'label', function ($url) {
                return !Social::getInstance()->isSocialUrl($url);
            },
        ]);
        $urlsTable = $urlsTable->mergeSubtables();

        if ($dataTable instanceof DataTable\Map) {
            $dataTables = $dataTable->getDataTables();
            /** @var DataTable\Map $urlsTable */
            $urlsTables = $urlsTable->getDataTables();
        } else {
            $dataTables = [$dataTable];
            $urlsTables = [$urlsTable];
        }

        foreach ($dataTables as $label => $dataTable) {
            foreach ($dataTable->getRows() as $row) {
                $row->removeSubtable();

                $social = $row->getColumn('label');
                $newTable = $urlsTables[$label]->getEmptyClone();

                $rows = $urlsTables[$label]->getRows();
                foreach ($rows as $id => $urlsTableRow) {
                    $url = $urlsTableRow->getColumn('label');
                    if (Social::getInstance()->isSocialUrl($url, $social)) {
                        $newTable->addRow($urlsTableRow);
                        $urlsTables[$label]->deleteRow($id);
                    }
                }

                if ($newTable->getRowsCount()) {
                    $newTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\UrlsForSocial');
                    $row->setSubtable($newTable);
                }
            }
        }

        Common::destroy($urlsTable);
        $urlsTable = null;
    }

    private function createReferrerTypeTable(DataTable\DataTableInterface $table)
    {
        if ($table instanceof DataTable) {
            $nameToColumnId = [
                Common::REFERRER_TYPE_SEARCH_ENGINE => 'Referrers_visitorsFromSearchEngines',
                Common::REFERRER_TYPE_SOCIAL_NETWORK => 'Referrers_visitorsFromSocialNetworks',
                Common::REFERRER_TYPE_AI_ASSISTANT => 'Referrers_visitorsFromAIAssistants',
                Common::REFERRER_TYPE_DIRECT_ENTRY => 'Referrers_visitorsFromDirectEntry',
                Common::REFERRER_TYPE_WEBSITE      => 'Referrers_visitorsFromWebsites',
                Common::REFERRER_TYPE_CAMPAIGN     => 'Referrers_visitorsFromCampaigns',
            ];

            $newRow = array_fill_keys(array_values($nameToColumnId), 0);
            foreach ($table->getRows() as $row) {
                $referrerType = $row->getMetadata('referrer_type');
                if (empty($nameToColumnId[$referrerType])) {
                    continue;
                }

                $nameVar = $nameToColumnId[$referrerType];
                $value = $row->getColumn(Metrics::INDEX_NB_VISITS);
                $newRow[$nameVar] = $value;
            }

            $result = new DataTable\Simple();
            $result->addRowFromSimpleArray($newRow);
            return $result;
        } elseif ($table instanceof DataTable\Map) {
            $result = new DataTable\Map();
            $result->setKeyName($table->getKeyName());
            foreach ($table->getDataTables() as $label => $childTable) {
                if ($childTable->getRowsCount() > 0) {
                    $referrerTypeTable = $this->createReferrerTypeTable($childTable);
                    $result->addTable($referrerTypeTable, $label);
                } else {
                    $result->addTable(new DataTable(), $label);
                }
            }
        } else {
            throw new \Exception("Unexpected DataTable type: " . get_class($table)); // sanity check
        }
        return $result;
    }

    /**
     * @template T of DataTable|DataTable\Map
     * @param T $table
     * @param T|null $numericArchives
     */
    private function mergeNumericArchives(DataTable\DataTableInterface $table, ?DataTable\DataTableInterface $numericArchives = null): void
    {
        if (empty($numericArchives)) {
            return;
        }

        if ($table instanceof DataTable) {
            /** @var DataTable $numericArchives */
            $table->setAllTableMetadata($numericArchives->getAllTableMetadata());

            if ($numericArchives->getRowsCount() == 0) {
                return;
            }

            if ($table->getRowsCountWithoutSummaryRow() == 0) {
                $table->addRow(new DataTable\Row());
            }

            $row = $table->getFirstRow();
            foreach ($numericArchives->getFirstRow() as $name => $value) {
                $row->setColumn($name, $value);
            }
        } elseif ($table instanceof DataTable\Map) {
            foreach ($table->getDataTables() as $label => $childTable) {
                /** @var DataTable\Map $numericArchives */
                $numericArchiveChildTable = $numericArchives->getTable($label);
                $this->mergeNumericArchives($childTable, $numericArchiveChildTable);
            }
        }
    }
}
