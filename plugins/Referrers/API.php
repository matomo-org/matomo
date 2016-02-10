<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\Archive;
use Piwik\Common;
use Piwik\DataTable\Row;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * The Referrers API lets you access reports about Websites, Search engines, Keywords, Campaigns used to access your website.
 *
 * For example, "getKeywords" returns all search engine keywords (with <a href='http://piwik.org/docs/analytics-api/reference/#toc-metric-definitions' rel='noreferrer' target='_blank'>general analytics metrics</a> for each keyword), "getWebsites" returns referrer websites (along with the full Referrer URL if the parameter &expanded=1 is set).
 * "getReferrerType" returns the Referrer overview report. "getCampaigns" returns the list of all campaigns (and all campaign keywords if the parameter &expanded=1 is set).
 *
 * The methods "getKeywordsForPageUrl" and "getKeywordsForPageTitle" are used to output the top keywords used to find a page.
 * Check out the widget <a href='http://demo.piwik.org/index.php?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&actionToWidgetize=getKeywordsForPage&idSite=7&period=day&date=2011-02-15&disableLink=1' rel='noreferrer' target='_blank'>"Top keywords used to find this page"</a> that you can easily re-use on your website.
 * @method static \Piwik\Plugins\Referrers\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @param string $name
     * @param int $idSite
     * @param string $period
     * @param string|Date $date
     * @param string $segment
     * @param bool $expanded
     * @param int|null $idSubtable
     * @return DataTable
     */
    protected function getDataTable($name, $idSite, $period, $date, $segment, $expanded = false, $idSubtable = null)
    {
        $dataTable = Archive::getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $idSubtable);
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    /**
     * Returns a report describing visit information for each possible referrer type. The
     * result is a datatable whose subtables are the reports for each parent row's referrer type.
     *
     * The subtable reports are: 'getKeywords' (for search engine referrer type), 'getWebsites',
     * and 'getCampaigns'.
     *
     * @param string $idSite The site ID.
     * @param string $period The period to get data for, either 'day', 'week', 'month', 'year',
     *                       or 'range'.
     * @param string $date The date of the period.
     * @param bool|string $segment The segment to use.
     * @param bool|int $typeReferrer (deprecated) If you want to get data only for a specific referrer
     *                         type, supply a type for this parameter.
     * @param bool|int $idSubtable For this report this value is a referrer type ID and not an actual
     *                        subtable ID. The result when using this parameter will be the
     *                        specific report for the given referrer type.
     * @param bool $expanded Whether to get report w/ subtables loaded or not.
     * @return DataTable
     */
    public function getReferrerType($idSite, $period, $date, $segment = false, $typeReferrer = false,
                                    $idSubtable = false, $expanded = false)
    {
        // if idSubtable is supplied, interpret idSubtable as referrer type and return correct report
        if ($idSubtable !== false) {
            $result = false;
            switch ($idSubtable) {
                case Common::REFERRER_TYPE_SEARCH_ENGINE:
                    $result = $this->getKeywords($idSite, $period, $date, $segment);
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
                $result->filter('ColumnCallbackDeleteMetadata', array('segment'));
                $result->filter('ColumnCallbackDeleteMetadata', array('segmentValue'));

                return $this->removeSubtableIds($result); // this report won't return subtables of individual reports
            }
        }

        // get visits by referrer type
        $dataTable = $this->getDataTable(Archiver::REFERRER_TYPE_RECORD_NAME, $idSite, $period, $date, $segment);

        if ($typeReferrer !== false) // filter for a specific referrer type
        {
            $dataTable->filter('Pattern', array('label', $typeReferrer));
        }

        // set subtable IDs for each row to the label (which holds the int referrer type)
        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\SetGetReferrerTypeSubtables', array($idSite, $period, $date, $segment, $expanded));

        $dataTable->filter('AddSegmentByLabelMapping', array(
            'referrerType',
            array(
                Common::REFERRER_TYPE_DIRECT_ENTRY  => 'direct',
                Common::REFERRER_TYPE_CAMPAIGN      => 'campaign',
                Common::REFERRER_TYPE_SEARCH_ENGINE => 'search',
                Common::REFERRER_TYPE_WEBSITE       => 'website',
            )
        ));
        // set referrer type column to readable value
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getReferrerTypeLabel'));

        return $dataTable;
    }

    /**
     * Returns a report that shows
     */
    public function getAll($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getReferrerType($idSite, $period, $date, $segment, $typeReferrer = false, $idSubtable = false, $expanded = true);

        if ($dataTable instanceof DataTable\Map) {
            throw new Exception("Referrers.getAll with multiple sites or dates is not supported (yet).");
        }

        $dataTable = $dataTable->mergeSubtables($labelColumn = 'referer_type', $useMetadataColumn = true);
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        return $dataTable;
    }

    public function getKeywords($idSite, $period, $date, $segment = false, $expanded = false, $flat = false)
    {
        $dataTable = Archive::createDataTableFromArchive(Archiver::KEYWORDS_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat);

        if ($flat) {
            $dataTable->filterSubtables('Piwik\Plugins\Referrers\DataTable\Filter\SearchEnginesFromKeywordId', array($dataTable));
        } else {
            $dataTable->filter('AddSegmentValue');
            $dataTable->queueFilter('PrependSegment', array('referrerType==search;'));
        }

        $dataTable->queueFilter('Piwik\Plugins\Referrers\DataTable\Filter\KeywordNotDefined');

        return $dataTable;
    }

    const LABEL_KEYWORD_NOT_DEFINED = "";

    /**
     * @ignore
     */
    public static function getKeywordNotDefinedString()
    {
        return Piwik::translate('General_NotDefined', Piwik::translate('General_ColumnKeyword'));
    }

    /**
     * @ignore
     */
    public static function getCleanKeyword($label)
    {
        return $label == self::LABEL_KEYWORD_NOT_DEFINED
            ? self::getKeywordNotDefinedString()
            : $label;
    }

    public function getKeywordsForPageUrl($idSite, $period, $date, $url)
    {
        // Fetch the Top keywords for this page
        $segment = 'entryPageUrl==' . $url;
        $table = $this->getKeywords($idSite, $period, $date, $segment);
        $this->filterOutKeywordNotDefined($table);
        return $this->getLabelsFromTable($table);
    }

    public function getKeywordsForPageTitle($idSite, $period, $date, $title)
    {
        $segment = 'entryPageTitle==' . $title;
        $table = $this->getKeywords($idSite, $period, $date, $segment);
        $this->filterOutKeywordNotDefined($table);
        return $this->getLabelsFromTable($table);
    }

    /**
     * @param DataTable $table
     */
    private function filterOutKeywordNotDefined($table)
    {
        if ($table instanceof DataTable) {
            $row = $table->getRowIdFromLabel('');
            if ($row) {
                $table->deleteRow($row);
            }
        }
    }

    protected function getLabelsFromTable($table)
    {
        $request = $_GET;
        $request['serialize'] = 0;

        // Apply generic filters
        $response = new ResponseBuilder($format = 'original', $request);
        $table = $response->getResponse($table);

        // If period=lastX we only keep the first resultset as we want to return a plain list
        if ($table instanceof DataTable\Map) {
            $tables = $table->getDataTables();
            $table = current($tables);
        }
        // Keep the response simple, only include keywords
        $keywords = $table->getColumn('label');
        return $keywords;
    }

    public function getSearchEnginesFromKeywordId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::KEYWORDS_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
        $keywords  = $this->getKeywords($idSite, $period, $date, $segment);
        $keyword   = $keywords->getRowFromIdSubDataTable($idSubtable)->getColumn('label');

        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\SearchEnginesFromKeywordId', array($keywords, $idSubtable));
        $dataTable->filter('AddSegmentByLabel', array('referrerName'));
        $dataTable->queueFilter('PrependSegment', array('referrerKeyword=='.$keyword.';referrerType==search;'));

        return $dataTable;
    }

    public function getSearchEngines($idSite, $period, $date, $segment = false, $expanded = false, $flat = false)
    {
        $dataTable = Archive::createDataTableFromArchive(Archiver::SEARCH_ENGINES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat);

        if ($flat) {
            $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'url', function ($url) { return SearchEngine::getInstance()->getUrlFromName($url); }));
            $dataTable->filter('MetadataCallbackAddMetadata', array('url', 'logo', function ($url) { return SearchEngine::getInstance()->getLogoFromUrl($url); }));
            $dataTable->filterSubtables('Piwik\Plugins\Referrers\DataTable\Filter\KeywordsFromSearchEngineId', array($dataTable));
        } else {
            $dataTable->filter('AddSegmentByLabel', array('referrerName'));
            $dataTable->queueFilter('PrependSegment', array('referrerType==search;'));
            $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'url', function ($url) { return SearchEngine::getInstance()->getUrlFromName($url); }));
            $dataTable->queueFilter('MetadataCallbackAddMetadata', array('url', 'logo', function ($url) { return SearchEngine::getInstance()->getLogoFromUrl($url); }));
        }

        return $dataTable;
    }

    public function getKeywordsFromSearchEngineId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::SEARCH_ENGINES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);

        // get the search engine and create the URL to the search result page
        $searchEngines = $this->getSearchEngines($idSite, $period, $date, $segment);
        $searchEngines->applyQueuedFilters();
        $searchEngine  = $searchEngines->getRowFromIdSubDataTable($idSubtable)->getColumn('label');

        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\KeywordsFromSearchEngineId', array($searchEngines, $idSubtable));
        $dataTable->filter('AddSegmentByLabel', array('referrerKeyword'));
        $dataTable->queueFilter('PrependSegment', array('referrerName=='.$searchEngine.';referrerType==search;'));

        return $dataTable;
    }

    public function getCampaigns($idSite, $period, $date, $segment = false, $expanded = false)
    {
        $dataTable = $this->getDataTable(Archiver::CAMPAIGNS_RECORD_NAME, $idSite, $period, $date, $segment, $expanded);

        $dataTable->filter('AddSegmentByLabel', array('referrerName'));
        $dataTable->queueFilter('PrependSegment', array('referrerType==campaign;'));

        return $dataTable;
    }

    public function getKeywordsFromCampaignId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        $campaigns = $this->getCampaigns($idSite, $period, $date, $segment);
        $campaigns->applyQueuedFilters();
        $campaign  = $campaigns->getRowFromIdSubDataTable($idSubtable)->getColumn('label');

        $dataTable = $this->getDataTable(Archiver::CAMPAIGNS_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
        $dataTable->filter('AddSegmentByLabel', array('referrerKeyword'));
        $dataTable->queueFilter('PrependSegment', array('referrerName=='.$campaign.';referrerType==campaign;'));
        return $dataTable;
    }

    public function getWebsites($idSite, $period, $date, $segment = false, $expanded = false, $flat = false)
    {
        $dataTable = Archive::createDataTableFromArchive(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable = null);

        if ($flat) {
            $dataTable->filterSubtables('Piwik\Plugins\Referrers\DataTable\Filter\UrlsFromWebsiteId');
        } else {
            $dataTable->filter('AddSegmentByLabel', array('referrerName'));
        }

        return $dataTable;
    }

    public function getUrlsFromWebsiteId($idSite, $period, $date, $idSubtable, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = false, $idSubtable);
        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\UrlsFromWebsiteId');
        $dataTable->filter('AddSegmentByLabel', array('referrerUrl'));

        return $dataTable;
    }

    /**
     * Returns report comparing the number of visits (and other info) for social network referrers.
     * This is a view of the getWebsites report.
     *
     * @param string $idSite
     * @param string $period
     * @param string $date
     * @param string|bool $segment
     * @param bool $expanded
     * @param bool $flat
     * @return DataTable
     */
    public function getSocials($idSite, $period, $date, $segment = false, $expanded = false, $flat = false)
    {
        $dataTable = Archive::createDataTableFromArchive(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, false);

        $dataTable->filter('ColumnCallbackDeleteRow', array('label', function ($url) { return !Social::getInstance()->isSocialUrl($url); }));
        $dataTable->filter('ColumnCallbackAddMetadata', array('label', 'url', function ($url) { return Social::getInstance()->getMainUrl($url); }));
        $dataTable->filter('GroupBy', array('label', function ($url) { return Social::getInstance()->getSocialNetworkFromDomain($url); }));

        $this->setSocialIdSubtables($dataTable);
        $this->removeSubtableMetadata($dataTable);

        if ($flat) {
            $this->buildExpandedTableForFlattenGetSocials($idSite, $period, $date, $segment, $expanded, $dataTable);
        }

        $dataTable->queueFilter('MetadataCallbackAddMetadata', array('url', 'logo', function ($url) { return Social::getInstance()->getLogoFromUrl($url); }));

        return $dataTable;
    }

    /**
     * Returns report containing individual referrer URLs for a specific social networking
     * site.
     *
     * @param string $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @param bool|int $idSubtable This ID does not reference a real DataTable record. Instead, it
     *                              is the array index of an item in the Socials list file.
     *                              The urls are filtered by the social network at this index.
     *                              If false, no filtering is done and every social URL is returned.
     * @return DataTable
     */
    public function getUrlsForSocial($idSite, $period, $date, $segment = false, $idSubtable = false)
    {
        $dataTable = $this->getDataTable(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded = true);

        // get the social network domain referred to by $idSubtable
        $socialNetworks = Social::getInstance()->getDefinitions();

        $social = false;
        if ($idSubtable !== false) {
            --$idSubtable;

            reset($socialNetworks);
            for ($i = 0; $i != (int)$idSubtable; ++$i) {
                next($socialNetworks);
            }

            $social = current($socialNetworks);
        }

        // filter out everything but social network indicated by $idSubtable
        $dataTable->filter(
            'ColumnCallbackDeleteRow',
            array('label',
                  function ($url) use ($social) { return !Social::getInstance()->isSocialUrl($url, $social); }
            )
        );

        // merge the datatable's subtables which contain the individual URLs
        $dataTable = $dataTable->mergeSubtables();

        $dataTable->filter('AddSegmentByLabel', array('referrerUrl'));
        $dataTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\UrlsForSocial', array($expanded));

        return $dataTable;
    }

    public function getNumberOfDistinctSearchEngines($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    public function getNumberOfDistinctKeywords($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    public function getNumberOfDistinctCampaigns($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    public function getNumberOfDistinctWebsites($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    public function getNumberOfDistinctWebsitesUrls($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric(Archiver::METRIC_DISTINCT_URLS_RECORD_NAME, $idSite, $period, $date, $segment);
    }

    private function getNumeric($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        return $archive->getDataTableFromNumeric($name);
    }

    /**
     * Removes idsubdatatable_in_db metadata from a DataTable. Used by Social tables since
     * they use fake subtable IDs.
     *
     * @param DataTable $dataTable
     */
    private function removeSubtableMetadata($dataTable)
    {
        if ($dataTable instanceof DataTable\Map) {
            foreach ($dataTable->getDataTables() as $childTable) {
                $this->removeSubtableMetadata($childTable);
            }
        } else {
            foreach ($dataTable->getRows() as $row) {
                $row->deleteMetadata('idsubdatatable_in_db');
            }
        }
    }

    /**
     * Sets the subtable IDs for the DataTable returned by getSocial.
     *
     * The IDs are int indexes into the array in of defined socials.
     *
     * @param DataTable $dataTable
     */
    private function setSocialIdSubtables($dataTable)
    {
        if ($dataTable instanceof DataTable\Map) {
            foreach ($dataTable->getDataTables() as $childTable) {
                $this->setSocialIdSubtables($childTable);
            }
        } else {
            foreach ($dataTable->getRows() as $row) {
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
        }
    }

    /**
     * Utility function that removes the subtable IDs for the subtables of the
     * getReferrerType report. This avoids infinite recursion in said report (ie,
     * the grandchildren of the report will be the original report, and it will
     * recurse when trying to get a flat report).
     *
     * @param DataTable $table
     * @return DataTable Returns $table for convenience.
     */
    private function removeSubtableIds($table)
    {
        if ($table instanceof DataTable\Map) {
            foreach ($table->getDataTables() as $childTable) {
                $this->removeSubtableIds($childTable);
            }
        } else {
            foreach ($table->getRows() as $row) {
                $row->removeSubtable();
            }
        }

        return $table;
    }

    /**
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param string|false $segment
     * @param bool $expanded
     * @param DataTable $dataTable
     */
    private function buildExpandedTableForFlattenGetSocials($idSite, $period, $date, $segment, $expanded, $dataTable)
    {
        $urlsTable = Archive::createDataTableFromArchive(Archiver::WEBSITES_RECORD_NAME, $idSite, $period, $date, $segment, $expanded, $flat = true);
        $urlsTable->filter('ColumnCallbackDeleteRow', array('label', function ($url) {
            return !Social::getInstance()->isSocialUrl($url);
        }));
        $urlsTable = $urlsTable->mergeSubtables();

        foreach ($dataTable->getRows() as $row) {
            $row->removeSubtable();

            $social = $row->getColumn('label');
            $newTable = $urlsTable->getEmptyClone();

            $rows = $urlsTable->getRows();
            foreach ($rows as $id => $urlsTableRow) {
                $url = $urlsTableRow->getColumn('label');
                if (Social::getInstance()->isSocialUrl($url, $social)) {
                    $newTable->addRow($urlsTableRow);
                    $urlsTable->deleteRow($id);
                }
            }

            if ($newTable->getRowsCount()) {
                $newTable->filter('Piwik\Plugins\Referrers\DataTable\Filter\UrlsForSocial', array($expanded));
                $row->setSubtable($newTable);
            }
        }

        Common::destroy($urlsTable);
        $urlsTable = null;
    }

}
