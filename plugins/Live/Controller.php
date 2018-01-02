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
use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Plugins\Live\ProfileSummary\ProfileSummaryAbstract;
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
        $view->visitorLog = $this->renderReport('getLastVisitsDetails');
        return $view->render();
    }

    /**
     * Widget
     */
    public function getVisitorLog()
    {
        return $this->renderReport('getLastVisitsDetails');
    }
    
    public function getLastVisitsStart()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

        // hack, ensure we load today's visits by default
        $_GET['date'] = 'today';
        \Piwik\Period\Factory::checkPeriodIsEnabled('day');
        $_GET['period'] = 'day';

        $view = new View('@Live/getLastVisitsStart');
        $view->idSite = (int) $this->idSite;
        $api = new Request("method=Live.getLastVisitsDetails&idSite={$this->idSite}&filter_limit=10&format=php&serialize=0&disable_generic_filters=1");
        $visitors = $api->process();
        if (!empty($visitors['result']) && $visitors['result'] === 'error' && !empty($visitors['message'])) {
            throw new \Exception($visitors['message']);
        }
        $view->visitors = $visitors;

        return $this->render($view);
    }

    private function setCounters($view)
    {
        $segment = Request::getRawSegmentFromRequest();
        $last30min = API::getInstance()->getCounters($this->idSite, $lastMinutes = 30, $segment, array('visits', 'actions'));
        $last30min = $last30min[0];
        $today = API::getInstance()->getCounters($this->idSite, $lastMinutes = 24 * 60, $segment, array('visits', 'actions'));
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
        $view->visitorData = Request::processRequest('Live.getVisitorProfile');
        $view->exportLink = $this->getVisitorProfileExportLink();

        $this->setWidgetizedVisitorProfileUrl($view);

        $summaryEntries = array();

        $profileSummaries = self::getAllProfileSummaryInstances();
        foreach ($profileSummaries as $profileSummary) {
            $profileSummary->setProfile($view->visitorData);
            $summaryEntries[] = [$profileSummary->getOrder(), $profileSummary->render()];
        }

        usort($summaryEntries, function($a, $b) {
            return version_compare($a[0], $b[0]);
        });

        $summary = '';

        foreach ($summaryEntries AS $summaryEntry) {
            $summary .= $summaryEntry[1];
        }

        $view->profileSummary = $summary;

        return $view->render();
    }

    public function getVisitList()
    {
        $filterLimit = Common::getRequestVar('filter_offset', 0, 'int');
        $startCounter = Common::getRequestVar('start_number', 0, 'int');
        $limit = Config::getInstance()->General['live_visitor_profile_max_visits_to_aggregate'];

        if ($startCounter >= $limit) {
            return; // do not return more visits than configured for profile
        }

        $nextVisits = Request::processRequest('Live.getLastVisitsDetails', array(
                                                                                'segment'                 => self::getSegmentWithVisitorId(),
                                                                                'filter_limit'            => VisitorProfile::VISITOR_PROFILE_MAX_VISITS_TO_SHOW,
                                                                                'filter_offset'           => $filterLimit,
                                                                                'period'                  => false,
                                                                                'date'                    => false
                                                                           ));

        $idSite = Common::getRequestVar('idSite', null, 'int');

        if (empty($nextVisits)) {
            return;
        }

        $view = new View('@Live/getVisitList.twig');
        $view->idSite = $idSite;
        $view->startCounter = $startCounter < count($nextVisits) ? count($nextVisits) : $startCounter;
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

    public static function getSegmentWithVisitorId()
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


    /**
     * Returns all available profile summaries
     *
     * @return ProfileSummaryAbstract[]
     * @throws \Exception
     */
    public static function getAllProfileSummaryInstances()
    {
        $cacheId = CacheId::pluginAware('ProfileSummaries');
        $cache   = Cache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $instances = [];

            /**
             * Triggered to add new live profile summaries.
             *
             * **Example**
             *
             *     public function addProfileSummary(&$profileSummaries)
             *     {
             *         $profileSummaries[] = new MyCustomProfileSummary();
             *     }
             *
             * @param ProfileSummaryAbstract[] $profileSummaries An array of profile summaries
             */
            Piwik::postEvent('Live.addProfileSummaries', array(&$instances));

            foreach (self::getAllProfileSummaryClasses() as $className) {
                $instances[] = new $className();
            }

            /**
             * Triggered to filter / restrict profile summaries.
             *
             * **Example**
             *
             *     public function filterProfileSummary(&$profileSummaries)
             *     {
             *         foreach ($profileSummaries as $index => $profileSummary) {
             *              if ($profileSummary->getId() === 'myid') {}
             *                  unset($profileSummaries[$index]); // remove all summaries having this ID
             *              }
             *         }
             *     }
             *
             * @param ProfileSummaryAbstract[] $profileSummaries An array of profile summaries
             */
            Piwik::postEvent('Live.filterProfileSummaries', array(&$instances));

            $cache->save($cacheId, $instances);
        }

        return $cache->fetch($cacheId);
    }

    /**
     * Returns class names of all VisitorDetails classes.
     *
     * @return string[]
     * @api
     */
    protected static function getAllProfileSummaryClasses()
    {
        return Plugin\Manager::getInstance()->findMultipleComponents('ProfileSummary', 'Piwik\Plugins\Live\ProfileSummary\ProfileSummaryAbstract');
    }
}
