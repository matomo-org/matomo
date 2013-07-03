<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Referers
 */

class Piwik_Referers_Archiver extends Piwik_PluginsArchiver
{
    const SEARCH_ENGINES_RECORD_NAME = 'Referers_keywordBySearchEngine';
    const KEYWORDS_RECORD_NAME = 'Referers_searchEngineByKeyword';
    const CAMPAIGNS_RECORD_NAME = 'Referers_keywordByCampaign';
    const WEBSITES_RECORD_NAME = 'Referers_urlByWebsite';
    const REFERER_TYPE_RECORD_NAME = 'Referers_type';
    const METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME = 'Referers_distinctSearchEngines';
    const METRIC_DISTINCT_KEYWORD_RECORD_NAME = 'Referers_distinctKeywords';
    const METRIC_DISTINCT_CAMPAIGN_RECORD_NAME = 'Referers_distinctCampaigns';
    const METRIC_DISTINCT_WEBSITE_RECORD_NAME = 'Referers_distinctWebsites';
    const METRIC_DISTINCT_URLS_RECORD_NAME = 'Referers_distinctWebsitesUrls';
    protected $columnToSortByBeforeTruncation;
    protected $maximumRowsInDataTableLevelZero;
    protected $maximumRowsInSubDataTable;
    /* @var array[Piwik_DataArray] $arrays */
    protected $arrays = array();
    protected $distinctUrls = array();

    function __construct($processor)
    {
        parent::__construct($processor);
        $this->columnToSortByBeforeTruncation = Piwik_Metrics::INDEX_NB_VISITS;
        $this->maximumRowsInDataTableLevelZero = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_referers'];
        $this->maximumRowsInSubDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_referers'];
    }

    public function archiveDay()
    {
        foreach ($this->getRecordNames() as $record) {
            $this->arrays[$record] = new Piwik_DataArray();
        }
        $query = $this->getLogAggregator()->queryVisitsByDimension(array("referer_type", "referer_name", "referer_keyword", "referer_url"));
        $this->aggregateFromVisits($query);

        $query = $this->getLogAggregator()->queryConversionsByDimension(array("referer_type", "referer_name", "referer_keyword"));
        $this->aggregateFromConversions($query);

        Piwik_PostEvent('Referers.archiveDay', $this);
        $this->recordDayReports();
    }

    protected function getRecordNames()
    {
        return array(
            self::REFERER_TYPE_RECORD_NAME,
            self::KEYWORDS_RECORD_NAME,
            self::SEARCH_ENGINES_RECORD_NAME,
            self::WEBSITES_RECORD_NAME,
            self::CAMPAIGNS_RECORD_NAME,
        );
    }

    protected function aggregateFromVisits($query)
    {
        while ($row = $query->fetch()) {
            $this->makeRefererTypeNonEmpty($row);
            $this->aggregateVisit($row);
        }
    }

    protected function makeRefererTypeNonEmpty(&$row)
    {
        if (empty($row['referer_type'])) {
            $row['referer_type'] = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
        }
    }

    protected function aggregateVisit($row)
    {
        switch ($row['referer_type']) {
            case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                if (empty($row['referer_keyword'])) {
                    $row['referer_keyword'] = Piwik_Referers_API::LABEL_KEYWORD_NOT_DEFINED;
                }
                $searchEnginesArray = $this->getDataArray(self::SEARCH_ENGINES_RECORD_NAME);
                $searchEnginesArray->sumMetricsVisits($row['referer_name'], $row);
                $searchEnginesArray->sumMetricsVisitsPivot($row['referer_name'], $row['referer_keyword'], $row);
                $keywordsDataArray = $this->getDataArray(self::KEYWORDS_RECORD_NAME);
                $keywordsDataArray->sumMetricsVisits($row['referer_keyword'], $row);
                $keywordsDataArray->sumMetricsVisitsPivot($row['referer_keyword'], $row['referer_name'], $row);
                break;

            case Piwik_Common::REFERER_TYPE_WEBSITE:
                $this->getDataArray(self::WEBSITES_RECORD_NAME)->sumMetricsVisits($row['referer_name'], $row);
                $this->getDataArray(self::WEBSITES_RECORD_NAME)->sumMetricsVisitsPivot($row['referer_name'], $row['referer_url'], $row);

                $urlHash = substr(md5($row['referer_url']), 0, 10);
                if (!isset($this->distinctUrls[$urlHash])) {
                    $this->distinctUrls[$urlHash] = true;
                }
                break;

            case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                if (!empty($row['referer_keyword'])) {
                    $this->getDataArray(self::CAMPAIGNS_RECORD_NAME)->sumMetricsVisitsPivot($row['referer_name'], $row['referer_keyword'], $row);
                }
                $this->getDataArray(self::CAMPAIGNS_RECORD_NAME)->sumMetricsVisits($row['referer_name'], $row);
                break;

            case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
                // direct entry are aggregated below in $this->metricsByType array
                break;

            default:
                throw new Exception("Non expected referer_type = " . $row['referer_type']);
                break;
        }
        $this->getDataArray(self::REFERER_TYPE_RECORD_NAME)->sumMetricsVisits($row['referer_type'], $row);
    }

    /**
     * @param $name
     * @return Piwik_DataArray
     */
    protected function getDataArray($name)
    {
        return $this->arrays[$name];
    }

