<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Dashboard
 */

/**
 *
 * @package Piwik_Dashboard
 */
class Piwik_Dashboard_Controller extends Piwik_Controller
{
	protected function getDashboardView($template)
	{
		$view = Piwik_View::factory($template);
		$this->setGeneralVariablesView($view);

		$view->availableWidgets = Piwik_Common::json_encode(Piwik_GetWidgetsList());
		$view->availableLayouts = $this->getAvailableLayouts();
		
		$layout            = $this->getLayout();
		$view->layout      = $layout;
		$view->dashboardId = 1;
		return $view;
	}
	
	public function embeddedIndex()
	{
		$view = $this->getDashboardView('index');
		echo $view->render();
	}
	
	public function index()
	{
		$view = $this->getDashboardView('standalone');
		echo $view->render();
	}
	
	public function getAvailableWidgets()
	{
		$this->checkTokenInUrl();
	    echo json_encode(Piwik_GetWidgetsList());
	}
	
	public function resetLayout() 
	{
		$this->checkTokenInUrl();
		$layout = $this->getDefaultLayout();
		$idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int' );
		if(Piwik::isUserIsAnonymous())
		{
			$session = new Piwik_Session_Namespace("Piwik_Dashboard");
			$session->dashboardLayout = $layout;
			$session->setExpirationSeconds(1800);
		}
		else
		{
		    $this->saveLayoutForUser(Piwik::getCurrentUserLogin(),$idDashboard, $layout);
		}
	}
	
	/**
	 * Records the layout in the DB for the given user.
	 *
	 * @param string $login
	 * @param int $idDashboard
	 * @param string $layout
	 */
	protected function saveLayoutForUser( $login, $idDashboard, $layout)
	{
		$paramsBind = array($login, $idDashboard, $layout, $layout);
		Piwik_Query('INSERT INTO '.Piwik_Common::prefixTable('user_dashboard') .
					' (login, iddashboard, layout)
						VALUES (?,?,?)
					ON DUPLICATE KEY UPDATE layout=?',
					$paramsBind);
	}
	
