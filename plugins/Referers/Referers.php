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
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('Referers_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
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
        if (Piwik::isSegmentationEnabled()) {
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

    /**
     * Hooks on daily archive to trigger various log processing
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function archiveDay($notification)
    {
        $archiveProcessor = $notification->getNotificationObject();

        $archiving = new Piwik_Referers_Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    /**
     * Period archiving: sums up daily stats and sums report tables,
     * making sure that tables are still truncated.
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    function archivePeriod($notification)
    {
        $archiveProcessor = $notification->getNotificationObject();
        $archiving = new Piwik_Referers_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }
}
