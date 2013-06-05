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

class Piwik_Referers_Archiving
{
    protected $columnToSortByBeforeTruncation;
    protected $maximumRowsInDataTableLevelZero;
    protected $maximumRowsInSubDataTable;

    function __construct()
    {
        $this->columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
        $this->maximumRowsInDataTableLevelZero = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_referers'];
        $this->maximumRowsInSubDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_referers'];
    }

    /**
     * @param $archiveProcessing
     */
    public function archiveDay($archiveProcessing)
    {
        $query = $archiveProcessing->queryVisitsByDimension(array("referer_type", "referer_name", "referer_keyword", "referer_url"));
        $this->aggregateFromVisits($archiveProcessing, $query);

        $query = $archiveProcessing->queryConversionsByDimension(array("referer_type", "referer_name", "referer_keyword"));
        $this->aggregateFromConversions($archiveProcessing, $query);

        Piwik_PostEvent('Referers.archiveDay', $this);
        $this->archiveDayRecordInDatabase($archiveProcessing);
    }

    /**
     * @param Piwik_ArchiveProcessing_Day $archiveProcessing
     * @param $query
     * @throws Exception
     */
    protected function aggregateFromVisits(Piwik_ArchiveProcessing_Day $archiveProcessing, $query)
    {
        $this->metricsBySearchEngine =
        $this->metricsByKeyword =
        $this->metricsBySearchEngineAndKeyword =
        $this->metricsByKeywordAndSearchEngine =
        $this->metricsByWebsite =
        $this->metricsByWebsiteAndUrl =
        $this->metricsByCampaignAndKeyword =
        $this->metricsByCampaign =
        $this->metricsByType =
        $this->distinctUrls = array();
        while ($row = $query->fetch()) {
            $this->makeRefererTypeNonEmpty($row);
            $this->aggregateVisit($archiveProcessing, $row);
            $this->aggregateVisitByType($archiveProcessing, $row);

        }
    }

    protected function makeRefererTypeNonEmpty(&$row)
    {
        if (empty($row['referer_type'])) {
            $row['referer_type'] = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
        }
    }

    protected function aggregateVisit(Piwik_ArchiveProcessing_Day $archiveProcessing, $row)
    {
        switch ($row['referer_type']) {
            case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                $this->aggregateVisitBySearchEngine($archiveProcessing, $row);
                break;

            case Piwik_Common::REFERER_TYPE_WEBSITE:
                $this->aggregateVisitByWebsite($archiveProcessing, $row);
                break;

            case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                $this->aggregateVisitByCampaign($archiveProcessing, $row);
                break;

            case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
                // direct entry are aggregated below in $this->metricsByType array
                break;

            default:
                throw new Exception("Non expected referer_type = " . $row['referer_type']);
                break;
        }
    }

