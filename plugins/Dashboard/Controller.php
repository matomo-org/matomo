<?php
/**
 * Piwik - Open source web analytics
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @category Piwik_Plugins
 * @package  Piwik_Dashboard
 */

/**
 * Dashboard Controller
 *
 * @package Piwik_Dashboard
 */
class Piwik_Dashboard_Controller extends Piwik_Controller
{
    protected function _getDashboardView($template)
    {
        $view = Piwik_View::factory($template);
        $this->setGeneralVariablesView($view);

        $view->availableWidgets = Piwik_Common::json_encode(Piwik_GetWidgetsList());
        $view->availableLayouts = $this->getAvailableLayouts();

        $view->dashboardId     = Piwik_Common::getRequestVar('idDashboard', 1, 'int');
        $view->dashboardLayout = $this->getLayout($view->dashboardId);

        return $view;
    }

    public function embeddedIndex()
    {
        $view = $this->_getDashboardView('index');

        echo $view->render();
    }

    public function index()
    {
        $view             = $this->_getDashboardView('standalone');
        $view->dashboards = array();
        if (!Piwik::isUserIsAnonymous()) {
            $login = Piwik::getCurrentUserLogin();

            $view->dashboards = Piwik_Dashboard::getAllDashboards($login);
        }
        echo $view->render();
    }

    public function getAvailableWidgets()
    {
        $this->checkTokenInUrl();

	    Piwik_DataTable_Renderer_Json::sendHeaderJSON();
        echo Piwik_Common::json_encode(Piwik_GetWidgetsList());
    }

    public function getDashboardLayout()
    {
        $this->checkTokenInUrl();

        $idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int');

        $layout = $this->getLayout($idDashboard);

        echo $layout;
    }

    /**
     * Resets the dashboard to the default widget configuration
     */
    public function resetLayout()
    {
        $this->checkTokenInUrl();
        $layout      = $this->getDefaultLayout();
        $idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int');
        if (Piwik::isUserIsAnonymous()) {
            $session                  = new Piwik_Session_Namespace("Piwik_Dashboard");
            $session->dashboardLayout = $layout;
            $session->setExpirationSeconds(1800);
        } else {
            $this->saveLayoutForUser(Piwik::getCurrentUserLogin(), $idDashboard, $layout);
        }
    }

    /**
     * Records the layout in the DB for the given user.
     *
     * @param string $login
     * @param int    $idDashboard
     * @param string $layout
     */
    protected function saveLayoutForUser($login, $idDashboard, $layout)
    {
        $paramsBind = array($login, $idDashboard, $layout, $layout);
        $query      = sprintf('INSERT INTO %s (login, iddashboard, layout) VALUES (?,?,?) ON DUPLICATE KEY UPDATE layout=?',
                              Piwik_Common::prefixTable('user_dashboard'));
        Piwik_Query($query, $paramsBind);
    }

    /**
     * Updates the name of a dashboard
     *
     * @param string $login
     * @param int    $idDashboard
     * @param string $name
     */
    protected function updateDashboardName($login, $idDashboard, $name)
    {
        $paramsBind = array($name, $login, $idDashboard);
        $query      = sprintf('UPDATE %s SET name = ? WHERE login = ? AND iddashboard = ?',
                              Piwik_Common::prefixTable('user_dashboard'));
        Piwik_Query($query, $paramsBind);
    }

    /**
     * Returns the layout in the DB for the given user, or false if the layout has not been set yet.
     * Parameters must be checked BEFORE this function call
     *
     * @param string $login
     * @param int    $idDashboard
     *
     * @return bool
     */
    protected function _getLayoutForUser($login, $idDashboard)
    {
        $paramsBind = array($login, $idDashboard);
        $query      = sprintf('SELECT layout FROM %s WHERE login = ? AND iddashboard = ?',
                              Piwik_Common::prefixTable('user_dashboard'));
        $return     = Piwik_FetchAll($query, $paramsBind);

        if (count($return) == 0) {
            return false;
        }

        return $return[0]['layout'];
    }

    /**
     * Removes the dashboard with the given id
     */
    public function removeDashboard()
    {
        $this->checkTokenInUrl();

        if (Piwik::isUserIsAnonymous()) {
            return;
        }

        $idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int');

        // first layout can't be removed
        if ($idDashboard != 1) {
            $query = sprintf('DELETE FROM %s WHERE iddashboard = ? AND login = ?',
                             Piwik_Common::prefixTable('user_dashboard'));
            Piwik_Query($query, array($idDashboard, Piwik::getCurrentUserLogin()));
        }
    }

