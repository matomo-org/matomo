<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Parent class of all plugins Controllers with admin functions
 * 
 * @package Piwik
 */
abstract class Piwik_Controller_Admin extends Piwik_Controller
{
	/**
	 * Set the minimal variables in the view object
	 * Extended by some admin view specific variables
	 * 
	 * @param Piwik_View  $view
	 */
	protected function setBasicVariablesView($view)
	{
		parent::setBasicVariablesView($view);

		self::setBasicVariablesAdminView($view);
	}

	static public function setBasicVariablesAdminView($view)
	{
		$view->currentAdminMenuName = Piwik_GetCurrentAdminMenuName();

		$view->enableFrames = Piwik_Config::getInstance()->General['enable_framed_settings'];
		if (!$view->enableFrames) {
			$view->setXFrameOptions('sameorigin');
		}
		
		$view->isSuperUser = Piwik::isUserIsSuperUser();
		
		// for old geoip plugin warning
		$view->usingOldGeoIPPlugin = Piwik_PluginsManager::getInstance()->isPluginActivated('GeoIP');
		
		// for cannot find installed plugin warning
		$missingPlugins = false;
		if (isset(Piwik_Config::getInstance()->Plugins['Plugins']))
		{
			foreach (Piwik_Config::getInstance()->Plugins['Plugins'] as $pluginName)
			{
				// if a plugin is listed in the config, but is not loaded, it does not exist in the folder
				if (!Piwik_PluginsManager::getInstance()->isPluginLoaded($pluginName))
				{
					$missingPlugins[] = $pluginName;
				}
			}
		}
		if (!empty($missingPlugins))
		{
			$pluginsLink = Piwik_Url::getCurrentQueryStringWithParametersModified(array(
				'module' => 'CorePluginsAdmin', 'action' => 'index'
			));
			$view->missingPluginsWarning = Piwik_Translate('CoreAdminHome_MissingPluginsWarning', array(
				'<strong>'.implode('</strong>,&nbsp;<strong>', $missingPlugins).'</strong>',
				'<a href="'.$pluginsLink.'"/>',
				'</a>'
			));
		}
	}
}