    protected function aggregateFromConversions($query)
    {
        if ($query === false) {
            return;
        }
        while ($row = $query->fetch()) {
            $this->makeRefererTypeNonEmpty($row);

            $skipAggregateByType = $this->aggregateConversion($row);
            if (!$skipAggregateByType) {
                $this->getDataArray(self::REFERER_TYPE_RECORD_NAME)->sumMetricsGoals($row['referer_type'], $row);
            }
        }

        foreach ($this->arrays as $dataArray) {
            /* @var Piwik_DataArray $dataArray */
            $dataArray->enrichMetricsWithConversions();
        }
    }

    protected function aggregateConversion($row)
    {
        $skipAggregateByType = false;
        switch ($row['referer_type']) {
            case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                if (empty($row['referer_keyword'])) {
                    $row['referer_keyword'] = Piwik_Referers_API::LABEL_KEYWORD_NOT_DEFINED;
                }

                $this->getDataArray(self::SEARCH_ENGINES_RECORD_NAME)->sumMetricsGoals($row['referer_name'], $row);
                $this->getDataArray(self::KEYWORDS_RECORD_NAME)->sumMetricsGoals($row['referer_keyword'], $row);
                break;

            case Piwik_Common::REFERER_TYPE_WEBSITE:
                $this->getDataArray(self::WEBSITES_RECORD_NAME)->sumMetricsGoals($row['referer_name'], $row);
                break;

            case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                if (!empty($row['referer_keyword'])) {
                    $this->getDataArray(self::CAMPAIGNS_RECORD_NAME)->sumMetricsGoalsPivot($row['referer_name'], $row['referer_keyword'], $row);
                }
                $this->getDataArray(self::CAMPAIGNS_RECORD_NAME)->sumMetricsGoals($row['referer_name'], $row);
                break;

            case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
                // Direct entry, no sub dimension
                break;

            default:
                // The referer type is user submitted for goal conversions, we ignore any malformed value
                // Continue to the next while iteration
                $skipAggregateByType = true;
                break;
        }
        return $skipAggregateByType;
    }

    /**
     * Records the daily stats (numeric or datatable blob) into the archive tables.
     *
     * @param Piwik_ArchiveProcessor $this->getProcessor()
     */
    protected function recordDayReports()
    {
        $this->recordDayNumeric();
        $this->recordDayBlobs();
    }

    protected function recordDayNumeric()
    {
        $numericRecords = array(
            self::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME => count($this->getDataArray(self::SEARCH_ENGINES_RECORD_NAME)),
            self::METRIC_DISTINCT_KEYWORD_RECORD_NAME       => count($this->getDataArray(self::KEYWORDS_RECORD_NAME)),
            self::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME      => count($this->getDataArray(self::CAMPAIGNS_RECORD_NAME)),
            self::METRIC_DISTINCT_WEBSITE_RECORD_NAME       => count($this->getDataArray(self::WEBSITES_RECORD_NAME)),
            self::METRIC_DISTINCT_URLS_RECORD_NAME          => count($this->distinctUrls),
        );

        $this->getProcessor()->insertNumericRecords($numericRecords);
    }

    protected function recordDayBlobs()
    {
        foreach ($this->getRecordNames() as $recordName) {
            $dataArray = $this->getDataArray($recordName);
            $table = $this->getProcessor()->getDataTableFromDataArray($dataArray);
            $blob = $table->getSerialized($this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation);
            $this->getProcessor()->insertBlobRecord($recordName, $blob);
        }
    }

    public function archivePeriod()
    {
        $dataTableToSum = $this->getRecordNames();
        $nameToCount = $this->getProcessor()->aggregateDataTableReports($dataTableToSum, $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation);

        $mappingFromArchiveName = array(
            self::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME =>
            array('typeCountToUse' => 'level0',
                  'nameTableToUse' => self::SEARCH_ENGINES_RECORD_NAME,
            ),
            self::METRIC_DISTINCT_KEYWORD_RECORD_NAME       =>
            array('typeCountToUse' => 'level0',
                  'nameTableToUse' => self::KEYWORDS_RECORD_NAME,
            ),
            self::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME      =>
            array('typeCountToUse' => 'level0',
                  'nameTableToUse' => self::CAMPAIGNS_RECORD_NAME,
            ),
            self::METRIC_DISTINCT_WEBSITE_RECORD_NAME       =>
            array('typeCountToUse' => 'level0',
                  'nameTableToUse' => self::WEBSITES_RECORD_NAME,
            ),
            self::METRIC_DISTINCT_URLS_RECORD_NAME          =>
            array('typeCountToUse' => 'recursive',
                  'nameTableToUse' => self::WEBSITES_RECORD_NAME,
            ),
        );

        foreach ($mappingFromArchiveName as $name => $infoMapping) {
            $typeCountToUse = $infoMapping['typeCountToUse'];
            $nameTableToUse = $infoMapping['nameTableToUse'];

            if ($typeCountToUse == 'recursive') {

                $countValue = $nameToCount[$nameTableToUse]['recursive']
                    - $nameToCount[$nameTableToUse]['level0'];
            } else {
                $countValue = $nameToCount[$nameTableToUse]['level0'];
            }
            $this->getProcessor()->insertNumericRecord($name, $countValue);
        }
    }
}