    /**
     * Outputs all available dashboards for the current user as a JSON string
     */
    public function getAllDashboards()
    {
        $this->checkTokenInUrl();
        if (Piwik::isUserIsAnonymous()) {
	        echo '[]';
	        return;
        }
        $login = Piwik::getCurrentUserLogin();

        $dashboards = Piwik_Dashboard::getAllDashboards($login);

        Piwik_DataTable_Renderer_Json::sendHeaderJSON();
        echo Piwik_Common::json_encode($dashboards);
    }

    /**
     * Creates a new dashboard for the current user
     * User needs to be logged in
     */
    public function createNewDashboard()
    {
        $this->checkTokenInUrl();

        if (Piwik::isUserIsAnonymous()) {
	        echo '0';
	        return;
        }
        $user  = Piwik::getCurrentUserLogin();
        $nextId = $this->getNextIdDashboard($user);

        $name   = urldecode(Piwik_Common::getRequestVar('name', '', 'string'));
        $type   = urldecode(Piwik_Common::getRequestVar('type', 'default', 'string'));
        $layout = '{}';

        if ($type == 'default') {
            $layout = $this->getDefaultLayout();
        }

        $query = sprintf('INSERT INTO %s (login, iddashboard, name, layout) VALUES (?, ?, ?, ?)',
                         Piwik_Common::prefixTable('user_dashboard'));
        Piwik_Query($query, array($user, $nextId, $name, $layout));

        Piwik_DataTable_Renderer_Json::sendHeaderJSON();
        echo Piwik_Common::json_encode($nextId);
    }

	private function getNextIdDashboard($login)
	{
		$nextIdQuery = sprintf('SELECT MAX(iddashboard)+1 FROM %s WHERE login = ?',
			Piwik_Common::prefixTable('user_dashboard'));
		$nextId = Piwik_FetchOne($nextIdQuery, array($login));

		if (empty($nextId)) {
			$nextId = 1;
			return $nextId;
		}
		return $nextId;
	}

	public function copyDashboardToUser()
    {
        $this->checkTokenInUrl();

        if (!Piwik::isUserIsSuperUser()) {
	        echo '0';
	        return;
        }
        $login       = Piwik::getCurrentUserLogin();
        $name        = urldecode(Piwik_Common::getRequestVar('name', '', 'string'));
        $user        = urldecode(Piwik_Common::getRequestVar('user', '', 'string'));
        $idDashboard = Piwik_Common::getRequestVar('dashboardId', 0, 'int');
        $layout      = $this->_getLayoutForUser($login, $idDashboard);

        if($layout !== false) {
	        $nextId = $this->getNextIdDashboard($user);

	        $query = sprintf('INSERT INTO %s (login, iddashboard, name, layout) VALUES (?, ?, ?, ?)',
		        Piwik_Common::prefixTable('user_dashboard'));
	        Piwik_Query($query, array($user, $nextId, $name, $layout));

	        Piwik_DataTable_Renderer_Json::sendHeaderJSON();
	        echo Piwik_Common::json_encode($nextId);
	        return;
        }
    }

    /**
     * Saves the layout for the current user
     * anonymous = in the session
     * authenticated user = in the DB
     */
    public function saveLayout()
    {
        $this->checkTokenInUrl();

        $layout      = Piwik_Common::unsanitizeInputValue(Piwik_Common::getRequestVar('layout'));
        $idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int');
        $name        = Piwik_Common::getRequestVar('name', '', 'string');
        if (Piwik::isUserIsAnonymous()) {
            $session                  = new Piwik_Session_Namespace("Piwik_Dashboard");
            $session->dashboardLayout = $layout;
            $session->setExpirationSeconds(1800);
        } else {
            $this->saveLayoutForUser(Piwik::getCurrentUserLogin(), $idDashboard, $layout);
            if (!empty($name)) {
                $this->updateDashboardName(Piwik::getCurrentUserLogin(), $idDashboard, $name);
            }
        }
    }

    /**
     * Saves the layout as default
     */
    public function saveLayoutAsDefault()
    {
        $this->checkTokenInUrl();

        if (Piwik::isUserIsSuperUser()) {
            $layout     = Piwik_Common::unsanitizeInputValue(Piwik_Common::getRequestVar('layout'));
            $paramsBind = array('', '1', $layout, $layout);
            $query      = sprintf('INSERT INTO %s (login, iddashboard, layout) VALUES (?,?,?) ON DUPLICATE KEY UPDATE layout=?',
                                  Piwik_Common::prefixTable('user_dashboard'));
            Piwik_Query($query, $paramsBind);
        }
    }

