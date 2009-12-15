<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_SecurityInfo
 */

/**
 * @package Piwik_SecurityInfo
 */
class Piwik_SecurityInfo_Controller extends Piwik_Controller
{
	function index()
	{
		Piwik::checkUserIsSuperUser();

		require_once(dirname(__FILE__) . '/PhpSecInfo/PhpSecInfo.php');

		// instantiate the class
		$psi = new PhpSecInfo();

		// load and run all tests
		$psi->loadAndRun();

		// grab the results as a multidimensional array
		$results = $psi->getResultsAsArray();

		// suppress results
		unset($results['test_results']['Core']['post_max_size']);
		unset($results['test_results']['Core']['upload_max_filesize']);

		$view = Piwik_View::factory('index');
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		$view->results = $results;
		echo $view->render();
	}
}
