<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Transitions
 */

/**
 * @package Piwik_Transitions
 */
class Piwik_Transitions_Controller extends Piwik_Controller
{
	
	/**
	 * Since the metric translations are taken from different plugins,
	 * it makes the rest of the code easier to read and maintain when we
	 * use this indirection to map between the metrics and the actual
	 * translation keys. 
	 */
	private static $metricTranslations = array(
		'pageviewsInline' => 'Transitions_PageviewsInline',
		'loopsInline' => 'Transitions_LoopsInline',
		'fromPreviousPages' => 'Transitions_FromPreviousPages',
		'fromPreviousPagesInline' => 'Transitions_FromPreviousPagesInline',
		'fromSearchEngines' => 'Transitions_FromSearchEngines',
		'fromSearchEnginesInline' => 'Transitions_FromSearchEnginesInline',
		'fromWebsites' => 'Transitions_FromWebsites',
		'fromWebsitesInline' => 'Transitions_FromWebsitesInline',
		'fromCampaigns' => 'Transitions_FromCampaigns',
		'fromCampaignsInline' => 'Transitions_FromCampaignsInline',
		'directEntries' => 'Transitions_DirectEntries',
		'directEntriesInline' => 'Referers_TypeDirectEntries',
		'toFollowingPages' => 'Transitions_ToFollowingPages',
		'toFollowingPagesInline' => 'Transitions_ToFollowingPagesInline',
		'downloads' => 'Actions_ColumnDownloads',
		'downloadsInline' => 'VisitsSummary_NbDownloadsDescription',
		'outlinks' => 'Actions_ColumnOutlinks',
		'outlinksInline' => 'VisitsSummary_NbOutlinksDescription',
		'exits' => 'General_ColumnExits',
		'exitsInline' => 'Transitions_ExitsInline',
		'bouncesInline' => 'Transitions_BouncesInline'
	);

	/**
	 * Translations that are added to JS
	 * (object Piwik_Transitions_Translations)
	 */
	private static $jsTranslations = array(
		'XOfY' => 'Transitions_XOutOfYVisits',
		'XOfAllPageviews' => 'Transitions_XOfAllPageviews',
		'NoDataForUrl' => 'Transitions_NoDataForUrl',
		'NoDataForUrlDetails' => 'Transitions_NoDataForUrlDetails',
		'NoDataForUrlBack' => 'Transitions_ErrorBack'
	);

	public static function getTranslation($key)
	{
		return Piwik_Translate(self::$metricTranslations[$key]);
	}
	
	/**
	 * The main method of the plugin. 
	 * It is triggered from the Transitions data table action.
	 */
	public function renderPopover()
	{
		$view = Piwik_View::factory('transitions');
		$view->translations = self::$metricTranslations + self::$jsTranslations;
		echo $view->render();
	}
	
}
