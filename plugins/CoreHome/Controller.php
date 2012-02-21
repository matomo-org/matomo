<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 *
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_Controller extends Piwik_Controller
{
	function getDefaultAction()
	{
		return 'redirectToCoreHomeIndex';
	}
	
	function redirectToCoreHomeIndex()
	{
		$defaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
		$module = 'CoreHome';
		$action = 'index';
		
		// User preference: default report to load is the All Websites dashboard
		if($defaultReport == 'MultiSites' 
			&& Piwik_PluginsManager::getInstance()->isPluginActivated('MultiSites'))
		{
			$module = 'MultiSites';
		}
		if($defaultReport == Piwik::getLoginPluginName())
		{
			$module = Piwik::getLoginPluginName();
		}
		$idSite = Piwik_Common::getRequestVar('idSite', false, 'int');
		
		parent::redirectToIndex($module, $action, !empty($idSite) ? $idSite : null );
	}
	
	public function showInContext()
	{
		$controllerName = Piwik_Common::getRequestVar('moduleToLoad');
		$actionName = Piwik_Common::getRequestVar('actionToLoad', 'index');
		$view = $this->getDefaultIndexView();
		$view->content = Piwik_FrontController::getInstance()->fetchDispatch( $controllerName, $actionName );
		echo $view->render();	
	}
	
	protected function getDefaultIndexView()
	{
		$view = Piwik_View::factory('index');
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetMenu();
		$view->content = '';
		return $view;
	}
	
	protected function setDateTodayIfWebsiteCreatedToday()
	{
		$date = Piwik_Common::getRequestVar('date', false);
		if($date == 'today'
			|| Piwik_Common::getRequestVar('period', false) == 'range') 
		{
			return;
		} 
		$websiteId = Piwik_Common::getRequestVar('idSite', false, 'int');
		if ($websiteId) 
		{
			$website = new Piwik_Site($websiteId);
			$datetimeCreationDate = $this->site->getCreationDate()->getDatetime();
			$creationDateLocalTimezone = Piwik_Date::factory($datetimeCreationDate, $website->getTimezone())->toString('Y-m-d');
			$todayLocalTimezone = Piwik_Date::factory('now', $website->getTimezone())->toString('Y-m-d');
			if( $creationDateLocalTimezone == $todayLocalTimezone ) 
			{
				Piwik::redirectToModule( 'CoreHome', 'index', 
										array(	'date' => 'today', 
												'idSite' => $websiteId, 
												'period' => Piwik_Common::getRequestVar('period')) 
				);
			}
		}
	}
	
	public function index()
	{
		$this->setDateTodayIfWebsiteCreatedToday();
		$view = $this->getDefaultIndexView();
		echo $view->render();
	}
	
	/*
	 * This method is called when the asset manager is configured in merged mode.
	 * It returns the content of the css merged file.
	 * 
	 * @see core/AssetManager.php
	 */
	public function getCss ()
	{
		$cssMergedFile = Piwik_AssetManager::getMergedCssFileLocation();
		Piwik::serveStaticFile($cssMergedFile, "text/css");
	}
	
	/*
	 * This method is called when the asset manager is configured in merged mode.
	 * It returns the content of the js merged file.
	 * 
	 * @see core/AssetManager.php
	 */
	public function getJs ()
	{
		$jsMergedFile = Piwik_AssetManager::getMergedJsFileLocation();
		Piwik::serveStaticFile($jsMergedFile, "application/javascript; charset=UTF-8");
	}
	
	
	//  --------------------------------------------------------
	//  ROW EVOLUTION
	//  The following methods render the popup that shows the
	//  evolution of a singe or multiple rows in a data table
	//  --------------------------------------------------------
	
	/**
	 * This static cache is necessary because the signature cannot be modified
	 * if the method renders a ViewDataTable. So we use it to pass information
	 * to getRowEvolutionGraph()
	 * @var Piwik_CoreHome_DataTableAction_Evolution
	 */
	private static $rowEvolutionCache = null;
	
	/** Render the entire row evolution popup for a single row */
	public function getRowEvolutionPopup()
	{
		$rowEvolution = new Piwik_CoreHome_DataTableAction_RowEvolution($this->idSite, $this->date);
		self::$rowEvolutionCache = $rowEvolution;
		$view = Piwik_View::factory('popup_rowevolution');
		echo $rowEvolution->renderPopup($this, $view);
	}
	
	/** Render the entire row evolution popup for multiple rows */
	public function getMultiRowEvolutionPopup()
	{
		$rowEvolution = new Piwik_CoreHome_DataTableAction_MultiRowEvolution($this->idSite, $this->date);
		self::$rowEvolutionCache = $rowEvolution;
		$view = Piwik_View::factory('popup_multirowevolution');
		echo $rowEvolution->renderPopup($this, $view);
	}
	
	/** Generic method to get an evolution graph or a sparkline for the row evolution popup */
	public function getRowEvolutionGraph($fetch = false)
	{
		$rowEvolution = self::$rowEvolutionCache;
		$view = $rowEvolution->getRowEvolutionGraph();
		return $this->renderView($view, $fetch);
	}
	
}
