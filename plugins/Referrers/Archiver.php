<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataArray;
use Piwik\Metrics;

class Archiver extends \Piwik\Plugin\Archiver
{
    const SEARCH_ENGINES_RECORD_NAME = 'Referrers_keywordBySearchEngine';
    const SOCIAL_NETWORKS_RECORD_NAME = 'Referrers_urlBySocialNetwork';
    const KEYWORDS_RECORD_NAME = 'Referrers_searchEngineByKeyword';
    const CAMPAIGNS_RECORD_NAME = 'Referrers_keywordByCampaign';
    const WEBSITES_RECORD_NAME = 'Referrers_urlByWebsite';
    const REFERRER_TYPE_RECORD_NAME = 'Referrers_type';
    const METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME = 'Referrers_distinctSearchEngines';
    const METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME = 'Referrers_distinctSocialNetworks';
    const METRIC_DISTINCT_KEYWORD_RECORD_NAME = 'Referrers_distinctKeywords';
    const METRIC_DISTINCT_CAMPAIGN_RECORD_NAME = 'Referrers_distinctCampaigns';
    const METRIC_DISTINCT_WEBSITE_RECORD_NAME = 'Referrers_distinctWebsites';
    const METRIC_DISTINCT_URLS_RECORD_NAME = 'Referrers_distinctWebsitesUrls';

    protected $columnToSortByBeforeTruncation;
    protected $maximumRowsInDataTableLevelZero;
    protected $maximumRowsInSubDataTable;
    /**
     * @var DataArray[] $arrays
     */
    protected $arrays = array();
    protected $distinctUrls = array();

    /**
     * @param string $name
     * @return DataArray
     */
    protected function getDataArray($name)
    {
        return $this->arrays[$name];
    }

    /**
     * Records the daily stats (numeric or datatable blob) into the archive tables.
     */
    protected function insertDayReports()
    {
        $this->insertDayNumericMetrics();

        // insert DataTable reports
        foreach ($this->getRecordNames() as $recordName) {
            $blob = $this->getDataArray($recordName)->asDataTable()->getSerialized($this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation);
            $this->getProcessor()->insertBlobRecord($recordName, $blob);
        }
    }

    protected function insertDayNumericMetrics()
    {

        $this->getProcessor()->insertNumericRecords($numericRecords);
    }

    public function aggregateMultipleReports()
    {
        $dataTableToSum = $this->getRecordNames();
        $columnsAggregationOperation = null;
        $nameToCount = $this->getProcessor()->aggregateDataTableRecords(
            $dataTableToSum,
            $this->maximumRowsInDataTableLevelZero,
            $this->maximumRowsInSubDataTable,
            $this->columnToSortByBeforeTruncation,
            $columnsAggregationOperation,
            $columnsToRenameAfterAggregation = null,
            $countRowsRecursive = array(self::WEBSITES_RECORD_NAME)
        );

        $mappingFromArchiveName = array(
            self::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME =>
                array('typeCountToUse' => 'level0',
                      'nameTableToUse' => self::SEARCH_ENGINES_RECORD_NAME,
                ),
            self::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME =>
                array('typeCountToUse' => 'level0',
                      'nameTableToUse' => self::SOCIAL_NETWORKS_RECORD_NAME,
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
            $nameTableToUse = $infoMapping['nameTableToUse'];

            if ($infoMapping['typeCountToUse'] == 'recursive') {
                $countValue = $nameToCount[$nameTableToUse]['recursive'] - $nameToCount[$nameTableToUse]['level0'];
            } else {
                $countValue = $nameToCount[$nameTableToUse]['level0'];
            }
            $this->getProcessor()->insertNumericRecord($name, $countValue);
        }
    }
}
