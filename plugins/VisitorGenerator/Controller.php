<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugin
 * @package Piwik_VisitorGenerator
 */

/**
 *
 * @package Piwik_VisitorGenerator
 */
class Piwik_VisitorGenerator_Controller extends Piwik_Controller {

	public function index() {
		Piwik::checkUserIsSuperUser();

		$sitesList = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();

		$view = Piwik_View::factory('index');
		$this->setBasicVariablesView($view);
		$view->assign('sitesList', $sitesList);

		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}

	public function generate() {
		// Only admin is allowed to do this!
		Piwik::checkUserIsSuperUser();
		$this->checkTokenInUrl();

		$GET = $_GET;
		$POST = $_POST;
		$COOKIE = $_COOKIE;
		$REQUEST = $_REQUEST;

		if(Piwik_Common::getRequestVar('choice', 'no') != 'yes') {
			Piwik::redirectToModule('VisitorGenerator', 'index');
		}

		$minVisitors = Piwik_Common::getRequestVar('minVisitors', 20, 'int');
		$maxVisitors = Piwik_Common::getRequestVar('maxVisitors', 100, 'int');
		$nbActions = Piwik_Common::getRequestVar('nbActions', 10, 'int');
		$daysToCompute = Piwik_Common::getRequestVar('daysToCompute', 1, 'int');
		$idSite = Piwik_Common::getRequestVar('idSite');
		Piwik::setMaxExecutionTime(0);

		$loadedPlugins = Piwik_PluginsManager::getInstance()->getLoadedPlugins();
		$loadedPlugins = array_keys($loadedPlugins);
		// we have to unload the Provider plugin otherwise it tries to lookup the IP for a hostname, and there is no dns server here
		if(Piwik_PluginsManager::getInstance()->isPluginActivated('Provider')) {
			Piwik_PluginsManager::getInstance()->unloadPlugin('Provider');
		}

		// we set the DO NOT load plugins so that the Tracker generator doesn't load the plugins we've just disabled.
		// if for some reasons you want to load the plugins, comment this line, and disable the plugin Provider in the plugins interface
		Piwik_PluginsManager::getInstance()->doNotLoadPlugins();

		$generator = new Piwik_VisitorGenerator_Generator();
		$generator->setMaximumUrlDepth(3);

		//$generator->disableProfiler();
		$generator->setIdSite( $idSite );

		$nbActionsTotal = 0;
		//$generator->emptyAllLogTables();
		$generator->init();

		$timer = new Piwik_Timer;

		$startTime = time() - ($daysToCompute-1)*86400;
		$dates = array();
		while($startTime <= time()) {
			$visitors = rand($minVisitors, $maxVisitors);
			$actions = $nbActions;
			$generator->setTimestampToUse($startTime);

			$nbActionsTotalThisDay = $generator->generate($visitors, $actions);
			$actionsPerVisit = round($nbActionsTotalThisDay / $visitors);

			$date = array();
			$date['visitors'] = $visitors;
			$date['actionsPerVisit'] = $actionsPerVisit;
			$date['startTime'] = $startTime;
			$dates[] = $date;

			$startTime += 86400;
			$nbActionsTotal += $nbActionsTotalThisDay;
			//sleep(1);
		}

		$generator->end();

		// Recover all super globals
		$_GET = $GET;
		$_POST = $POST;
		$_COOKIE = $COOKIE;
		$_REQUEST = $REQUEST;
		
		// Reload plugins
		Piwik_PluginsManager::getInstance()->loadPlugins($loadedPlugins);
		
		// Init view
		$view = Piwik_View::factory('generate');
		$view->menu = Piwik_GetAdminMenu();
		$this->setBasicVariablesView($view);
		$view->assign('dates', $dates);
		$view->assign('timer', $timer);
		$view->assign('nbActionsTotal', $nbActionsTotal);
		$view->assign('nbRequestsPerSec', round($nbActionsTotal / $timer->getTime(),0));
		echo $view->render();
	}
}
