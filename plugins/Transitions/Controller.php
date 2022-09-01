<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Transitions;

use Piwik\Piwik;
use Piwik\View;

/**
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * Since the metric translations are taken from different plugins,
     * it makes the rest of the code easier to read and maintain when we
     * use this indirection to map between the metrics and the actual
     * translation keys.
     */
    private static $metricTranslations = array(
        'pageviewsInline'                => 'Transitions_NumPageviews',
        'loopsInline'                    => 'Transitions_LoopsInline',
        'fromPreviousPages'              => 'Transitions_FromPreviousPages',
        'fromPreviousPagesInline'        => 'Transitions_FromPreviousPagesInline',
        'fromPreviousSiteSearches'       => 'Transitions_FromPreviousSiteSearches',
        'fromPreviousSiteSearchesInline' => 'Transitions_FromPreviousSiteSearchesInline',
        'fromSearchEngines'              => 'Transitions_FromSearchEngines',
        'fromSearchEnginesInline'        => 'Referrers_TypeSearchEngines',
        'fromSocialNetworks'             => 'Transitions_FromSocialNetworks',
        'fromSocialNetworksInline'       => 'Referrers_TypeSocialNetworks',
        'fromWebsites'                   => 'Transitions_FromWebsites',
        'fromWebsitesInline'             => 'Referrers_TypeWebsites',
        'fromCampaigns'                  => 'Transitions_FromCampaigns',
        'fromCampaignsInline'            => 'Referrers_TypeCampaigns',
        'directEntries'                  => 'Transitions_DirectEntries',
        'directEntriesInline'            => 'Referrers_TypeDirectEntries',
        'toFollowingPages'               => 'Transitions_ToFollowingPages',
        'toFollowingPagesInline'         => 'Transitions_ToFollowingPagesInline',
        'toFollowingSiteSearches'        => 'Transitions_ToFollowingSiteSearches',
        'toFollowingSiteSearchesInline'  => 'Transitions_ToFollowingSiteSearchesInline',
        'downloads'                      => 'General_Downloads',
        'downloadsInline'                => 'Transitions_NumDownloads',
        'outlinks'                       => 'General_Outlinks',
        'outlinksInline'                 => 'Transitions_NumOutlinks',
        'exits'                          => 'General_ColumnExits',
        'exitsInline'                    => 'Transitions_ExitsInline',
        'bouncesInline'                  => 'Transitions_BouncesInline'
    );

    /**
     * Translations that are added to JS
     */
    private static $jsTranslations = array(
        'XOfY'                   => 'Transitions_XOutOfYVisits',
        'XOfAllPageviews'        => 'Transitions_XOfAllPageviews',
        'NoDataForAction'        => 'Transitions_NoDataForAction',
        'NoDataForActionDetails' => 'Transitions_NoDataForActionDetails',
        'NoDataForActionBack'    => 'Transitions_ErrorBack',
        'PeriodNotAllowed'       => 'Transitions_PeriodNotAllowed',
        'PeriodNotAllowedDetails'=> 'Transitions_PeriodNotAllowedDetails',
        'PeriodNotAllowedBack'   => 'Transitions_ErrorBack',
        'ShareOfAllPageviews'    => 'Transitions_ShareOfAllPageviews',
        'DateRange'              => 'General_DateRange'
    );

    public static function getTranslation($key)
    {
        return Piwik::translate(self::$metricTranslations[$key]);
    }

    /**
     * The main method of the plugin.
     * It is triggered from the Transitions data table action.
     */
    public function renderPopover()
    {
        $view = new View('@Transitions/renderPopover');
        $view->translations = $this->getTranslations();
        return $view->render();
    }

    public function getTranslations()
    {
        $translations = self::$metricTranslations + self::$jsTranslations;
        foreach ($translations as &$message) {
            $message = Piwik::translate($message);
        }
        return $translations;
    }
}
