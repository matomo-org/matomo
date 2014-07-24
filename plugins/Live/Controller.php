<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\Live\Reports\GetLastVisitsDetails;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Url;
use Piwik\View;

/**
 */
class Controller extends \Piwik\Plugin\Controller
{
    const SIMPLE_VISIT_COUNT_WIDGET_LAST_MINUTES_CONFIG_KEY = 'live_widget_visitor_count_last_minutes';

    function index()
    {
        return $this->widget();
    }

    public function widget()
    {
        $view = new View('@Live/index');
        $view->idSite = $this->idSite;
        $view = $this->setCounters($view);
        $view->liveRefreshAfterMs = (int)Config::getInstance()->General['live_widget_refresh_after_seconds'] * 1000;
        $view->visitors = $this->getLastVisitsStart($fetchPlease = true);
        $view->liveTokenAuth = Piwik::getCurrentUserTokenAuth();
        return $this->render($view);
    }

    public function ajaxTotalVisitors()
    {
        $view = new View('@Live/ajaxTotalVisitors');
        $view = $this->setCounters($view);
        $view->idSite = $this->idSite;
        return $this->render($view);
    }

    private function render(View $view)
    {
        $rendered = $view->render();

        return $rendered;
    }

    public function indexVisitorLog()
    {
        $view = new View('@Live/indexVisitorLog.twig');
        $view->filterEcommerce = Common::getRequestVar('filterEcommerce', 0, 'int');
        $view->visitorLog = $this->renderReport(new GetLastVisitsDetails());
        return $view->render();
    }

    /**
     * Widget
     */
    public function getVisitorLog()
    {
        return $this->renderReport(new GetLastVisitsDetails());
    }

    public function getLastVisitsStart()
    {
        // hack, ensure we load today's visits by default
        $_GET['date'] = 'today';
        \Piwik\Period\Factory::checkPeriodIsEnabled('day');
        $_GET['period'] = 'day';

        $view = new View('@Live/getLastVisitsStart');
        $view->idSite = $this->idSite;
        $api = new Request("method=Live.getLastVisitsDetails&idSite={$this->idSite}&filter_limit=10&format=php&serialize=0&disable_generic_filters=1");
        $visitors = $api->process();
        $view->visitors = $visitors;

        return $this->render($view);
    }

    private function setCounters($view)
    {
        $segment = Request::getRawSegmentFromRequest();
        $last30min = API::getInstance()->getCounters($this->idSite, $lastMinutes = 30, $segment);
        $last30min = $last30min[0];
        $today = API::getInstance()->getCounters($this->idSite, $lastMinutes = 24 * 60, $segment);
        $today = $today[0];
        $view->visitorsCountHalfHour = $last30min['visits'];
        $view->visitorsCountToday = $today['visits'];
        $view->pisHalfhour = $last30min['actions'];
        $view->pisToday = $today['actions'];
        return $view;
    }

    /**
     * Echo's HTML for visitor profile popup.
     */
    public function getVisitorProfilePopup()
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        $view = new View('@Live/getVisitorProfilePopup.twig');
        $view->idSite = $idSite;
        $view->goals = APIGoals::getInstance()->getGoals($idSite);
        $view->visitorData = Request::processRequest('Live.getVisitorProfile', array('checkForLatLong' => true));
        $view->exportLink = $this->getVisitorProfileExportLink();

        if (Common::getRequestVar('showMap', 1) == 1
            && !empty($view->visitorData['hasLatLong'])
            && \Piwik\Plugin\Manager::getInstance()->isPluginLoaded('UserCountryMap')
        ) {
            $view->userCountryMapUrl = $this->getUserCountryMapUrlForVisitorProfile();
        }

        $this->setWidgetizedVisitorProfileUrl($view);

        return $view->render();
    }

    public function getSingleVisitSummary()
    {
        $view = new View('@Live/getSingleVisitSummary.twig');
        $visits = Request::processRequest('Live.getLastVisitsDetails', array(
                                                                            'segment' => 'visitId==' . Common::getRequestVar('visitId'),
                                                                            'period'  => false,
                                                                            'date'    => false
                                                                       ));
        $view->visitData = $visits->getFirstRow()->getColumns();
        $view->visitReferralSummary = API::getReferrerSummaryForVisit($visits->getFirstRow());
        $view->showLocation = true;
        $this->setWidgetizedVisitorProfileUrl($view);
        $view->exportLink = $this->getVisitorProfileExportLink();
        return $view->render();
    }

    public function getVisitList()
    {
        $startCounter = Common::getRequestVar('filter_offset', 0, 'int');
        $nextVisits = Request::processRequest('Live.getLastVisitsDetails', array(
                                                                                'segment'                 => self::getSegmentWithVisitorId(),
                                                                                'filter_limit'            => API::VISITOR_PROFILE_MAX_VISITS_TO_SHOW,
                                                                                'filter_offset'           => $startCounter,
                                                                                'period'                  => false,
                                                                                'date'                    => false
                                                                           ));

        if (empty($nextVisits)) {
            return;
        }

        $view = new View('@Live/getVisitList.twig');
        $view->idSite = Common::getRequestVar('idSite', null, 'int');
        $view->startCounter = $startCounter + 1;
        $view->visits = $nextVisits;
        return $view->render();
    }

    private function getVisitorProfileExportLink()
    {
        return Url::getCurrentQueryStringWithParametersModified(array(
                                                                     'module'   => 'API',
                                                                     'action'   => 'index',
                                                                     'method'   => 'Live.getVisitorProfile',
                                                                     'format'   => 'XML',
                                                                     'expanded' => 1
                                                                ));
    }

    private function setWidgetizedVisitorProfileUrl($view)
    {
        if (\Piwik\Plugin\Manager::getInstance()->isPluginLoaded('Widgetize')) {
            $view->widgetizedLink = Url::getCurrentQueryStringWithParametersModified(array(
                                                                                          'module'            => 'Widgetize',
                                                                                          'action'            => 'iframe',
                                                                                          'moduleToWidgetize' => 'Live',
                                                                                          'actionToWidgetize' => 'getVisitorProfilePopup'
                                                                                     ));
        }
    }

    private function getUserCountryMapUrlForVisitorProfile()
    {
        $params = array(
            'module'             => 'UserCountryMap',
            'action'             => 'realtimeMap',
            'segment'            => self::getSegmentWithVisitorId(),
            'visitorId'          => false,
            'changeVisitAlpha'   => 0,
            'removeOldVisits'    => 0,
            'realtimeWindow'     => 'false',
            'showFooterMessage'  => 0,
            'showDateTime'       => 0,
            'doNotRefreshVisits' => 1
        );
        return Url::getCurrentQueryStringWithParametersModified($params);
    }

    private static function getSegmentWithVisitorId()
    {
        static $cached = null;
        if ($cached === null) {
            $segment = Request::getRawSegmentFromRequest();
            if (!empty($segment)) {
                $segment = urldecode($segment) . ';';
            }

            $idVisitor = Common::getRequestVar('visitorId', false);
            if ($idVisitor === false) {
                $idVisitor = Request::processRequest('Live.getMostRecentVisitorId');
            }

            $cached = urlencode($segment . 'visitorId==' . $idVisitor);
        }
        return $cached;
    }
}