    /**
     * Get the dashboard layout for the current user (anonymous or logged user)
     *
     * @param int $idDashboard
     *
     * @return string $layout
     */
    protected function getLayout($idDashboard)
    {
        if (Piwik::isUserIsAnonymous()) {
            $session = new Piwik_Session_Namespace("Piwik_Dashboard");
            if (!isset($session->dashboardLayout)) {
                return $this->getDefaultLayout();
            }
            $layout = $session->dashboardLayout;
        } else {
            $layout = $this->_getLayoutForUser(Piwik::getCurrentUserLogin(), $idDashboard);
        }
        if (!empty($layout)) {
            $layout = $this->removeDisabledPluginFromLayout($layout);
        }

        if (empty($layout)) {
            $layout = $this->getDefaultLayout();
        }
        return $layout;
    }

    protected function removeDisabledPluginFromLayout($layout)
    {
        $layout = str_replace("\n", "", $layout);
        // if the json decoding works (ie. new Json format)
        // we will only return the widgets that are from enabled plugins
        $layoutObject = Piwik_Common::json_decode($layout, $assoc = false);

        if (is_array($layoutObject)) {
            $layoutObject = (object)array(
                'config'  => array('layout' => '33-33-33'),
                'columns' => $layoutObject
            );
        }

        if (empty($layoutObject) || empty($layoutObject->columns)) {
            $layoutObject = (object)array(
                'config'  => array('layout' => '33-33-33'),
                'columns' => array()
            );
        }

        foreach ($layoutObject->columns as &$row) {
            if (!is_array($row)) {
                $row = array();
                continue;
            }

            foreach ($row as $widgetId => $widget) {
                if (isset($widget->parameters->module)) {
                    $controllerName   = $widget->parameters->module;
                    $controllerAction = $widget->parameters->action;
                    if (!Piwik_IsWidgetDefined($controllerName, $controllerAction)) {
                        unset($row[$widgetId]);
                    }
                } else {
                    unset($row[$widgetId]);
                }
            }
        }
        $layout = Piwik_Common::json_encode($layoutObject);
        return $layout;
    }

    protected function getDefaultLayout()
    {
        $defaultLayout = $this->_getLayoutForUser('', 1);

        if (empty($defaultLayout)) {
        	$topWidget = '';
        	if (Piwik::isUserIsSuperUser())
        	{
        		$topWidget = '{"uniqueId":"widgetCoreHomegetDonateForm",'
        				   .  '"parameters":{"module":"CoreHome","action":"getDonateForm"}},';
        	}
        	else
        	{
        		$topWidget = '{"uniqueId":"widgetCoreHomegetPromoVideo",'
        				   .  '"parameters":{"module":"CoreHome","action":"getPromoVideo"}},';
        	}
        	
            $defaultLayout = '[
                [
                    {"uniqueId":"widgetVisitsSummarygetEvolutionGraphcolumnsArray","parameters":{"module":"VisitsSummary","action":"getEvolutionGraph","columns":"nb_visits"}},
                    {"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}},
                    {"uniqueId":"widgetVisitorInterestgetNumberOfVisitsPerVisitDuration","parameters":{"module":"VisitorInterest","action":"getNumberOfVisitsPerVisitDuration"}}
                ],
                [
                	'.$topWidget.'
                    {"uniqueId":"widgetReferersgetKeywords","parameters":{"module":"Referers","action":"getKeywords"}},
                    {"uniqueId":"widgetReferersgetWebsites","parameters":{"module":"Referers","action":"getWebsites"}}
                ],
                [
                    {"uniqueId":"widgetUserCountryMapvisitorMap","parameters":{"module":"UserCountryMap","action":"visitorMap"}},
                    {"uniqueId":"widgetUserSettingsgetBrowser","parameters":{"module":"UserSettings","action":"getBrowser"}},
                    {"uniqueId":"widgetReferersgetSearchEngines","parameters":{"module":"Referers","action":"getSearchEngines"}},
                    {"uniqueId":"widgetVisitTimegetVisitInformationPerServerTime","parameters":{"module":"VisitTime","action":"getVisitInformationPerServerTime"}},
                    {"uniqueId":"widgetExampleRssWidgetrssPiwik","parameters":{"module":"ExampleRssWidget","action":"rssPiwik"}}
                ]
            ]';
        }
        $defaultLayout = $this->removeDisabledPluginFromLayout($defaultLayout);
        return $defaultLayout;
    }

    /**
     * Returns all available column layouts for the dashboard
     *
     * @return array
     */
    protected function getAvailableLayouts()
    {
        return array(
            array(100),
            array(50, 50), array(67, 33), array(33, 67),
            array(33, 33, 33), array(40, 30, 30), array(30, 40, 30), array(30, 30, 40),
            array(25, 25, 25, 25)
        );
    }

}