	/**
	 * Returns the layout in the DB for the given user, or false if the layout has not been set yet.
	 * Parameters must be checked BEFORE this function call
	 *
	 * @param string $login
	 * @param int $idDashboard
	 * @param string|false $layout
	 */
	protected function getLayoutForUser( $login, $idDashboard)
	{
		$paramsBind = array($login, $idDashboard);
		$return = Piwik_FetchAll('SELECT layout 
								FROM '.Piwik_Common::prefixTable('user_dashboard') .
								' WHERE login = ? 
									AND iddashboard = ?', $paramsBind);
		if(count($return) == 0)
		{
			return false;
		}
		return $return[0]['layout'];
	}
	
	/**
	 * Saves the layout for the current user
	 * anonymous = in the session
	 * authenticated user = in the DB
	 */
	public function saveLayout()
	{
		$this->checkTokenInUrl();
		$layout = Piwik_Common::getRequestVar('layout');
		// Currently not used
		$idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int' );
		if(Piwik::isUserIsAnonymous())
		{
			$session = new Piwik_Session_Namespace("Piwik_Dashboard");
			$session->dashboardLayout = $layout;
			$session->setExpirationSeconds(1800);
		}
		else
		{
			$this->saveLayoutForUser(Piwik::getCurrentUserLogin(),$idDashboard, $layout);
		}
	}
	
	/**
	 * Get the dashboard layout for the current user (anonymous or loggued user) 
	 *
	 * @return string $layout
	 */
	protected function getLayout()
	{
		// Currently not used
		$idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int' );

		if(Piwik::isUserIsAnonymous())
		{
			$session = new Piwik_Session_Namespace("Piwik_Dashboard");
			if(!isset($session->dashboardLayout))
			{
				return $this->getDefaultLayout();
			}
			$layout = $session->dashboardLayout;
		}
		else
		{
			$layout = $this->getLayoutForUser(Piwik::getCurrentUserLogin(),$idDashboard);
		}
		if(!empty($layout))
		{
			// layout was JSON.stringified
			$layout = html_entity_decode($layout);
			$layout = str_replace("\\\"", "\"", $layout);
	
			$layout = $this->removeDisabledPluginFromLayout($layout);
		}
		return $layout;
	}
	
	protected function removeDisabledPluginFromLayout($layout)
	{
		$layout = str_replace("\n", "", $layout);
		// if the json decoding works (ie. new Json format)
		// we will only return the widgets that are from enabled plugins
		$layoutObject = Piwik_Common::json_decode($layout, $assoc = false);

		if(is_array($layoutObject)) {
			$layoutObject = (object) array(
			    'config'  => array( 'layout' => '33-33-33' ),
			    'columns' => $layoutObject
			);
		}
		
		if(empty($layoutObject))
		{
			$layoutObject = (object) array(
			    'config'  => array( 'layout' => '33-33-33' ),
			    'columns' => array()
			);
		}
		
		// return default layout if all columns are empty
		if(empty($layoutObject->columns[0]) && empty($layoutObject->columns[1]) && 
		   empty($layoutObject->columns[2]) && empty($layoutObject->columns[3])) {
		    return $this->getDefaultLayout();
		}
		
		foreach($layoutObject->columns as &$row) 
		{
			if(!is_array($row))
			{
				$row = array();
				continue;
			}

			foreach($row as $widgetId => $widget)
			{
				if(isset($widget->parameters->module)) {
					$controllerName = $widget->parameters->module;
					$controllerAction = $widget->parameters->action;
					if(!Piwik_IsWidgetDefined($controllerName, $controllerAction))
					{
						unset($row[$widgetId]);
					}
				}
				else
				{
					unset($row[$widgetId]);
				}
			}
		}
		$layout = Piwik_Common::json_encode($layoutObject);
		return $layout;
	}
	
	protected function getDefaultLayout()
	{
		$defaultLayout = '[
    		[
    			{"uniqueId":"widgetVisitsSummarygetEvolutionGraphcolumnsArray","parameters":{"module":"VisitsSummary","action":"getEvolutionGraph","columns":"nb_visits"}},
    			{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}},
    			{"uniqueId":"widgetVisitorInterestgetNumberOfVisitsPerVisitDuration","parameters":{"module":"VisitorInterest","action":"getNumberOfVisitsPerVisitDuration"}}
    		],
    		[
    			{"uniqueId":"widgetReferersgetKeywords","parameters":{"module":"Referers","action":"getKeywords"}},
    			{"uniqueId":"widgetReferersgetWebsites","parameters":{"module":"Referers","action":"getWebsites"}}
    		],
    		[
    			{"uniqueId":"widgetUserCountryMapworldMap","parameters":{"module":"UserCountryMap","action":"worldMap"}},
    			{"uniqueId":"widgetUserSettingsgetBrowser","parameters":{"module":"UserSettings","action":"getBrowser"}},
    			{"uniqueId":"widgetReferersgetSearchEngines","parameters":{"module":"Referers","action":"getSearchEngines"}},
    			{"uniqueId":"widgetVisitTimegetVisitInformationPerServerTime","parameters":{"module":"VisitTime","action":"getVisitInformationPerServerTime"}},
    			{"uniqueId":"widgetExampleRssWidgetrssPiwik","parameters":{"module":"ExampleRssWidget","action":"rssPiwik"}}
    		]
    	]';
		$defaultLayout = $this->removeDisabledPluginFromLayout($defaultLayout);
		return $defaultLayout;
	}
	
	protected function getAvailableLayouts()
	{
	    return array(
	        array(100),
	        array(50,50), array(75,25), array(25,75),
	        array(33,33,33), array(50,25,25), array(25,50,25), array(25,25,50),
	        array(25,25,25,25)
	    );
	}
}


