<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_MultiSites
 */

/**
 *
 * @package Piwik_MultiSites
 */
class Piwik_MultiSites extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('MultiSites_PluginDescription'),
			'author' => 'ClearCode.cc',
			'author_homepage' => "http://clearcode.cc/",
			'version' => Piwik_Version::VERSION,
		);
	}

	public function getListHooksRegistered()
	{
		return array(
			'AssetManager.getCssFiles' => 'getCssFiles',
			'AssetManager.getJsFiles' => 'getJsFiles',
			'TopMenu.add' => 'addTopMenu',
			'API.getReportMetadata' => 'getReportMetadata',
		);
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	public function getReportMetadata($notification)
	{
		$isGoalPluginEnabled = Piwik_Common::isGoalPluginEnabled();
	
		$metrics = array( 'nb_visits', 'nb_actions' );
		if ($isGoalPluginEnabled)
		{
			$metrics['revenue'] = Piwik_Translate('Goals_ColumnRevenue');
		}

		$reports = &$notification->getNotificationObject();
		$reports[] = array(
			'category' => Piwik_Translate('General_MultiSitesSummary'),
			'name' => Piwik_Translate('General_AllWebsitesDashboard'),
			'module' => 'MultiSites',
			'action' => 'getAll',
			'dimension' => Piwik_Translate('General_Website'), // re-using translation
			'metrics' => $metrics,
			'processedMetrics' => false,
			'constantRowsCount' => false,
			'order' => 5
		);
	}

	public function addTopMenu()
	{
		Piwik_AddTopMenu('General_MultiSitesSummary', array('module' => 'MultiSites', 'action' => 'index'), true, 3);
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();
		
		$jsFiles[] = "plugins/MultiSites/templates/common.js";
	}

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	function getCssFiles( $notification )
	{
		$cssFiles = &$notification->getNotificationObject();
		
		$cssFiles[] = "plugins/MultiSites/templates/styles.css";
	}
}
