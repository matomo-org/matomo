<?php
/**
 * Piwik - Open source web analytics
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
