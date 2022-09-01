<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link     https://matomo.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable\Renderer\Json;
use Piwik\Piwik;
use Piwik\Session\SessionNamespace;
use Piwik\View;

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

        $view->availableLayouts = $this->getAvailableLayouts();
        $view->hasSomeAdminAccess = Piwik::isUserHasSomeAdminAccess();

        $view->dashboardId = Common::getRequestVar('idDashboard', 1, 'int');

        return $view;
    }

    // this
    public function embeddedIndex()
    {
        $view = $this->_getDashboardView('@Dashboard/embeddedIndex');
        return $view->render();
    }

    // this is the exported widget
    public function index()
    {
        $view = $this->_getDashboardView('@Dashboard/index');
        $view->dashboardSettingsControl = new DashboardManagerControl();
        $view->hasSomeAdminAccess = Piwik::isUserHasSomeAdminAccess();
        $view->dashboards = array();
        if (!Piwik::isUserIsAnonymous()) {
            $login = Piwik::getCurrentUserLogin();

            $view->dashboards = $this->dashboard->getAllDashboards($login);
        }
        return $view->render();
    }

    public function getDashboardLayout($checkToken = true)
    {
        if ($checkToken) {
            $this->checkTokenInUrl();
        }

        $idDashboard = Common::getRequestVar('idDashboard', 1, 'int');

        $layout = $this->getLayout($idDashboard);

        Json::sendHeaderJSON();
        return $layout;
    }

    /**
     * Resets the dashboard to the default widget configuration
     */
    public function resetLayout()
    {
        $this->checkTokenInUrl();
        if (Piwik::isUserIsAnonymous()) {
            $session = new SessionNamespace("Dashboard");
            $session->dashboardLayout = $this->dashboard->getDefaultLayout();
            $session->setExpirationSeconds(1800);
        } else {
            Request::processRequest('Dashboard.resetDashboardLayout');
        }
    }

    private function getModel()
    {
        return new Model();
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

        $login      = Piwik::getCurrentUserLogin();
        $dashboards = $this->dashboard->getAllDashboards($login);

        Json::sendHeaderJSON();
        return json_encode($dashboards);
    }

    /**
     * Saves the layout for the current user
     * anonymous = in the session
     * authenticated user = in the DB
     */
    public function saveLayout()
    {
        $this->checkTokenInUrl();

        $layout      = Common::unsanitizeInputValue(Common::getRequestVar('layout'));
        $layout      = strip_tags($layout);
        $idDashboard = Common::getRequestVar('idDashboard', 1, 'int');
        $name        = Common::getRequestVar('name', '', 'string');

        if (Piwik::isUserIsAnonymous()) {
            $session = new SessionNamespace("Dashboard");
            $session->dashboardLayout = $layout;
            $session->setExpirationSeconds(1800);
        } else {
            $this->getModel()->createOrUpdateDashboard(Piwik::getCurrentUserLogin(), $idDashboard, $layout);
            if (!empty($name)) {
                $this->getModel()->updateDashboardName(Piwik::getCurrentUserLogin(), $idDashboard, $name);
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
            $layout = strip_tags($layout);
            $this->getModel()->createOrUpdateDashboard('', '1', $layout);
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

        if (empty($layout)) {
            $layout = $this->dashboard->getDefaultLayout();
        }

        if (!empty($layout)) {
            $layout = $this->dashboard->removeDisabledPluginFromLayout($layout);
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

