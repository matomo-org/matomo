<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: Controller.php 4451 2011-04-14 19:00:39Z vipsoft $
 *
 * @category Piwik_Plugins
 * @package Piwik_ClearCache
 */

/**
 * @package Piwik_ClearCache
 */
class Piwik_ClearCache_Controller extends Piwik_Controller_Admin
{
	function index()
	{
		$pwd = explode('\\', $_SERVER['SCRIPT_FILENAME']);
		$pwd = $pwd[0]."\\".$pwd[1]."\\".$pwd[2];
		$directories = glob($pwd."\\tmp\*" , GLOB_ONLYDIR);

		for($i=0; $i < sizeof($directories); $i++)
		{
			if(strstr($_SERVER['OS'], "Windows"))
			{
				system("rmdir ".$directories[$i]." /s /q");
			} else {
				system("rm -rf ".$directories[$i]);
			}
		}
		Piwik_Url::redirectToReferer();
	}
}