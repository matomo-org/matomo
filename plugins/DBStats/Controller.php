<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_DBStats
 */

class Piwik_DBStats_Controller extends Piwik_Controller
{
	function index()
	{
		$view = new Piwik_View('DBStats/templates/DBStats.tpl');
		$view->tablesStatus = Piwik_DBStats_API::getAllTablesStatus();
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();		
	}
}
