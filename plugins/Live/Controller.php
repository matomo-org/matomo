<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live;

use Piwik\API\Request;
use Piwik\Config\GeneralConfig;
use Piwik\Piwik;
use Piwik\DataTable;
use Piwik\Plugins\Live\Exception\MaxExecutionTimeExceededException;
use Piwik\Plugins\Live\Visualizations\VisitorLog;
use Piwik\Url;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    public const SIMPLE_VISIT_COUNT_WIDGET_LAST_MINUTES_CONFIG_KEY = 'live_widget_visitor_count_last_minutes';

    private $profileSummaryProvider;

    public function __construct(ProfileSummaryProvider $profileSummaryProvider)
    {
        $this->profileSummaryProvider = $profileSummaryProvider;
        parent::__construct();
    }

    public function index()
    {
        return $this->widget();
    }

    public function widget()
    {
        Piwik::checkUserHasViewAccess($this->idSite);
        Live::checkIsVisitorLogEnabled($this->idSite);

        $view = new View('@Live/index');
        $view->idSite = $this->idSite;
        $view->isWidgetized = \Piwik\Request::fromRequest()->getIntegerParameter('widget', 0);
        $view->liveRefreshAfterMs = GeneralConfig::getIntegerConfigValue('live_widget_refresh_after_seconds', 0, $this->idSite) * 1000;
        return $this->render($view);
    }

    public function ajaxTotalVisitors()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

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
        Piwik::checkUserHasViewAccess($this->idSite);
        Live::checkIsVisitorLogEnabled($this->idSite);

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
        Live::checkIsVisitorLogEnabled($this->idSite);

        // hack, ensure we load today's visits by default
        $_GET['date'] = 'today';
        \Piwik\Period\Factory::checkPeriodIsEnabled('day');
        $_GET['period'] = 'day';

        $view = new View('@Live/getLastVisitsStart');
        $view->isProfileEnabled = Live::isVisitorProfileEnabled();
        $view->idSite = (int) $this->idSite;
        $error = '';
        $visitors = new DataTable();
        try {
            $api = new Request([
                'method' => 'Live.getLastVisitsDetails',
                'idSite' => $this->idSite,
                'filter_limit' => 10,
                'format' => 'original',
                'serialize' => 0,
                'disable_generic_filters' => 1,
            ]);
            $visitors = $api->process();
            $this->decodeActionUrls($visitors);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $view->error = $error;
        $view->visitors = $visitors;

        return $this->render($view);
    }

    /**
     * The real-time widget template renders action URLs inline instead of going through the
     * `Live.renderAction` event. As tracked URLs are returned HTML-entity encoded (e.g. `&` as
     * `&amp;`), they would be escaped a second time when output into the href attribute, resulting
     * in broken links like `download.pdf?a=b&amp;c=d`. Decode the entities here so the template's
     * output escaping produces a single, correct encoding - matching VisitorDetails::renderAction().
     */
    private function decodeActionUrls(DataTable $visitors): void
    {
        foreach ($visitors->getRows() as $visitor) {
            $actionDetails = $visitor->getColumn('actionDetails');

            if (!is_array($actionDetails)) {
                continue;
            }

            foreach ($actionDetails as &$action) {
                if (
                    isset($action['type'], $action['url'])
                    && in_array($action['type'], ['outlink', 'download'], true)
                ) {
                    $action['url'] = html_entity_decode($action['url'], ENT_QUOTES, 'UTF-8');
                }
            }
            unset($action);

            $visitor->setColumn('actionDetails', $actionDetails);
        }
    }

    private function setCounters($view)
    {
        $segment = Request::getRawSegmentFromRequest();
        $executeTodayQuery = true;
        $view->countErrorToday = '';
        $view->countErrorHalfHour = '';
        try {
            $last30min = Request::processRequest('Live.getCounters', [
                'idSite' => $this->idSite,
                'lastMinutes' => 30,
                'segment' => $segment,
                'showColumns' => 'visits,actions',
            ], $default = []);
            $last30min = $last30min[0];
        } catch (MaxExecutionTimeExceededException $e) {
            $last30min = ['visits' => '-', 'actions' => '-'];
            $today = ['visits' => '-', 'actions' => '-'];
            $view->countErrorToday = $e->getMessage();
            $view->countErrorHalfHour = $e->getMessage();
            $executeTodayQuery = false; // if query for last 30 min failed, we also expect the 24 hour query to fail
        }

        try {
            if ($executeTodayQuery) {
                $today = Request::processRequest('Live.getCounters', [
                    'idSite' => $this->idSite,
                    'lastMinutes' => 24 * 60,
                    'segment' => $segment,
                    'showColumns' => 'visits,actions',
                ], $default = []);
                $today = $today[0];
            }
        } catch (MaxExecutionTimeExceededException $e) {
            $today = ['visits' => '-', 'actions' => '-'];
            $view->countErrorToday = $e->getMessage();
        }

        $view->visitorsCountHalfHour = $last30min['visits'];
        $view->visitorsCountToday = $today['visits'];
        $view->pisHalfhour = (int)$last30min['actions'];
        $view->pisToday = (int)$today['actions'];
        return $view;
    }

    /**
     * Echo's HTML for visitor profile popup.
     */
    public function getVisitorProfilePopup()
    {
        Piwik::checkUserHasViewAccess($this->idSite);
        Live::checkIsVisitorProfileEnabled($this->idSite);

        $visitorData = Request::processRequest('Live.getVisitorProfile');

        if (empty($visitorData)) {
            throw new \Exception('Visitor could not be found'); // for example when URL parameter is not set
        }

        VisitorLog::groupActionsByPageviewId($visitorData['lastVisits']);

        $view = new View('@Live/getVisitorProfilePopup.twig');
        $view->idSite = $this->idSite;
        $view->goals = Request::processRequest('Goals.getGoals', ['idSite' => $this->idSite, 'filter_limit' => '-1'], $default = []);
        $view->visitorData = $visitorData;
        $view->exportLink = $this->getVisitorProfileExportLink();

        $this->setWidgetizedVisitorProfileUrl($view);

        $summaryEntries = array();

        $profileSummaries = $this->profileSummaryProvider->getAllInstances();
        foreach ($profileSummaries as $profileSummary) {
            $profileSummary->setProfile($view->visitorData);
            $summaryEntries[] = [$profileSummary->getOrder(), $profileSummary->render()];
        }

        usort($summaryEntries, function ($a, $b) {
            return version_compare($a[0], $b[0]);
        });

        $summary = '';

        foreach ($summaryEntries as $summaryEntry) {
            $summary .= $summaryEntry[1];
        }

        $view->profileSummary = $summary;

        return $view->render();
    }

    public function getVisitList()
    {
        $this->checkSitePermission();
        Piwik::checkUserHasViewAccess($this->idSite);

        $filterLimit  = \Piwik\Request::fromRequest()->getIntegerParameter('filter_offset', 0);
        $startCounter = \Piwik\Request::fromRequest()->getIntegerParameter('start_number', 0);
        $limit        = GeneralConfig::getIntegerConfigValue('live_visitor_profile_max_visits_to_aggregate', 0, $this->idSite);

        if ($startCounter >= $limit) {
            return ''; // do not return more visits than configured for profile
        }

        $nextVisits = Request::processRequest('Live.getLastVisitsDetails', array(
                                                                                'segment'                 => Live::getSegmentWithVisitorId(),
                                                                                'filter_limit'            => VisitorProfile::VISITOR_PROFILE_MAX_VISITS_TO_SHOW,
                                                                                'filter_offset'           => $filterLimit,
                                                                                'period'                  => false,
                                                                                'date'                    => false,
                                                                           ));

        if (empty($nextVisits)) {
            return '';
        }

        VisitorLog::groupActionsByPageviewId($nextVisits);

        $view = new View('@Live/getVisitList.twig');
        $view->idSite = $this->idSite;
        $view->startCounter = $startCounter < $nextVisits->getRowsCount() ? $nextVisits->getRowsCount() : $startCounter;
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
                                                                     'expanded' => 1,
                                                                ));
    }

    private function setWidgetizedVisitorProfileUrl($view)
    {
        if (\Piwik\Plugin\Manager::getInstance()->isPluginLoaded('Widgetize')) {
            $view->widgetizedLink = Url::getCurrentQueryStringWithParametersModified(array(
                                                                                          'module'            => 'Widgetize',
                                                                                          'action'            => 'iframe',
                                                                                          'moduleToWidgetize' => 'Live',
                                                                                          'actionToWidgetize' => 'getVisitorProfilePopup',
                                                                                     ));
        }
    }
}
