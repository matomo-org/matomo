<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: ExamplePlugin.php 6427 2012-05-31 23:26:11Z matt $
 * 
 * @category Piwik_Plugins
 * @package Piwik_ClearCache
 */

/**
 *
 * @package Piwik_ClearCache
 */
class Piwik_ClearCache extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('ClearCache_PluginDescription'),
			'author' => 'Spherexx.com',
			'author_homepage' => 'http://spherexx.com/',
			'version' => '1.0',
			'translationAvailable' => true
		);
		return $info;
	}

	function getListHooksRegistered()
	{
		return array( 
			'TopMenu.add' => 'addTopMenu',
		);
	}

	public function addTopMenu()
	{
		If(Piwik::isUserIsSuperUser())
		{
			$tooltip = false;
			try
			{
				$idSite = Piwik_Common::getRequestVar('idSite');
				$tooltip = Piwik_Translate('ClearCache_PluginDescription', Piwik_Site::getNameFor($idSite));
			}
			catch (Exception $ex)
			{
				// if no idSite parameter, show no tooltip
			}
			
			$urlParams = array('module' => 'ClearCache', 'action' => 'index');
			Piwik_AddTopMenu('ClearCache', $urlParams, true, 8, $isHTML = false, $tooltip);
		}
	}
}