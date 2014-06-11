<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable\AllColumns;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\SettingsPiwik;

/**
 * @see plugins/Referrers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';

/**
 */
class Referrers extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Goals.getReportsWithGoalMetrics' => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'ViewDataTable.configure'         => 'configureViewDataTable',
            'ViewDataTable.getDefaultType'    => 'getDefaultTypeViewDataTable',
            'Insights.addReportToOverview'    => 'addReportToInsightsOverview'
        );
        return $hooks;
    }

    public function addReportToInsightsOverview(&$reports)
    {
        $reports['Referrers_getWebsites']  = array();
        $reports['Referrers_getCampaigns'] = array();
        $reports['Referrers_getSocials']   = array();
        $reports['Referrers_getSearchEngines'] = array();
    }

    public function getReportMetadata(&$reports)
    {
        $reports = array_merge($reports, array(
                                              array(
                                                  'category'          => Piwik::translate('Referrers_Referrers'),
                                                  'name'              => Piwik::translate('Referrers_Type'),
                                                  'module'            => 'Referrers',
                                                  'action'            => 'getReferrerType',
                                                  'dimension'         => Piwik::translate('Referrers_Type'),
                                                  'constantRowsCount' => true,
                                                  'documentation'     => Piwik::translate('Referrers_TypeReportDocumentation') . '<br />'
                                                      . '<b>' . Piwik::translate('Referrers_DirectEntry') . ':</b> ' . Piwik::translate('Referrers_DirectEntryDocumentation') . '<br />'
                                                      . '<b>' . Piwik::translate('Referrers_SearchEngines') . ':</b> ' . Piwik::translate('Referrers_SearchEnginesDocumentation',
                                                          array('<br />', '&quot;' . Piwik::translate('Referrers_SubmenuSearchEngines') . '&quot;')) . '<br />'
                                                      . '<b>' . Piwik::translate('Referrers_Websites') . ':</b> ' . Piwik::translate('Referrers_WebsitesDocumentation',
                                                          array('<br />', '&quot;' . Piwik::translate('Referrers_SubmenuWebsites') . '&quot;')) . '<br />'
                                                      . '<b>' . Piwik::translate('Referrers_Campaigns') . ':</b> ' . Piwik::translate('Referrers_CampaignsDocumentation',
                                                          array('<br />', '&quot;' . Piwik::translate('Referrers_Campaigns') . '&quot;')),
                                                  'order'             => 1,
                                              ),
                                              array(
                                                  'category'      => Piwik::translate('Referrers_Referrers'),
                                                  'name'          => Piwik::translate('Referrers_WidgetGetAll'),
                                                  'module'        => 'Referrers',
                                                  'action'        => 'getAll',
                                                  'dimension'     => Piwik::translate('Referrers_Referrer'),
                                                  'documentation' => Piwik::translate('Referrers_AllReferrersReportDocumentation', '<br />'),
                                                  'order'         => 2,
                                              ),
                                              array(
                                                  'category'              => Piwik::translate('Referrers_Referrers'),
                                                  'name'                  => Piwik::translate('Referrers_Keywords'),
                                                  'module'                => 'Referrers',
                                                  'action'                => 'getKeywords',
                                                  'actionToLoadSubTables' => 'getSearchEnginesFromKeywordId',
                                                  'dimension'             => Piwik::translate('General_ColumnKeyword'),
                                                  'documentation'         => Piwik::translate('Referrers_KeywordsReportDocumentation', '<br />'),
                                                  'order'                 => 3,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik::translate('Referrers_Referrers'),
                                                  'name'             => Piwik::translate('Referrers_Keywords'),
                                                  'module'           => 'Referrers',
                                                  'action'           => 'getSearchEnginesFromKeywordId',
                                                  'dimension'        => Piwik::translate('Referrers_ColumnSearchEngine'),
                                                  'documentation'    => Piwik::translate('Referrers_KeywordsReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 4
                                              ),

                                              array(
                                                  'category'              => Piwik::translate('Referrers_Referrers'),
                                                  'name'                  => Piwik::translate('Referrers_Websites'),
                                                  'module'                => 'Referrers',
                                                  'action'                => 'getWebsites',
                                                  'dimension'             => Piwik::translate('Referrers_ColumnWebsite'),
                                                  'documentation'         => Piwik::translate('Referrers_WebsitesReportDocumentation', '<br />'),
                                                  'actionToLoadSubTables' => 'getUrlsFromWebsiteId',
                                                  'order'                 => 5
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik::translate('Referrers_Referrers'),
                                                  'name'             => Piwik::translate('Referrers_Websites'),
                                                  'module'           => 'Referrers',
                                                  'action'           => 'getUrlsFromWebsiteId',
                                                  'dimension'        => Piwik::translate('Referrers_ColumnWebsitePage'),
                                                  'documentation'    => Piwik::translate('Referrers_WebsitesReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 6,
                                              ),

                                              array(
                                                  'category'              => Piwik::translate('Referrers_Referrers'),
                                                  'name'                  => Piwik::translate('Referrers_SearchEngines'),
                                                  'module'                => 'Referrers',
                                                  'action'                => 'getSearchEngines',
                                                  'dimension'             => Piwik::translate('Referrers_ColumnSearchEngine'),
                                                  'documentation'         => Piwik::translate('Referrers_SearchEnginesReportDocumentation', '<br />'),
                                                  'actionToLoadSubTables' => 'getKeywordsFromSearchEngineId',
                                                  'order'                 => 7,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik::translate('Referrers_Referrers'),
                                                  'name'             => Piwik::translate('Referrers_SearchEngines'),
                                                  'module'           => 'Referrers',
                                                  'action'           => 'getKeywordsFromSearchEngineId',
                                                  'dimension'        => Piwik::translate('General_ColumnKeyword'),
                                                  'documentation'    => Piwik::translate('Referrers_SearchEnginesReportDocumentation', '<br />'),
                                                  'isSubtableReport' => true,
                                                  'order'            => 8,
                                              ),

                                              array(
                                                  'category'              => Piwik::translate('Referrers_Referrers'),
                                                  'name'                  => Piwik::translate('Referrers_Campaigns'),
                                                  'module'                => 'Referrers',
                                                  'action'                => 'getCampaigns',
                                                  'dimension'             => Piwik::translate('Referrers_ColumnCampaign'),
                                                  'documentation'         => Piwik::translate('Referrers_CampaignsReportDocumentation',
                                                      array('<br />', '<a href="http://piwik.org/docs/tracking-campaigns/" target="_blank">', '</a>')),
                                                  'actionToLoadSubTables' => 'getKeywordsFromCampaignId',
                                                  'order'                 => 9,
                                              ),
                                              array( // subtable report
                                                  'category'         => Piwik::translate('Referrers_Referrers'),
                                                  'name'             => Piwik::translate('Referrers_Campaigns'),
                                                  'module'           => 'Referrers',
                                                  'action'           => 'getKeywordsFromCampaignId',
                                                  'dimension'        => Piwik::translate('General_ColumnKeyword'),
                                                  'documentation'    => Piwik::translate('Referrers_CampaignsReportDocumentation',
                                                      array('<br />', '<a href="http://piwik.org/docs/tracking-campaigns/" target="_blank">', '</a>')),
                                                  'isSubtableReport' => true,
                                                  'order'            => 10,
                                              ),
                                              array(
                                                  'category'              => Piwik::translate('Referrers_Referrers'),
                                                  'name'                  => Piwik::translate('Referrers_Socials'),
                                                  'module'                => 'Referrers',
                                                  'action'                => 'getSocials',
                                                  'actionToLoadSubTables' => 'getUrlsForSocial',
                                                  'dimension'             => Piwik::translate('Referrers_ColumnSocial'),
                                                  'documentation'         => Piwik::translate('Referrers_WebsitesReportDocumentation', '<br />'),
                                                  'order'                 => 11,
                                              ),
                                              array(
                                                  'category'              => Piwik::translate('Referrers_Referrers'),
                                                  'name'                  => Piwik::translate('Referrers_Socials'),
                                                  'module'                => 'Referrers',
                                                  'action'                => 'getUrlsForSocial',
                                                  'isSubtableReport'      => true,
                                                  'dimension'             => Piwik::translate('Referrers_ColumnWebsitePage'),
                                                  'documentation'         => Piwik::translate('Referrers_WebsitesReportDocumentation', '<br />'),
                                                  'order'                 => 12,
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
            'sqlFilterValue' => __NAMESPACE__ . '\getReferrerTypeFromShortName',
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
     * Adds Goal dimensions, so that the dimensions are displayed in the UI Goal Overview page
     */
    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions = array_merge($dimensions, array(
            array('category' => Piwik::translate('Referrers_Referrers'),
                  'name'     => Piwik::translate('Referrers_Type'),
                  'module'   => 'Referrers',
                  'action'   => 'getReferrerType',
            ),
            array('category' => Piwik::translate('Referrers_Referrers'),
                  'name'     => Piwik::translate('Referrers_Keywords'),
                  'module'   => 'Referrers',
                  'action'   => 'getKeywords',
            ),
            array('category' => Piwik::translate('Referrers_Referrers'),
                  'name'     => Piwik::translate('Referrers_SearchEngines'),
                  'module'   => 'Referrers',
                  'action'   => 'getSearchEngines',
            ),
            array('category' => Piwik::translate('Referrers_Referrers'),
                  'name'     => Piwik::translate('Referrers_Websites'),
                  'module'   => 'Referrers',
                  'action'   => 'getWebsites',
            ),
            array('category' => Piwik::translate('Referrers_Referrers'),
                  'name'     => Piwik::translate('Referrers_Campaigns'),
                  'module'   => 'Referrers',
                  'action'   => 'getCampaigns',
            ),
        ));
    }

    public function getDefaultTypeViewDataTable(&$defaultViewTypes)
    {
        $defaultViewTypes['Referrers.getReferrerType'] = AllColumns::ID;
        $defaultViewTypes['Referrers.getSocials']      = Pie::ID;
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'Referrers.getReferrerType':
                $this->configureViewForGetReferrerType($view);
                break;
            case 'Referrers.getAll':
                $this->configureViewForGetAll($view);
                break;
            case 'Referrers.getKeywords':
                $this->configureViewForGetKeywords($view);
                break;
            case 'Referrers.getSearchEnginesFromKeywordId':
                $this->configureViewForGetSearchEnginesFromKeywordId($view);
                break;
            case 'Referrers.getSearchEngines':
                $this->configureViewForGetSearchEngines($view);
                break;
            case 'Referrers.getKeywordsFromSearchEngineId':
                $this->configureViewForGetKeywordsFromSearchEngineId($view);
                break;
            case 'Referrers.getWebsites':
                $this->configureViewForGetWebsites($view);
                break;
            case 'Referrers.getSocials':
                $this->configureViewForGetSocials($view);
                break;
            case 'Referrers.getUrlsForSocial':
                $this->configureViewForGetUrlsForSocial($view);
                break;
            case 'Referrers.getCampaigns':
                $this->configureViewForGetCampaigns($view);
                break;
            case 'Referrers.getKeywordsFromCampaignId':
                $this->configureViewForGetKeywordsFromCampaignId($view);
                break;
            case 'Referrers.getUrlsFromWebsiteId':
                $this->configureViewForGetUrlsFromWebsiteId($view);
                break;
        }
    }

    private function configureViewForGetReferrerType(ViewDataTable $view)
    {
        $idSubtable       = Common::getRequestVar('idSubtable', false);
        $labelColumnTitle = Piwik::translate('Referrers_Type');

        switch ($idSubtable) {
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                $labelColumnTitle = Piwik::translate('Referrers_ColumnSearchEngine');
                break;
            case Common::REFERRER_TYPE_WEBSITE:
                $labelColumnTitle = Piwik::translate('Referrers_ColumnWebsite');
                break;
            case Common::REFERRER_TYPE_CAMPAIGN:
                $labelColumnTitle = Piwik::translate('Referrers_ColumnCampaign');
                break;
            default:
                break;
        }

        $view->config->show_search = false;
        $view->config->show_goals  = true;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $labelColumnTitle);

        $view->requestConfig->filter_limit = 10;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }
    }

    private function configureViewForGetAll(ViewDataTable $view)
    {
        $setGetAllHtmlPrefix = array($this, 'setGetAllHtmlPrefix');

        $view->config->show_exclude_low_population = false;
        $view->config->show_goals = true;
        $view->config->addTranslation('label', Piwik::translate('Referrers_Referrer'));

        $view->requestConfig->filter_limit = 20;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_row_actions = true;
        }

        $view->config->filters[] = array('MetadataCallbackAddMetadata', array('referer_type', 'html_label_prefix', $setGetAllHtmlPrefix));
    }

    private function configureViewForGetKeywords(ViewDataTable $view)
    {
        $view->config->subtable_controller_action  = 'getSearchEnginesFromKeywordId';
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('General_ColumnKeyword'));
        $view->config->show_goals = true;

        $view->requestConfig->filter_limit = 25;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }
    }

    private function configureViewForGetSearchEnginesFromKeywordId(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('Referrers_ColumnSearchEngine'));
    }

    private function configureViewForGetSearchEngines(ViewDataTable $view)
    {
        $view->config->subtable_controller_action  = 'getKeywordsFromSearchEngineId';
        $view->config->show_exclude_low_population = false;
        $view->config->show_search = false;
        $view->config->show_goals  = true;
        $view->config->addTranslation('label', Piwik::translate('Referrers_ColumnSearchEngine'));

        $view->requestConfig->filter_limit = 25;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }
    }

    private function configureViewForGetKeywordsFromSearchEngineId(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('General_ColumnKeyword'));
    }

    private function configureViewForGetWebsites(ViewDataTable $view)
    {
        $view->config->subtable_controller_action  = 'getUrlsFromWebsiteId';
        $view->config->show_exclude_low_population = false;
        $view->config->show_goals = true;
        $view->config->addTranslation('label', Piwik::translate('Referrers_ColumnWebsite'));

        $view->requestConfig->filter_limit = 25;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }
    }

    private function configureViewForGetSocials(ViewDataTable $view)
    {
        $view->config->subtable_controller_action  = 'getUrlsForSocial';
        $view->config->show_exclude_low_population = false;
        $view->config->show_goals = true;
        $view->config->addTranslation('label', Piwik::translate('Referrers_ColumnSocial'));

        $view->requestConfig->filter_limit = 10;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;
        }

        $widget = Common::getRequestVar('widget', false);
        if (empty($widget)) {
            $view->config->show_footer_message = Piwik::translate('Referrers_SocialFooterMessage');
        }
    }

    private function configureViewForGetUrlsForSocial(ViewDataTable $view)
    {
        $view->config->show_goals = true;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('Referrers_ColumnWebsitePage'));

        $view->requestConfig->filter_limit = 10;
    }

    private function configureViewForGetCampaigns(ViewDataTable $view)
    {
        $view->config->show_goals = true;
        $view->config->subtable_controller_action  = 'getKeywordsFromCampaignId';
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('Referrers_ColumnCampaign'));

        $view->requestConfig->filter_limit = 25;
    }

    private function configureViewForGetKeywordsFromCampaignId(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('General_ColumnKeyword'));
    }

    private function configureViewForGetUrlsFromWebsiteId(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->tooltip_metadata_name       = 'url';
        $view->config->addTranslation('label', Piwik::translate('Referrers_ColumnWebsitePage'));
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

        $label = strtolower(Piwik::translate($indexTranslation));

        // return html that displays it as grey & italic
        return '<span class="datatable-label-category"><em>(' . $label . ')</em></span>';
    }
}
