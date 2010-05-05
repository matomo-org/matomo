<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Dashboard
 */

/**
 *
 * @package Piwik_Dashboard
 */
class Piwik_Dashboard extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Dashboard',
			'description' => Piwik_Translate('Dashboard_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}

	public function getListHooksRegistered()
	{
		return array( 
			'template_js_import' => 'js',
			'template_css_import' => 'css',
			'UsersManager.deleteUser' => 'deleteDashboardLayout',
		);
	}

	function js()
	{
		echo '
<script type="text/javascript" src="plugins/Dashboard/templates/widgetMenu.js"></script>
<script type="text/javascript" src="libs/javascript/json2.js"></script>
<script type="text/javascript" src="plugins/Dashboard/templates/Dashboard.js"></script>
		';
	}

	function css()
	{
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"plugins/Dashboard/templates/dashboard.css\" />\n";
	}

	function deleteDashboardLayout($notification)
	{
		$userLogin = $notification->getNotificationObject();
		Piwik_Query('DELETE FROM ' . Piwik::prefixTable('user_dashboard') . ' WHERE login = ?', array($userLogin));
	}

	public function install()
	{
		// we catch the exception
		try{
			$sql = "CREATE TABLE ". Piwik::prefixTable('user_dashboard')." (
					login VARCHAR( 100 ) NOT NULL ,
					iddashboard INT NOT NULL ,
					layout TEXT NOT NULL,
					PRIMARY KEY ( login , iddashboard )
					)  DEFAULT CHARSET=utf8 " ;
			Piwik_Exec($sql);
		} catch(Exception $e){
			// mysql code error 1050:table already exists
			// see bug #153 http://dev.piwik.org/trac/ticket/153
			if(!Zend_Registry::get('db')->isErrNo($e, '1050'))
			{
				throw $e;
			}
		}
	}
	
	public function uninstall()
	{
		$sql = "DROP TABLE ". Piwik::prefixTable('user_dashboard') ;
		Piwik_Exec($sql);		
	}
	
}

Piwik_AddMenu('Dashboard_Dashboard', '', array('module' => 'Dashboard', 'action' => 'embeddedIndex'));
