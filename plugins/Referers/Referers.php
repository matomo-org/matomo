<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Referers
 */
namespace Piwik\Plugins\Referers;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\SettingsPiwik;
use Piwik\WidgetsList;

/**
 * @see plugins/Referers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referers/functions.php';

/**
 * @package Referers
 */
class Referers extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'WidgetsList.addWidgets'                   => 'addWidgets',
            'Menu.addMenuEntries'                      => 'addMenus',
            'Goals.getReportsWithGoalMetrics'          => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
        );
        return $hooks;
    }

    public function getReportMetadata(&$reports)
    {
        $reports = array_merge($reports, array(
                                              array(
                                                  'category'          => Piwik_Translate('Referrers_Referrers'),
                                                  'name'              => Piwik_Translate('Referrers_Type'),
                                                  'module'            => 'Referers',
                                                  'action'            => 'getRefererType',
                                                  'dimension'         => Piwik_Translate('Referrers_Type'),
                                                  'constantRowsCount' => true,
                                                  'documentation'     => Piwik_Translate('Referrers_TypeReportDocumentation') . '<br />'
                                                      . '<b>' . Piwik_Translate('Referrers_DirectEntry') . ':</b> ' . Piwik_Translate('Referrers_DirectEntryDocumentation') . '<br />'
                                                      . '<b>' . Piwik_Translate('Referrers_SearchEngines') . ':</b> ' . Piwik_Translate('Referrers_SearchEnginesDocumentation',
                                                      array('<br />', '&quot;' . Piwik_Translate('Referrers_SubmenuSearchEngines') . '&quot;')) . '<br />'
                                                      . '<b>' . Piwik_Translate('Referrers_Websites') . ':</b> ' . Piwik_Translate('Referrers_WebsitesDocumentation',
                                                      array('<br />', '&quot;' . Piwik_Translate('Referrers_SubmenuWebsites') . '&quot;')) . '<br />'
                                                      . '<b>' . Piwik_Translate('Referrers_Campaigns') . ':</b> ' . Piwik_Translate('Referrers_CampaignsDocumentation',
                                                      array('<br />', '&quot;' . Piwik_Translate('Referrers_Campaigns') . '&quot;')),
                                                  'order'             => 1,
                                              ),
                                              array(
                                                  'category'      => Piwik_Translate('Referrers_Referrers'),
                                                  'name'          => Piwik_Translate('Referrers_WidgetGetAll'),
                                                  'module'        => 'Referers',
                                                  'action'        => 'getAll',
                                                  'dimension'     => Piwik_Translate('Referrers_Referrer'),
                                                  'documentation' => Piwik_Translate('Referrers_AllReferrersReportDocumentation', '<br />'),
                                                  'order'         => 2,
                                              ),
                                              array(
                                                  'category'              => Piwik_Translate('Referrers_Referrers'),
                                                  'name'                  => Piwik_Translate('Referrers_Keywords'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getKeywords',
                                                  'actionToLoadSubTables' => 'getSearchEnginesFromKeywordId',
                                                  'dimension'             => Piwik_Translate('General_ColumnKeyword'),
                                                  'documentation'         => Piwik_Translate('Referrers_KeywordsReportDocumentation', '<br />'),
                                                  'order'                 => 3,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik_Translate('Referrers_Referrers'),
                                                  'name'             => Piwik_Translate('Referrers_Keywords'),
                                                  'module'           => 'Referers',
                                                  'action'           => 'getSearchEnginesFromKeywordId',
                                                  'dimension'        => Piwik_Translate('Referrers_ColumnSearchEngine'),
                                                  'documentation'    => Piwik_Translate('Referrers_KeywordsReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 4
                                              ),

                                              array(
                                                  'category'              => Piwik_Translate('Referrers_Referrers'),
                                                  'name'                  => Piwik_Translate('Referrers_Websites'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getWebsites',
                                                  'dimension'             => Piwik_Translate('Referrers_ColumnWebsite'),
                                                  'documentation'         => Piwik_Translate('Referrers_WebsitesReportDocumentation', '<br />'),
                                                  'actionToLoadSubTables' => 'getUrlsFromWebsiteId',
                                                  'order'                 => 5
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik_Translate('Referrers_Referrers'),
                                                  'name'             => Piwik_Translate('Referrers_Websites'),
                                                  'module'           => 'Referers',
                                                  'action'           => 'getUrlsFromWebsiteId',
                                                  'dimension'        => Piwik_Translate('Referrers_ColumnWebsitePage'),
                                                  'documentation'    => Piwik_Translate('Referrers_WebsitesReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 6,
                                              ),

                                              array(
                                                  'category'              => Piwik_Translate('Referrers_Referrers'),
                                                  'name'                  => Piwik_Translate('Referrers_SearchEngines'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getSearchEngines',
                                                  'dimension'             => Piwik_Translate('Referrers_ColumnSearchEngine'),
                                                  'documentation'         => Piwik_Translate('Referrers_SearchEnginesReportDocumentation', '<br />'),
                                                  'actionToLoadSubTables' => 'getKeywordsFromSearchEngineId',
                                                  'order'                 => 7,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik_Translate('Referrers_Referrers'),
                                                  'name'             => Piwik_Translate('Referrers_SearchEngines'),
                                                  'module'           => 'Referers',
                                                  'action'           => 'getKeywordsFromSearchEngineId',
                                                  'dimension'        => Piwik_Translate('General_ColumnKeyword'),
                                                  'documentation'    => Piwik_Translate('Referrers_SearchEnginesReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 8,
                                              ),

                                              array(
                                                  'category'              => Piwik_Translate('Referrers_Referrers'),
                                                  'name'                  => Piwik_Translate('Referrers_Campaigns'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getCampaigns',
                                                  'dimension'             => Piwik_Translate('Referrers_ColumnCampaign'),
                                                  'documentation'         => Piwik_Translate('Referrers_CampaignsReportDocumentation',
                                                      array('<br />', '<a href="http://piwik.org/docs/tracking-campaigns/" target="_blank">', '</a>')),
                                                  'actionToLoadSubTables' => 'getKeywordsFromCampaignId',
                                                  'order'                 => 9,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik_Translate('Referrers_Referrers'),
                                                  'name'             => Piwik_Translate('Referrers_Campaigns'),
                                                  'module'           => 'Referers',
                                                  'action'           => 'getKeywordsFromCampaignId',
                                                  'dimension'        => Piwik_Translate('General_ColumnKeyword'),
                                                  'documentation'    => Piwik_Translate('Referrers_CampaignsReportDocumentation',
                                                      array('<br />', '<a href="http://piwik.org/docs/tracking-campaigns/" target="_blank">', '</a>')),
                                                  'isSubtableReport' => true,
                                                  'order'            => 10,
                                              ),
                                              array(
                                                  'category'              => Piwik_Translate('Referrers_Referrers'),
                                                  'name'                  => Piwik_Translate('Referrers_Socials'),
                                                  'module'                => 'Referers',
                                                  'action'                => 'getSocials',
                                                  'actionToLoadSubTables' => 'getUrlsForSocial',
                                                  'dimension'             => Piwik_Translate('Referrers_ColumnSocial'),
                                                  'documentation'         => Piwik_Translate('Referrers_WebsitesReportDocumentation', '<br />'),
                                                  'order'                 => 11,
                                              ),
                                         ));
    }

    public function getSegmentsMetadata(&$segments)
    {
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Referrers_Referrers',
            'name'           => 'Referrers_Type',
            'segment'        => 'referrerType',
            'acceptedValues' => 'direct, search, website, campaign',
            'sqlSegment'     => 'log_visit.referer_type',
            'sqlFilter'      => __NAMESPACE__ . '\getRefererTypeFromShortName',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Referrers_Referrers',
            'name'           => 'General_ColumnKeyword',
            'segment'        => 'referrerKeyword',
            'acceptedValues' => 'Encoded%20Keyword, keyword',
            'sqlSegment'     => 'log_visit.referer_keyword',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Referrers_Referrers',
            'name'           => 'Referrers_ReferrerName',
            'segment'        => 'referrerName',
            'acceptedValues' => 'twitter.com, www.facebook.com, Bing, Google, Yahoo, CampaignName',
            'sqlSegment'     => 'log_visit.referer_name',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Referrers_Referrers',
            'name'           => 'Live_Referrer_URL',
            'acceptedValues' => 'http%3A%2F%2Fwww.example.org%2Freferer-page.htm',
            'segment'        => 'referrerUrl',
            'sqlSegment'     => 'log_visit.referer_url',
        );
    }

    /**
     * Adds Referrer widgets
     */
    function addWidgets()
    {
        WidgetsList::add('Referrers_Referrers', 'Referrers_WidgetKeywords', 'Referers', 'getKeywords');
        WidgetsList::add('Referrers_Referrers', 'Referrers_WidgetExternalWebsites', 'Referers', 'getWebsites');
        WidgetsList::add('Referrers_Referrers', 'Referrers_WidgetSocials', 'Referers', 'getSocials');
        WidgetsList::add('Referrers_Referrers', 'Referrers_SearchEngines', 'Referers', 'getSearchEngines');
        WidgetsList::add('Referrers_Referrers', 'Referrers_Campaigns', 'Referers', 'getCampaigns');
        WidgetsList::add('Referrers_Referrers', 'General_Overview', 'Referers', 'getRefererType');
        WidgetsList::add('Referrers_Referrers', 'Referrers_WidgetGetAll', 'Referers', 'getAll');
        if (SettingsPiwik::isSegmentationEnabled()) {
            WidgetsList::add('SEO', 'Referrers_WidgetTopKeywordsForPages', 'Referers', 'getKeywordsForPage');
        }
    }

    /**
     * Adds Web Analytics menus
     */
    function addMenus()
    {
        Piwik_AddMenu('Referrers_Referrers', '', array('module' => 'Referers', 'action' => 'index'), true, 20);
        Piwik_AddMenu('Referrers_Referrers', 'General_Overview', array('module' => 'Referers', 'action' => 'index'), true, 1);
        Piwik_AddMenu('Referrers_Referrers', 'Referrers_SubmenuSearchEngines', array('module' => 'Referers', 'action' => 'getSearchEnginesAndKeywords'), true, 2);
        Piwik_AddMenu('Referrers_Referrers', 'Referrers_SubmenuWebsites', array('module' => 'Referers', 'action' => 'indexWebsites'), true, 3);
        Piwik_AddMenu('Referrers_Referrers', 'Referrers_Campaigns', array('module' => 'Referers', 'action' => 'indexCampaigns'), true, 4);
    }

    /**
     * Adds Goal dimensions, so that the dimensions are displayed in the UI Goal Overview page
     */
    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions = array_merge($dimensions, array(
                                                    array('category' => Piwik_Translate('Referrers_Referrers'),
                                                          'name'     => Piwik_Translate('Referrers_Keywords'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getKeywords',
                                                    ),
                                                    array('category' => Piwik_Translate('Referrers_Referrers'),
                                                          'name'     => Piwik_Translate('Referrers_SearchEngines'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getSearchEngines',
                                                    ),
                                                    array('category' => Piwik_Translate('Referrers_Referrers'),
                                                          'name'     => Piwik_Translate('Referrers_Websites'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getWebsites',
                                                    ),
                                                    array('category' => Piwik_Translate('Referrers_Referrers'),
                                                          'name'     => Piwik_Translate('Referrers_Campaigns'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getCampaigns',
                                                    ),
                                                    array('category' => Piwik_Translate('Referrers_Referrers'),
                                                          'name'     => Piwik_Translate('Referrers_Type'),
                                                          'module'   => 'Referers',
                                                          'action'   => 'getRefererType',
                                                    ),
                                               ));
    }

    /**
     * Hooks on daily archive to trigger various log processing
     */
    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    /**
     * Period archiving: sums up daily stats and sums report tables,
     * making sure that tables are still truncated.
     */
    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['Referers.getRefererType'] = $this->getDisplayPropertiesForGetRefererType();
        $properties['Referers.getAll'] = $this->getDisplayPropertiesForGetAll();
        $properties['Referers.getKeywords'] = $this->getDisplayPropertiesForGetKeywords();
        $properties['Referers.getSearchEnginesFromKeywordId'] = $this->getDisplayPropertiesForGetSearchEnginesFromKeywordId();
        $properties['Referers.getSearchEngines'] = $this->getDisplayPropertiesForGetSearchEngines();
        $properties['Referers.getKeywordsFromSearchEngineId'] = $this->getDisplayPropertiesForGetKeywordsFromSearchEngineId();
        $properties['Referers.getWebsites'] = $this->getDisplayPropertiesForGetWebsites();
        $properties['Referers.getSocials'] = $this->getDisplayPropertiesForGetSocials();
        $properties['Referers.getUrlsForSocial'] = $this->getDisplayPropertiesForGetUrlsForSocial();
        $properties['Referers.getCampaigns'] = $this->getDisplayPropertiesForGetCampaigns();
        $properties['Referers.getKeywordsFromCampaignId'] = $this->getDisplayPropertiesForGetKeywordsFromCampaignId();
        $properties['Referers.getUrlsFromWebsiteId'] = $this->getDisplayPropertiesForGetUrlsFromWebsiteId();
    }

    private function getDisplayPropertiesForGetRefererType()
    {
        $idSubtable = Common::getRequestVar('idSubtable', false);
        $labelColumnTitle = Piwik_Translate('Referrers_Type');
        switch ($idSubtable) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                $labelColumnTitle = Piwik_Translate('Referrers_ColumnSearchEngine');
                break;
            case Common::REFERRER_TYPE_WEBSITE:
                $labelColumnTitle = Piwik_Translate('Referrers_ColumnWebsite');
                break;
            case Common::REFERRER_TYPE_CAMPAIGN:
                $labelColumnTitle = Piwik_Translate('Referrers_ColumnCampaign');
                break;
            default:
                break;
        }

        return array(
            'default_view_type' => 'tableAllColumns',
            'show_search' => false,
            'show_offset_information' => false,
            'show_pagination_control' => false,
            'show_limit_control' => false,
            'show_exclude_low_population' => false,
            'show_goals' => true,
            'filter_limit' => 10,
            'translations' => array('label' => $labelColumnTitle),
            'visualization_properties' => array(
                'table' => array(
                    'disable_subtable_when_show_goals' => true,
                )
            ),
        );
    }

    private function getDisplayPropertiesForGetAll()
    {
        $setGetAllHtmlPrefix = array($this, 'setGetAllHtmlPrefix');
        return array(
            'show_exclude_low_population' => false,
            'translations' => array('label' => Piwik_Translate('Referrers_Referrer')),
            'show_goals' => true,
            'filter_limit' => 20,
            'visualization_properties' => array(
                'table' => array(
                    'disable_row_actions' => true
                )
            ),
            'filters' => array(
                array('MetadataCallbackAddMetadata', array('referrer_type', 'html_label_prefix', $setGetAllHtmlPrefix))
            )
        );
    }

    private function getDisplayPropertiesForGetKeywords()
    {
        return array(
            'subtable_controller_action' => 'getSearchEnginesFromKeywordId',
            'show_exclude_low_population' => false,
            'translations' => array('label' => Piwik_Translate('General_ColumnKeyword')),
            'show_goals' => true,
            'filter_limit' => 25,
            'visualization_properties' => array(
                'table' => array(
                    'disable_subtable_when_show_goals' => true,
                )
            ),
        );
    }

    private function getDisplayPropertiesForGetSearchEnginesFromKeywordId()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik_Translate('Referrers_ColumnSearchEngine'))
        );
    }

    private function getDisplayPropertiesForGetSearchEngines()
    {
        return array(
            'subtable_controller_action' => 'getKeywordsFromSearchEngineId',
            'show_search' => false,
            'show_exclude_low_population' => false,
            'show_goals' => true,
            'filter_limit' => 25,
            'translations' => array('label' => Piwik_Translate('Referrers_ColumnSearchEngine')),
            'visualization_properties' => array(
                'table' => array(
                    'disable_subtable_when_show_goals' => true,
                )
            ),
        );
    }

    private function getDisplayPropertiesForGetKeywordsFromSearchEngineId()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik_Translate('General_ColumnKeyword'))
        );
    }

    private function getDisplayPropertiesForGetWebsites()
    {
        return array(
            'subtable_controller_action' => 'getUrlsFromWebsiteId',
            'show_exclude_low_population' => false,
            'show_goals' => true,
            'filter_limit' => 25,
            'translations' => array('label' => Piwik_Translate('Referrers_ColumnWebsite')),
            'visualization_properties' => array(
                'table' => array(
                    'disable_subtable_when_show_goals' => true,
                )
            ),
        );
    }

    private function getDisplayPropertiesForGetSocials()
    {
        $result = array(
            'default_view_type' => 'graphPie',
            'subtable_controller_action' => 'getUrlsForSocial',
            'show_exclude_low_population' => false,
            'filter_limit' => 10,
            'show_goals' => true,
            'translations' => array('label' => Piwik_Translate('Referrers_ColumnSocial')),
            'visualization_properties' => array(
                'table' => array(
                    'disable_subtable_when_show_goals' => true,
                )
            ),
        );

        $widget = Common::getRequestVar('widget', false);
        if (empty($widget)) {
            $result['show_footer_message'] = Piwik_Translate('Referrers_SocialFooterMessage');
        }

        return $result;
    }

    private function getDisplayPropertiesForGetUrlsForSocial()
    {
        return array(
            'show_exclude_low_population' => false,
            'filter_limit'                => 10,
            'show_goals'                  => true,
            'translations'                => array('label' => Piwik_Translate('Referrers_ColumnWebsitePage'))
        );
    }

    private function getDisplayPropertiesForGetCampaigns()
    {
        $result = array(
            'subtable_controller_action'  => 'getKeywordsFromCampaignId',
            'show_exclude_low_population' => false,
            'show_goals'                  => true,
            'filter_limit'                => 25,
            'translations'                => array('label' => Piwik_Translate('Referrers_ColumnCampaign')),
        );

        if (Common::getRequestVar('viewDataTable', false) != 'graphEvolution') {
            $result['show_footer_message'] = Piwik_Translate('Referrers_CampaignFooterHelp',
                array('<a target="_blank" href="http://piwik.org/docs/tracking-campaigns/">',
                      '</a> - <a target="_blank" href="http://piwik.org/docs/tracking-campaigns/url-builder/">',
                      '</a>')
            );
        }

        return $result;
    }

    private function getDisplayPropertiesForGetKeywordsFromCampaignId()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik_Translate('General_ColumnKeyword'))
        );
    }

    private function getDisplayPropertiesForGetUrlsFromWebsiteId()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'translations'                => array('label' => Piwik_Translate('Referrers_ColumnWebsitePage')),
            'tooltip_metadata_name'       => 'url'
        );
    }

    /**
     * DataTable filter callback that returns the HTML prefix for a label in the
     * 'getAll' report based on the row's referrer type.
     *
     * @param int $referrerType The referrer type.
     * @return string
     */
    public function setGetAllHtmlPrefix($referrerType)
    {
        // get singular label for referrer type
        $indexTranslation = '';
        switch ($referrerType) {
            case Common::REFERRER_TYPE_DIRECT_ENTRY:
                $indexTranslation = 'Referrers_DirectEntry';
                break;
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                $indexTranslation = 'General_ColumnKeyword';
                break;
            case Common::REFERRER_TYPE_WEBSITE:
                $indexTranslation = 'Referrers_ColumnWebsite';
                break;
            case Common::REFERRER_TYPE_CAMPAIGN:
                $indexTranslation = 'Referrers_ColumnCampaign';
                break;
            default:
                // case of newsletter, partners, before Piwik 0.2.25
                $indexTranslation = 'General_Others';
                break;
        }

        $label = strtolower(Piwik_Translate($indexTranslation));

        // return html that displays it as grey & italic
        return '<span class="datatable-label-category"><em>(' . $label . ')</em></span>';
    }
}
