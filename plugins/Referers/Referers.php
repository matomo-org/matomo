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

/**
 * @see plugins/Referers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referers/functions.php';

/**
 * @package Piwik_Referers
 */
class Piwik_Referers extends Piwik_Plugin
{
    public $archiveProcessing;
    protected $columnToSortByBeforeTruncation;
    protected $maximumRowsInDataTableLevelZero;
    protected $maximumRowsInSubDataTable;

    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('Referers_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );

        return $info;
    }

    function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'    => 'archiveDay',
            'ArchiveProcessing_Period.compute' => 'archivePeriod',
            'WidgetsList.add'                  => 'addWidgets',
            'Menu.add'                         => 'addMenus',
            'Goals.getReportsWithGoalMetrics'  => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'            => 'getReportMetadata',
            'API.getSegmentsMetadata'          => 'getSegmentsMetadata',
        );
        return $hooks;
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $reports = & $notification->getNotificationObject();
        $reports = array_merge($reports, array(
                                              array(
                                                  'category'          => Piwik_Translate('Referers_Referers'),
                                                  'name'              => Piwik_Translate('Referers_Type'),
                                                  'module'            => 'Referers',
                                                  'action'            => 'getRefererType',
                                                  'dimension'         => Piwik_Translate('Referers_ColumnRefererType'),
                                                  'constantRowsCount' => true,
                                                  'documentation'     => Piwik_Translate('Referers_TypeReportDocumentation') . '<br />'
                                                      . '<b>' . Piwik_Translate('Referers_DirectEntry') . ':</b> ' . Piwik_Translate('Referers_DirectEntryDocumentation') . '<br />'
                                                      . '<b>' . Piwik_Translate('Referers_SearchEngines') . ':</b> ' . Piwik_Translate('Referers_SearchEnginesDocumentation',
                                                      array('<br />', '&quot;' . Piwik_Translate('Referers_SubmenuSearchEngines') . '&quot;')) . '<br />'
                                                      . '<b>' . Piwik_Translate('Referers_Websites') . ':</b> ' . Piwik_Translate('Referers_WebsitesDocumentation',
                                                      array('<br />', '&quot;' . Piwik_Translate('Referers_SubmenuWebsites') . '&quot;')) . '<br />'
                                                      . '<b>' . Piwik_Translate('Referers_Campaigns') . ':</b> ' . Piwik_Translate('Referers_CampaignsDocumentation',
                                                      array('<br />', '&quot;' . Piwik_Translate('Referers_SubmenuCampaigns') . '&quot;')),
                                                  'order'             => 1,
                                              ),
                                              array(
                                                  'category'      => Piwik_Translate('Referers_Referers'),
                                                  'name'          => Piwik_Translate('Referers_WidgetGetAll'),
                                                  'module'        => 'Referers',
                                                  'action'        => 'getAll',
                                                  'dimension'     => Piwik_Translate('Referers_Referrer'),
                                                  'documentation' => Piwik_Translate('Referers_AllReferersReportDocumentation', '<br />'),
                                                  'order'         => 2,
                                              ),
                                              array(
                                                  'category'              => Piwik_Translate('Referers_Referers'),
                                                  'name'                  => Piwik_Translate('Referers_Keywords'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getKeywords',
                                                  'actionToLoadSubTables' => 'getSearchEnginesFromKeywordId',
                                                  'dimension'             => Piwik_Translate('Referers_ColumnKeyword'),
                                                  'documentation'         => Piwik_Translate('Referers_KeywordsReportDocumentation', '<br />'),
                                                  'order'                 => 3,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik_Translate('Referers_Referers'),
                                                  'name'             => Piwik_Translate('Referers_Keywords'),
                                                  'module'           => 'Referers',
                                                  'action'           => 'getSearchEnginesFromKeywordId',
                                                  'dimension'        => Piwik_Translate('Referers_ColumnSearchEngine'),
                                                  'documentation'    => Piwik_Translate('Referers_KeywordsReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 4
                                              ),

                                              array(
                                                  'category'              => Piwik_Translate('Referers_Referers'),
                                                  'name'                  => Piwik_Translate('Referers_Websites'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getWebsites',
                                                  'dimension'             => Piwik_Translate('Referers_ColumnWebsite'),
                                                  'documentation'         => Piwik_Translate('Referers_WebsitesReportDocumentation', '<br />'),
                                                  'actionToLoadSubTables' => 'getUrlsFromWebsiteId',
                                                  'order'                 => 5
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik_Translate('Referers_Referers'),
                                                  'name'             => Piwik_Translate('Referers_Websites'),
                                                  'module'           => 'Referers',
                                                  'action'           => 'getUrlsFromWebsiteId',
                                                  'dimension'        => Piwik_Translate('Referers_ColumnWebsitePage'),
                                                  'documentation'    => Piwik_Translate('Referers_WebsitesReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 6,
                                              ),

                                              array(
                                                  'category'              => Piwik_Translate('Referers_Referers'),
                                                  'name'                  => Piwik_Translate('Referers_SearchEngines'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getSearchEngines',
                                                  'dimension'             => Piwik_Translate('Referers_ColumnSearchEngine'),
                                                  'documentation'         => Piwik_Translate('Referers_SearchEnginesReportDocumentation', '<br />'),
                                                  'actionToLoadSubTables' => 'getKeywordsFromSearchEngineId',
                                                  'order'                 => 7,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik_Translate('Referers_Referers'),
                                                  'name'             => Piwik_Translate('Referers_SearchEngines'),
                                                  'module'           => 'Referers',
                                                  'action'           => 'getKeywordsFromSearchEngineId',
                                                  'dimension'        => Piwik_Translate('Referers_ColumnKeyword'),
                                                  'documentation'    => Piwik_Translate('Referers_SearchEnginesReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 8,
                                              ),

                                              array(
                                                  'category'              => Piwik_Translate('Referers_Referers'),
                                                  'name'                  => Piwik_Translate('Referers_Campaigns'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getCampaigns',
                                                  'dimension'             => Piwik_Translate('Referers_ColumnCampaign'),
                                                  'documentation'         => Piwik_Translate('Referers_CampaignsReportDocumentation',
                                                      array('<br />', '<a href="http://piwik.org/docs/tracking-campaigns/" target="_blank">', '</a>')),
                                                  'actionToLoadSubTables' => 'getKeywordsFromCampaignId',
                                                  'order'                 => 9,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik_Translate('Referers_Referers'),
                                                  'name'             => Piwik_Translate('Referers_Campaigns'),
                                                  'module'           => 'Referers',
                                                  'action'           => 'getKeywordsFromCampaignId',
                                                  'dimension'        => Piwik_Translate('Referers_ColumnKeyword'),
                                                  'documentation'    => Piwik_Translate('Referers_CampaignsReportDocumentation',
                                                      array('<br />', '<a href="http://piwik.org/docs/tracking-campaigns/" target="_blank">', '</a>')),
                                                  'isSubtableReport' => true,
                                                  'order'            => 10,
                                              ),
                                              array(
                                                  'category'              => Piwik_Translate('Referers_Referers'),
                                                  'name'                  => Piwik_Translate('Referers_Socials'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getSocials',
                                                  'actionToLoadSubTables' => 'getUrlsForSocial',
                                                  'dimension'             => Piwik_Translate('Referers_ColumnSocial'),
                                                  'documentation'         => Piwik_Translate('Referers_WebsitesReportDocumentation', '<br />'),
                                                  'order'                 => 11,
                                              ),
                                         ));
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getSegmentsMetadata($notification)
    {
        $segments =& $notification->getNotificationObject();
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Referers_Referers',
            'name'           => 'Referers_ColumnRefererType',
            'segment'        => 'referrerType',
            'acceptedValues' => 'direct, search, website, campaign',
            'sqlSegment'     => 'log_visit.referer_type',
            'sqlFilter'      => 'Piwik_getRefererTypeFromShortName',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Referers_Referers',
            'name'           => 'Referers_ColumnKeyword',
            'segment'        => 'referrerKeyword',
            'acceptedValues' => 'Encoded%20Keyword, keyword',
            'sqlSegment'     => 'log_visit.referer_keyword',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Referers_Referers',
            'name'           => 'Referers_RefererName',
            'segment'        => 'referrerName',
            'acceptedValues' => 'twitter.com, www.facebook.com, Bing, Google, Yahoo, CampaignName',
            'sqlSegment'     => 'log_visit.referer_name',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Referers_Referers',
            'name'           => 'Live_Referrer_URL',
            'acceptedValues' => 'http%3A%2F%2Fwww.example.org%2Freferer-page.htm',
            'segment'        => 'referrerUrl',
            'sqlSegment'     => 'log_visit.referer_url',
        );
    }

    /**
     * Adds Referer widgets
     */
    function addWidgets()
    {
        Piwik_AddWidget('Referers_Referers', 'Referers_WidgetKeywords', 'Referers', 'getKeywords');
        Piwik_AddWidget('Referers_Referers', 'Referers_WidgetExternalWebsites', 'Referers', 'getWebsites');
        Piwik_AddWidget('Referers_Referers', 'Referers_WidgetSocials', 'Referers', 'getSocials');
        Piwik_AddWidget('Referers_Referers', 'Referers_WidgetSearchEngines', 'Referers', 'getSearchEngines');
        Piwik_AddWidget('Referers_Referers', 'Referers_WidgetCampaigns', 'Referers', 'getCampaigns');
        Piwik_AddWidget('Referers_Referers', 'Referers_WidgetOverview', 'Referers', 'getRefererType');
        Piwik_AddWidget('Referers_Referers', 'Referers_WidgetGetAll', 'Referers', 'getAll');
        if (Piwik_Archive::isSegmentationEnabled()) {
            Piwik_AddWidget('SEO', 'Referers_WidgetTopKeywordsForPages', 'Referers', 'getKeywordsForPage');
        }
    }

    /**
     * Adds Web Analytics menus
     */
    function addMenus()
    {
        Piwik_AddMenu('Referers_Referers', '', array('module' => 'Referers', 'action' => 'index'), true, 20);
        Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuOverview', array('module' => 'Referers', 'action' => 'index'), true, 1);
        Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuSearchEngines', array('module' => 'Referers', 'action' => 'getSearchEnginesAndKeywords'), true, 2);
        Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuWebsites', array('module' => 'Referers', 'action' => 'indexWebsites'), true, 3);
        Piwik_AddMenu('Referers_Referers', 'Referers_SubmenuCampaigns', array('module' => 'Referers', 'action' => 'indexCampaigns'), true, 4);
    }

    /**
     * Adds Goal dimensions, so that the dimensions are displayed in the UI Goal Overview page
     *
     * @param Piwik_Event_Notification $notification  notification object
     * @return void
     */
    function getReportsWithGoalMetrics($notification)
    {
        $dimensions =& $notification->getNotificationObject();
        $dimensions = array_merge($dimensions, array(
                                                    array('category' => Piwik_Translate('Referers_Referers'),
                                                          'name'     => Piwik_Translate('Referers_Keywords'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getKeywords',
                                                    ),
                                                    array('category' => Piwik_Translate('Referers_Referers'),
                                                          'name'     => Piwik_Translate('Referers_SearchEngines'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getSearchEngines',
                                                    ),
                                                    array('category' => Piwik_Translate('Referers_Referers'),
                                                          'name'     => Piwik_Translate('Referers_Websites'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getWebsites',
                                                    ),
                                                    array('category' => Piwik_Translate('Referers_Referers'),
                                                          'name'     => Piwik_Translate('Referers_Campaigns'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getCampaigns',
                                                    ),
                                                    array('category' => Piwik_Translate('Referers_Referers'),
                                                          'name'     => Piwik_Translate('Referers_Type'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getRefererType',
                                                    ),
                                               ));
    }

    function __construct()
    {
        $this->columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
        $this->maximumRowsInDataTableLevelZero = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_referers'];
        $this->maximumRowsInSubDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_referers'];
    }

    /**
     * Period archiving: sums up daily stats and sums report tables,
     * making sure that tables are still truncated.
     *
     * @param Piwik_Event_Notification $notification  notification object
     * @return void
     */
    function archivePeriod($notification)
    {
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

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

    const LABEL_KEYWORD_NOT_DEFINED = "";

    static public function getKeywordNotDefinedString()
    {
        return Piwik_Translate('General_NotDefined', Piwik_Translate('Referers_ColumnKeyword'));
    }

    static public function getCleanKeyword($label)
    {
        return $label == Piwik_Referers::LABEL_KEYWORD_NOT_DEFINED
            ? self::getKeywordNotDefinedString()
            : $label;
    }

    /**
     * Hooks on daily archive to trigger various log processing
     *
     * @param Piwik_Event_Notification $notification  notification object
     * @return void
     */
    public function archiveDay($notification)
    {
        /**
         * @var Piwik_ArchiveProcessing_Day
         */
        $this->archiveProcessing = $notification->getNotificationObject();
        if (!$this->archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $this->archiveDayAggregateVisits($this->archiveProcessing);
        $this->archiveDayAggregateGoals($this->archiveProcessing);
        Piwik_PostEvent('Referers.archiveDay', $this);
        $this->archiveDayRecordInDatabase($this->archiveProcessing);
        $this->cleanup();
    }

    protected function cleanup()
    {
        destroy($this->metricsBySearchEngine);
        destroy($this->metricsByKeyword);
        destroy($this->metricsBySearchEngineAndKeyword);
        destroy($this->metricsByKeywordAndSearchEngine);
        destroy($this->metricsByWebsite);
        destroy($this->metricsByWebsiteAndUrl);
        destroy($this->metricsByCampaignAndKeyword);
        destroy($this->metricsByCampaign);
        destroy($this->metricsByType);
        destroy($this->distinctUrls);
    }

    /**
     * Daily archive: processes all Referers reports, eg. Visits by Keyword,
     * Visits by websites, etc.
     *
     * @param Piwik_ArchiveProcessing $archiveProcessing
     * @throws Exception
     * @return void
     */
    protected function archiveDayAggregateVisits(Piwik_ArchiveProcessing_Day $archiveProcessing)
    {
        $dimension = array("referer_type", "referer_name", "referer_keyword", "referer_url");
        $query = $archiveProcessing->queryVisitsByDimension($dimension);

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
            if (empty($row['referer_type'])) {
                $row['referer_type'] = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
            } else {
                switch ($row['referer_type']) {
                    case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                        if (empty($row['referer_keyword'])) {
                            $row['referer_keyword'] = self::LABEL_KEYWORD_NOT_DEFINED;
                        }
                        if (!isset($this->metricsBySearchEngine[$row['referer_name']])) $this->metricsBySearchEngine[$row['referer_name']] = $archiveProcessing->makeEmptyRow();
                        if (!isset($this->metricsByKeyword[$row['referer_keyword']])) $this->metricsByKeyword[$row['referer_keyword']] = $archiveProcessing->makeEmptyRow();
                        if (!isset($this->metricsBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']])) $this->metricsBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']] = $archiveProcessing->makeEmptyRow();
                        if (!isset($this->metricsByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']])) $this->metricsByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']] = $archiveProcessing->makeEmptyRow();

                        $archiveProcessing->sumMetrics($row, $this->metricsBySearchEngine[$row['referer_name']]);
                        $archiveProcessing->sumMetrics($row, $this->metricsByKeyword[$row['referer_keyword']]);
                        $archiveProcessing->sumMetrics($row, $this->metricsBySearchEngineAndKeyword[$row['referer_name']][$row['referer_keyword']]);
                        $archiveProcessing->sumMetrics($row, $this->metricsByKeywordAndSearchEngine[$row['referer_keyword']][$row['referer_name']]);
                        break;

                    case Piwik_Common::REFERER_TYPE_WEBSITE:

                        if (!isset($this->metricsByWebsite[$row['referer_name']])) $this->metricsByWebsite[$row['referer_name']] = $archiveProcessing->makeEmptyRow();
                        $archiveProcessing->sumMetrics($row, $this->metricsByWebsite[$row['referer_name']]);

                        if (!isset($this->metricsByWebsiteAndUrl[$row['referer_name']][$row['referer_url']])) $this->metricsByWebsiteAndUrl[$row['referer_name']][$row['referer_url']] = $archiveProcessing->makeEmptyRow();
                        $archiveProcessing->sumMetrics($row, $this->metricsByWebsiteAndUrl[$row['referer_name']][$row['referer_url']]);

                        if (!isset($this->distinctUrls[$row['referer_url']])) {
                            $this->distinctUrls[$row['referer_url']] = true;
                        }
                        break;

                    case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                        if (!empty($row['referer_keyword'])) {
                            if (!isset($this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']])) $this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']] = $archiveProcessing->makeEmptyRow();
                            $archiveProcessing->sumMetrics($row, $this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']]);
                        }
                        if (!isset($this->metricsByCampaign[$row['referer_name']])) $this->metricsByCampaign[$row['referer_name']] = $archiveProcessing->makeEmptyRow();
                        $archiveProcessing->sumMetrics($row, $this->metricsByCampaign[$row['referer_name']]);
                        break;

                    case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
                        // direct entry are aggregated below in $this->metricsByType array
                        break;

                    default:
                        throw new Exception("Non expected referer_type = " . $row['referer_type']);
                        break;
                }
            }
            if (!isset($this->metricsByType[$row['referer_type']])) $this->metricsByType[$row['referer_type']] = $archiveProcessing->makeEmptyRow();
            $archiveProcessing->sumMetrics($row, $this->metricsByType[$row['referer_type']]);
        }
    }

    /**
     * Daily Goal archiving:  processes reports of Goal conversions by Keyword,
     * Goal conversions by Referer Websites, etc.
     *
     * @param Piwik_ArchiveProcessing $archiveProcessing
     * @return void
     */
    protected function archiveDayAggregateGoals($archiveProcessing)
    {
        $query = $archiveProcessing->queryConversionsByDimension(array("referer_type", "referer_name", "referer_keyword"));

        if ($query === false) return;
        while ($row = $query->fetch()) {
            if (empty($row['referer_type'])) {
                $row['referer_type'] = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
            } else {
                switch ($row['referer_type']) {
                    case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                        if (empty($row['referer_keyword'])) {
                            $row['referer_keyword'] = self::LABEL_KEYWORD_NOT_DEFINED;
                        }
                        if (!isset($this->metricsBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->metricsBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
                        if (!isset($this->metricsByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->metricsByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);

                        $archiveProcessing->sumGoalMetrics($row, $this->metricsBySearchEngine[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
                        $archiveProcessing->sumGoalMetrics($row, $this->metricsByKeyword[$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
                        break;

                    case Piwik_Common::REFERER_TYPE_WEBSITE:
                        if (!isset($this->metricsByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->metricsByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
                        $archiveProcessing->sumGoalMetrics($row, $this->metricsByWebsite[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
                        break;

                    case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                        if (!empty($row['referer_keyword'])) {
                            if (!isset($this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
                            $archiveProcessing->sumGoalMetrics($row, $this->metricsByCampaignAndKeyword[$row['referer_name']][$row['referer_keyword']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
                        }
                        if (!isset($this->metricsByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->metricsByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
                        $archiveProcessing->sumGoalMetrics($row, $this->metricsByCampaign[$row['referer_name']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
                        break;

                    case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
                        // Direct entry, no sub dimension
                        break;

                    default:
                        // The referer type is user submitted for goal conversions, we ignore any malformed value
                        // Continue to the next while iteration
                        continue 2;
                        break;
                }
            }
            if (!isset($this->metricsByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']])) $this->metricsByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->makeEmptyGoalRow($row['idgoal']);
            $archiveProcessing->sumGoalMetrics($row, $this->metricsByType[$row['referer_type']][Piwik_Archive::INDEX_GOALS][$row['idgoal']]);
        }

        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByType);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsBySearchEngine);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByKeyword);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByWebsite);
        $archiveProcessing->enrichConversionsByLabelArray($this->metricsByCampaign);
        $archiveProcessing->enrichConversionsByLabelArrayHasTwoLevels($this->metricsByCampaignAndKeyword);
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
}
