<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->getSitesInfo($isWidgetized = false);
    }

    public function standalone()
    {
        return $this->getSitesInfo($isWidgetized = true);
    }

    public function getSitesInfo($isWidgetized = false)
    {
        Piwik::checkUserHasSomeViewAccess();

        $date   = Common::getRequestVar('date', 'today');
        $period = Common::getRequestVar('period', 'day');

        $view = new View("@MultiSites/getSitesInfo");

        $view->isWidgetized         = $isWidgetized;
        $view->displayRevenueColumn = Common::isGoalPluginEnabled();
        $view->limit                = Config::getInstance()->General['all_websites_website_per_page'];
        $view->show_sparklines      = Config::getInstance()->General['show_multisites_sparklines'];

        $view->autoRefreshTodayReport = 0;
        // if the current date is today, or yesterday,
        // in case the website is set to UTC-12), or today in UTC+14, we refresh the page every 5min
        if (in_array($date, array('today', date('Y-m-d'),
                                  'yesterday', Date::factory('yesterday')->toString('Y-m-d'),
                                  Date::factory('now', 'UTC+14')->toString('Y-m-d')))
        ) {
            $view->autoRefreshTodayReport = Config::getInstance()->General['multisites_refresh_after_seconds'];
        }

        $params = $this->getGraphParamsModified();
        $view->dateSparkline = $period == 'range' ? $date : $params['date'];

        $this->setGeneralVariablesView($view);

        return $view->render();
    }

    public function getEvolutionGraph($columns = false)
    {
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns');
        }
        $api = "API.get";

        if ($columns == 'revenue') {
            $api = "Goals.get";
        }
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, $api);
        return $this->renderView($view);
    }
}
