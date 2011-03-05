<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_UsersManager
 */

/**
 * Manage Piwik users
 *
 * @package Piwik_UsersManager
 */
class Piwik_UsersManager extends Piwik_Plugin
{
	/**
	 * Plugin information
	 *
	 * @see Piwik_Plugin
	 *
	 * @return array
	 */
	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('UsersManager_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);

		return $info;
	}

	/**
	 * Get list of hooks to register.
	 *
	 * @see Piwik_PluginsManager.loadPlugin()
	 *
	 * @return array
	 */
	function getListHooksRegistered()
	{
		return array(
				'AdminMenu.add' => 'addMenu',
				'AssetManager.getJsFiles' => 'getJsFiles',
				'SitesManager.deleteSite' => 'deleteSite',
		);
	}

	/**
	 * Delete user preferences associated with a particular site
	 *
	 * @param Event_Notification $notification
	 */
	function deleteSite( $notification )
	{
		$idSite = &$notification->getNotificationObject();

		Piwik_Option::getInstance()->deleteLike('%\_'.Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT, $idSite);
	}

	/**
	 * Return list of plug-in specific JavaScript files to be imported by the asset manager
	 *
	 * @see Piwik_AssetManager
	 *
	 * @param Event_Notification $notification
	 */
	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();

		$jsFiles[] = "plugins/UsersManager/templates/UsersManager.js";
		$jsFiles[] = "plugins/UsersManager/templates/userSettings.js";
	}

	/**
	 * Add admin menu items
	 *
	 * @param Event_Notification $notification (not used)
	 */
	function addMenu()
	{
		Piwik_AddAdminMenu('UsersManager_MenuUsers', 
							array('module' => 'UsersManager', 'action' => 'index'),
							Piwik::isUserHasSomeAdminAccess(),
							$order = 3);
		Piwik_AddAdminMenu('UsersManager_MenuUserSettings', 
							array('module' => 'UsersManager', 'action' => 'userSettings'),
							Piwik::isUserHasSomeViewAccess(),
							$order = 1);
	}
}
