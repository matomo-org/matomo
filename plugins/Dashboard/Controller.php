<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Common;
use Piwik\DataTable\Renderer\Json;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Session\SessionNamespace;
use Piwik\View;
use Piwik\WidgetsList;
use Piwik\FrontController;

/**
 * Dashboard Controller
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var Dashboard
     */
    private $dashboard;

    protected function init()
    {
        parent::init();

        $this->dashboard = new Dashboard();
    }

    protected function _getDashboardView($template)
    {
        $view = new View($template);
        $this->setGeneralVariablesView($view);

        $view->availableWidgets = Common::json_encode(WidgetsList::get());
        $view->availableLayouts = $this->getAvailableLayouts();

        $view->dashboardId = Common::getRequestVar('idDashboard', 1, 'int');

        // get the layout via FrontController so controller events are posted
        $view->dashboardLayout = FrontController::getInstance()->dispatch('Dashboard', 'getDashboardLayout',
            array($checkToken = false));

        return $view;
    }

    public function embeddedIndex()
    {
        $view = $this->_getDashboardView('@Dashboard/embeddedIndex');
        return $view->render();
    }

    public function index()
    {
        $view = $this->_getDashboardView('@Dashboard/index');
        $view->dashboardSettingsControl = new DashboardManagerControl();
        $view->dashboards = array();
        if (!Piwik::isUserIsAnonymous()) {
            $login = Piwik::getCurrentUserLogin();

            $view->dashboards = $this->dashboard->getAllDashboards($login);
        }
        return $view->render();
    }

    public function getAvailableWidgets()
    {
        $this->checkTokenInUrl();

        Json::sendHeaderJSON();
        return Common::json_encode(WidgetsList::get());
    }

    public function getDashboardLayout($checkToken = true)
    {
        if ($checkToken) {
            $this->checkTokenInUrl();
        }

        $idDashboard = Common::getRequestVar('idDashboard', 1, 'int');

        $layout = $this->getLayout($idDashboard);

        return $layout;
    }

    /**
     * Resets the dashboard to the default widget configuration
     */
    public function resetLayout()
    {
        $this->checkTokenInUrl();
        $layout = $this->dashboard->getDefaultLayout();
        $idDashboard = Common::getRequestVar('idDashboard', 1, 'int');
        if (Piwik::isUserIsAnonymous()) {
            $session = new SessionNamespace("Dashboard");
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
     * @param int $idDashboard
     * @param string $layout
     */
    protected function saveLayoutForUser($login, $idDashboard, $layout)
    {
        $paramsBind = array($login, $idDashboard, $layout, $layout);
        $query = sprintf('INSERT INTO %s (login, iddashboard, layout) VALUES (?,?,?) ON DUPLICATE KEY UPDATE layout=?',
            Common::prefixTable('user_dashboard'));
        Db::query($query, $paramsBind);
    }

    /**
     * Updates the name of a dashboard
     *
     * @param string $login
     * @param int $idDashboard
     * @param string $name
     */
    protected function updateDashboardName($login, $idDashboard, $name)
    {
        $paramsBind = array($name, $login, $idDashboard);
        $query = sprintf('UPDATE %s SET name = ? WHERE login = ? AND iddashboard = ?',
            Common::prefixTable('user_dashboard'));
        Db::query($query, $paramsBind);
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

        $idDashboard = Common::getRequestVar('idDashboard', 1, 'int');

        // first layout can't be removed
        if ($idDashboard != 1) {
            $query = sprintf('DELETE FROM %s WHERE iddashboard = ? AND login = ?',
                Common::prefixTable('user_dashboard'));
            Db::query($query, array($idDashboard, Piwik::getCurrentUserLogin()));
        }
    }

    /**
     * Outputs all available dashboards for the current user as a JSON string
     */
    public function getAllDashboards()
    {
        $this->checkTokenInUrl();

        if (Piwik::isUserIsAnonymous()) {
            Json::sendHeaderJSON();
            return '[]';
        }

        $login = Piwik::getCurrentUserLogin();
        $dashboards = $this->dashboard->getAllDashboards($login);

        Json::sendHeaderJSON();
        return Common::json_encode($dashboards);
    }

    /**
     * Creates a new dashboard for the current user
     * User needs to be logged in
     */
    public function createNewDashboard()
    {
        $this->checkTokenInUrl();

        if (Piwik::isUserIsAnonymous()) {
            return '0';
        }
        $user = Piwik::getCurrentUserLogin();
        $nextId = $this->getNextIdDashboard($user);

        $name = urldecode(Common::getRequestVar('name', '', 'string'));
        $type = urldecode(Common::getRequestVar('type', 'default', 'string'));
        $layout = '{}';

        if ($type == 'default') {
            $layout = $this->dashboard->getDefaultLayout();
        }

        $query = sprintf('INSERT INTO %s (login, iddashboard, name, layout) VALUES (?, ?, ?, ?)',
            Common::prefixTable('user_dashboard'));
        Db::query($query, array($user, $nextId, $name, $layout));

        Json::sendHeaderJSON();
        return Common::json_encode($nextId);
    }

    private function getNextIdDashboard($login)
    {
        $nextIdQuery = sprintf('SELECT MAX(iddashboard)+1 FROM %s WHERE login = ?',
            Common::prefixTable('user_dashboard'));
        $nextId = Db::fetchOne($nextIdQuery, array($login));

        if (empty($nextId)) {
            $nextId = 1;
            return $nextId;
        }
        return $nextId;
    }

    public function copyDashboardToUser()
    {
        $this->checkTokenInUrl();

        if (!Piwik::hasUserSuperUserAccess()) {
            return '0';
        }
        $login = Piwik::getCurrentUserLogin();
        $name = urldecode(Common::getRequestVar('name', '', 'string'));
        $user = urldecode(Common::getRequestVar('user', '', 'string'));
        $idDashboard = Common::getRequestVar('dashboardId', 0, 'int');
        $layout = $this->dashboard->getLayoutForUser($login, $idDashboard);

        if ($layout !== false) {
            $nextId = $this->getNextIdDashboard($user);

            $query = sprintf('INSERT INTO %s (login, iddashboard, name, layout) VALUES (?, ?, ?, ?)',
                Common::prefixTable('user_dashboard'));
            Db::query($query, array($user, $nextId, $name, $layout));

            Json::sendHeaderJSON();
            return Common::json_encode($nextId);
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

        $layout = Common::unsanitizeInputValue(Common::getRequestVar('layout'));
        $idDashboard = Common::getRequestVar('idDashboard', 1, 'int');
        $name = Common::getRequestVar('name', '', 'string');
        if (Piwik::isUserIsAnonymous()) {
            $session = new SessionNamespace("Dashboard");
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

        if (Piwik::hasUserSuperUserAccess()) {
            $layout = Common::unsanitizeInputValue(Common::getRequestVar('layout'));
            $paramsBind = array('', '1', $layout, $layout);
            $query = sprintf('INSERT INTO %s (login, iddashboard, layout) VALUES (?,?,?) ON DUPLICATE KEY UPDATE layout=?',
                Common::prefixTable('user_dashboard'));
            Db::query($query, $paramsBind);
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

            $session = new SessionNamespace("Dashboard");
            if (!isset($session->dashboardLayout)) {

                return $this->dashboard->getDefaultLayout();
            }

            $layout = $session->dashboardLayout;
        } else {
            $layout = $this->dashboard->getLayoutForUser(Piwik::getCurrentUserLogin(), $idDashboard);
        }

        if (!empty($layout)) {
            $layout = $this->dashboard->removeDisabledPluginFromLayout($layout);
        }

        if (empty($layout)) {
            $layout = $this->dashboard->getDefaultLayout();
        }

        return $layout;
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