    protected function aggregateVisitBySearchEngine(Piwik_ArchiveProcessing_Day $archiveProcessing, $row)
    {
        if (empty($row['referer_keyword'])) {
            $row['referer_keyword'] = Piwik_Referers_API::LABEL_KEYWORD_NOT_DEFINED;
        }
        if (!isset($this->metricsBySearchEngine[$row['referer_name']])) {
            $this->metricsBySearchEngine[$row['referer_name']] = $archiveProcessing->makeEmptyRow();
        }
        if (!isset($this->metricsByKeyword[$row['referer_keyword']])) {
            $this->metricsByKeyword[$row['referer_keyword']] = $archiveProcessing->makeEmptyRow();
        }
        if (!isset($this->metricsBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']])) {
            $this->metricsBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']] = $archiveProcessing->makeEmptyRow();
        }
        if (!isset($this->metricsByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']])) {
            $this->metricsByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']] = $archiveProcessing->makeEmptyRow();
        }

        $archiveProcessing->sumMetrics($row, $this->metricsBySearchEngine[$row['referer_name']]);
        $archiveProcessing->sumMetrics($row, $this->metricsByKeyword[$row['referer_keyword']]);
        $archiveProcessing->sumMetrics($row, $this->metricsBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']]);
        $archiveProcessing->sumMetrics($row, $this->metricsByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']]);
    }

    protected function aggregateVisitByWebsite(Piwik_ArchiveProcessing_Day $archiveProcessing, $row)
    {
        if (!isset($this->metricsByWebsite[$row['referer_name']])) {
            $this->metricsByWebsite[$row['referer_name']] = $archiveProcessing->makeEmptyRow();
        }
        $archiveProcessing->sumMetrics($row, $this->metricsByWebsite[$row['referer_name']]);

        if (!isset($this->metricsByWebsiteAndUrl[$row['referer_name']][$row['referer_url']])) {
            $this->metricsByWebsiteAndUrl[$row['referer_name']][$row['referer_url']] = $archiveProcessing->makeEmptyRow();
        }
        $archiveProcessing->sumMetrics($row, $this->metricsByWebsiteAndUrl[$row['referer_name']][$row['referer_url']]);

        $urlHash = substr(md5($row['referer_url']), 0, 10);
        if (!isset($this->distinctUrls[$urlHash])) {
            $this->distinctUrls[$urlHash] = true;
        }
    }

    protected function aggregateVisitByCampaign(Piwik_ArchiveProcessing_Day $archiveProcessing, $row)
    {
        if (!empty($row['referer_keyword'])) {
            if (!isset($this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']])) {
                $this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']] = $archiveProcessing->makeEmptyRow();
            }
            $archiveProcessing->sumMetrics($row, $this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']]);
        }
        if (!isset($this->metricsByCampaign[$row['referer_name']])) {
            $this->metricsByCampaign[$row['referer_name']] = $archiveProcessing->makeEmptyRow();
        }
        $archiveProcessing->sumMetrics($row, $this->metricsByCampaign[$row['referer_name']]);
    }

    protected function aggregateVisitByType(Piwik_ArchiveProcessing_Day $archiveProcessing, $row)
    {
        if (!isset($this->metricsByType[$row['referer_type']])) {
            $this->metricsByType[$row['referer_type']] = $archiveProcessing->makeEmptyRow();
        }
        $archiveProcessing->sumMetrics($row, $this->metricsByType[$row['referer_type']]);
    }

    protected function aggregateFromConversions($archiveProcessing, $query)
    {
        if ($query === false) {
            return;
        }
        while ($row = $query->fetch()) {
            $this->makeRefererTypeNonEmpty($row);

            $skipAggregateByType = $this->aggregateConversion($archiveProcessing, $row);
            if (!$skipAggregateByType) {
                $this->aggregateConversionByType($archiveProcessing, $row);
            }
        }

        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByType);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsBySearchEngine);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByKeyword);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByWebsite);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByCampaign);
        $archiveProcessing->enrichConversionsByLabelArrayHasTwoLevels($this->metricsByCampaignAndKeyword);
    }

    protected function aggregateConversion($archiveProcessing, $row)
    {
        $skipAggregateByType = false;
        switch ($row['referer_type']) {
            case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                $this->aggregateConversionBySearchEngine($archiveProcessing, $row);
                break;

            case Piwik_Common::REFERER_TYPE_WEBSITE:
                $this->aggregateConversionByWebsite($archiveProcessing, $row);
                break;

            case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                $this->aggregateConversionByCampaign($archiveProcessing, $row);
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

    protected function aggregateConversionBySearchEngine($archiveProcessing, $row)
    {
        if (empty($row['referer_keyword'])) {
            $row['referer_keyword'] = Piwik_Referers_API::LABEL_KEYWORD_NOT_DEFINED;
        }
        if (!isset($this->metricsBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) {
            $this->metricsBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
        }
        if (!isset($this->metricsByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) {
            $this->metricsByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
        }

        $archiveProcessing->sumGoalMetrics($row, $this->metricsBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
        $archiveProcessing->sumGoalMetrics($row, $this->metricsByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
    }

    protected function aggregateConversionByWebsite($archiveProcessing, $row)
    {
        if (!isset($this->metricsByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) {
            $this->metricsByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
        }
        $archiveProcessing->sumGoalMetrics($row, $this->metricsByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
    }

    protected function aggregateConversionByCampaign($archiveProcessing, $row)
    {
        if (!empty($row['referer_keyword'])) {
            if (!isset($this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) {
                $this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
            }
            $archiveProcessing->sumGoalMetrics($row, $this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
        }
        if (!isset($this->metricsByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) {
            $this->metricsByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
        }
        $archiveProcessing->sumGoalMetrics($row, $this->metricsByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
    }

    protected function aggregateConversionByType($archiveProcessing, $row)
    {
        if (!isset($this->metricsByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) {
            $this->metricsByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
        }
        $archiveProcessing->sumGoalMetrics($row, $this->metricsByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
    }

    /**
     * Records the daily stats (numeric or datatable blob) into the archive tables.
     *
     * @param Piwik_ArchiveProcessing $archiveProcessing
     * @return void
     */
    protected function archiveDayRecordInDatabase($archiveProcessing)
    {
        $numericRecords = array(
            'Referers_distinctSearchEngines' => count($this->metricsBySearchEngineAndKeyword),
            'Referers_distinctKeywords'      => count($this->metricsByKeywordAndSearchEngine),
            'Referers_distinctCampaigns'     => count($this->metricsByCampaign),
            'Referers_distinctWebsites'      => count($this->metricsByWebsite),
            'Referers_distinctWebsitesUrls'  => count($this->distinctUrls),
        );

        foreach ($numericRecords as $name => $value) {
            $archiveProcessing->insertNumericRecord($name, $value);
        }

        $dataTable = $archiveProcessing->getDataTableSerialized($this->metricsByType);
        $archiveProcessing->insertBlobRecord('Referers_type', $dataTable);
        destroy($dataTable);

        $blobRecords = array(
            'Referers_keywordBySearchEngine' => $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->metricsBySearchEngineAndKeyword, $this->metricsBySearchEngine),
            'Referers_searchEngineByKeyword' => $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->metricsByKeywordAndSearchEngine, $this->metricsByKeyword),
            'Referers_keywordByCampaign'     => $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->metricsByCampaignAndKeyword, $this->metricsByCampaign),
            'Referers_urlByWebsite'          => $archiveProcessing->getDataTableWithSubtablesFromArraysIndexedByLabel($this->metricsByWebsiteAndUrl, $this->metricsByWebsite),
        );
        foreach ($blobRecords as $recordName => $table) {
            $blob = $table->getSerialized($this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation);
            $archiveProcessing->insertBlobRecord($recordName, $blob);
            destroy($table);
        }
    }

    /**
     * @param $archiveProcessing
     */
    public function archivePeriod($archiveProcessing)
    {
        $dataTableToSum = array(
            'Referers_type',
            'Referers_keywordBySearchEngine',
            'Referers_searchEngineByKeyword',
            'Referers_keywordByCampaign',
            'Referers_urlByWebsite',
        );
        $nameToCount = $archiveProcessing->archiveDataTable($dataTableToSum, null, $this->maximumRowsInDataTableLevelZero, $this->maximumRowsInSubDataTable, $this->columnToSortByBeforeTruncation);

        $mappingFromArchiveName = array(
            'Referers_distinctSearchEngines' =>
            array('typeCountToUse' => 'level0',
                  'nameTableToUse' => 'Referers_keywordBySearchEngine',
            ),
            'Referers_distinctKeywords'      =>
            array('typeCountToUse' => 'level0',
                  'nameTableToUse' => 'Referers_searchEngineByKeyword',
            ),
            'Referers_distinctCampaigns'     =>
            array('typeCountToUse' => 'level0',
                  'nameTableToUse' => 'Referers_keywordByCampaign',
            ),
            'Referers_distinctWebsites'      =>
            array('typeCountToUse' => 'level0',
                  'nameTableToUse' => 'Referers_urlByWebsite',
            ),
            'Referers_distinctWebsitesUrls'  =>
            array('typeCountToUse' => 'recursive',
                  'nameTableToUse' => 'Referers_urlByWebsite',
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
            $archiveProcessing->insertNumericRecord($name, $countValue);
        }
    }
}