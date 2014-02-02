<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\MetricsFormatter;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\MultiSites\API as APIMultiSites;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Site;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    protected $orderBy = 'visits';
    protected $order = 'desc';
    protected $evolutionBy = 'visits';
    protected $page = 1;
    protected $limit = 0;
    protected $period;
    protected $date;

    function __construct()
    {
        parent::__construct();

        $this->limit = Config::getInstance()->General['all_websites_website_per_page'];
    }

    function index()
    {
        return $this->getSitesInfo($isWidgetized = false);
    }

    function standalone()
    {
        return $this->getSitesInfo($isWidgetized = true);
    }

    public function getSitesInfo($isWidgetized = false)
    {
        Piwik::checkUserHasSomeViewAccess();
        $displayRevenueColumn = Common::isGoalPluginEnabled();

        $date = Common::getRequestVar('date', 'today');
        $period = Common::getRequestVar('period', 'day');
        $siteIds = APISitesManager::getInstance()->getSitesIdWithAtLeastViewAccess();
        list($minDate, $maxDate) = Site::getMinMaxDateAcrossWebsites($siteIds);

        // overwrites the default Date set in the parent controller
        // Instead of the default current website's local date,
        // we set "today" or "yesterday" based on the default Piwik timezone
        $piwikDefaultTimezone = APISitesManager::getInstance()->getDefaultTimezone();
        if ($period != 'range') {
            $date = $this->getDateParameterInTimezone($date, $piwikDefaultTimezone);
            $this->setDate($date);
            $date = $date->toString();
        }
        $dataTable = APIMultiSites::getInstance()->getAll($period, $date, $segment = false);

        // put data into a form the template will understand better
        $digestableData = array();
        foreach ($siteIds as $idSite) {
            $isEcommerceEnabled = Site::isEcommerceEnabledFor($idSite);

            $digestableData[$idSite] = array(
                'idsite'    => $idSite,
                'main_url'  => Site::getMainUrlFor($idSite),
                'name'      => Site::getNameFor($idSite),
                'visits'    => 0,
                'pageviews' => 0
            );

            if ($period != 'range') {
                $digestableData[$idSite]['visits_evolution'] = 0;
                $digestableData[$idSite]['pageviews_evolution'] = 0;
            }

            if ($displayRevenueColumn) {
                $revenueDefault = $isEcommerceEnabled ? 0 : "'-'";

                if ($period != 'range') {
                    $digestableData[$idSite]['revenue_evolution'] = $revenueDefault;
                }
            }
        }

        foreach ($dataTable->getRows() as $row) {
            $idsite = (int)$row->getMetadata('idsite');

            $site = & $digestableData[$idsite];

            $site['visits'] = (int)$row->getColumn('nb_visits');
            $site['pageviews'] = (int)$row->getColumn('nb_pageviews');

            if ($displayRevenueColumn) {
                if ($row->getColumn('revenue') !== false) {
                    $site['revenue'] = $row->getColumn('revenue');
                }
            }

            if ($period != 'range') {
                $site['visits_evolution'] = $row->getColumn('visits_evolution');
                $site['pageviews_evolution'] = $row->getColumn('pageviews_evolution');

                if ($displayRevenueColumn) {
                    $site['revenue_evolution'] = $row->getColumn('revenue_evolution');
                }
            }
        }

        $this->applyPrettyMoney($digestableData);

        $view = new View("@MultiSites/getSitesInfo");
        $view->isWidgetized = $isWidgetized;
        $view->sitesData = array_values($digestableData);
        $view->evolutionBy = $this->evolutionBy;
        $view->period = $period;
        $view->page = $this->page;
        $view->limit = $this->limit;
        $view->orderBy = $this->orderBy;
        $view->order = $this->order;
        $view->totalVisits = $dataTable->getMetadata('total_nb_visits');
        $view->totalRevenue = $dataTable->getMetadata('total_revenue');

        $view->displayRevenueColumn = $displayRevenueColumn;
        $view->totalPageviews = $dataTable->getMetadata('total_nb_pageviews');
        $view->pastTotalVisits = $dataTable->getMetadata('last_period_total_nb_visits');
        $view->totalVisitsEvolution = $dataTable->getMetadata('total_visits_evolution');
        if ($view->totalVisitsEvolution > 0) {
            $view->totalVisitsEvolution = '+' . $view->totalVisitsEvolution;
        }

        if ($period != 'range') {
            $lastPeriod = Period::factory($period, $dataTable->getMetadata('last_period_date'));
            $view->pastPeriodPretty = self::getCalendarPrettyDate($lastPeriod);
        }

        $params = $this->getGraphParamsModified();
        $view->dateSparkline = $period == 'range' ? $date : $params['date'];

        $view->autoRefreshTodayReport = false;
        // if the current date is today, or yesterday,
        // in case the website is set to UTC-12), or today in UTC+14, we refresh the page every 5min
        if (in_array($date, array('today', date('Y-m-d'),
                                  'yesterday', Date::factory('yesterday')->toString('Y-m-d'),
                                  Date::factory('now', 'UTC+14')->toString('Y-m-d')))
        ) {

            $view->autoRefreshTodayReport = Config::getInstance()->General['multisites_refresh_after_seconds'];
        }
        $this->setGeneralVariablesView($view);
        $this->setMinDateView($minDate, $view);
        $this->setMaxDateView($maxDate, $view);
        $view->show_sparklines = Config::getInstance()->General['show_multisites_sparklines'];

        return $view->render();
    }

    protected function applyPrettyMoney(&$sites)
    {
        foreach ($sites as $idsite => &$site) {
            $revenue = "-";
            if (!empty($site['revenue'])) {
                $revenue = MetricsFormatter::getPrettyMoney($site['revenue'], $site['idsite'], $htmlAllowed = false);
            }
            $site['revenue'] = '"' . $revenue . '"';
        }
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
