<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ImageGraph;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Site;
use Piwik\TaskScheduler;
use Piwik\Url;

class ImageGraph extends \Piwik\Plugin
{
    public function getInformation()
    {
        $suffix = ' Debug: <a href="' . Url::getCurrentQueryStringWithParametersModified(
                array('module' => 'ImageGraph', 'action' => 'index')) . '">All images</a>';
        $info = parent::getInformation();
        $info['description'] .= ' ' . $suffix;
        return $info;
    }

    private static $CONSTANT_ROW_COUNT_REPORT_EXCEPTIONS = array(
        'Referrers_getReferrerType',
    );

    // row evolution support not yet implemented for these APIs
    private static $REPORTS_DISABLED_EVOLUTION_GRAPH = array(
        'Referrers_getAll',
    );

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'API.getReportMetadata.end' => array('function' => 'getReportMetadata',
                                                 'after'    => true),
        );
        return $hooks;
    }

    // Number of periods to plot on an evolution graph
    const GRAPH_EVOLUTION_LAST_PERIODS = 30;

    /**
     * @param array $reports
     * @param array $info
     * @return mixed
     */
    public function getReportMetadata(&$reports, $info)
    {
        $idSites = $info['idSites'];

        // If only one website is selected, we add the Graph URL
        if (count($idSites) != 1) {
            return;
        }
        $idSite = reset($idSites);

        // in case API.getReportMetadata was not called with date/period we use sane defaults
        if (empty($info['period'])) {
            $info['period'] = 'day';
        }
        if (empty($info['date'])) {
            $info['date'] = 'today';
        }

        // need two sets of period & date, one for single period graphs, one for multiple periods graphs
        if (Period::isMultiplePeriod($info['date'], $info['period'])) {
            $periodForMultiplePeriodGraph = $info['period'];
            $dateForMultiplePeriodGraph = $info['date'];

            $periodForSinglePeriodGraph = 'range';
            $dateForSinglePeriodGraph = $info['date'];
        } else {
            $periodForSinglePeriodGraph = $info['period'];
            $dateForSinglePeriodGraph = $info['date'];

            $piwikSite = new Site($idSite);
            if ($periodForSinglePeriodGraph == 'range') {
                $periodForMultiplePeriodGraph = Config::getInstance()->General['graphs_default_period_to_plot_when_period_range'];
                $dateForMultiplePeriodGraph = $dateForSinglePeriodGraph;
            } else {
                $periodForMultiplePeriodGraph = $periodForSinglePeriodGraph;
                $dateForMultiplePeriodGraph = Range::getRelativeToEndDate(
                    $periodForSinglePeriodGraph,
                    'last' . self::GRAPH_EVOLUTION_LAST_PERIODS,
                    $dateForSinglePeriodGraph,
                    $piwikSite
                );
            }
        }

        $token_auth = Common::getRequestVar('token_auth', false);

        $urlPrefix = "index.php?";
        foreach ($reports as &$report) {
            $reportModule = $report['module'];
            $reportAction = $report['action'];
            $reportUniqueId = $reportModule . '_' . $reportAction;

            $parameters = array();
            $parameters['module'] = 'API';
            $parameters['method'] = 'ImageGraph.get';
            $parameters['idSite'] = $idSite;
            $parameters['apiModule'] = $reportModule;
            $parameters['apiAction'] = $reportAction;
            if (!empty($token_auth)) {
                $parameters['token_auth'] = $token_auth;
            }

            // Forward custom Report parameters to the graph URL
            if (!empty($report['parameters'])) {
                $parameters = array_merge($parameters, $report['parameters']);
            }
            if (empty($report['dimension'])) {
                $parameters['period'] = $periodForMultiplePeriodGraph;
                $parameters['date'] = $dateForMultiplePeriodGraph;
            } else {
                $parameters['period'] = $periodForSinglePeriodGraph;
                $parameters['date'] = $dateForSinglePeriodGraph;
            }

            // add the idSubtable if it exists
            $idSubtable = Common::getRequestVar('idSubtable', false);
            if ($idSubtable !== false) {
                $parameters['idSubtable'] = $idSubtable;
            }

            if (!empty($_GET['_restrictSitesToLogin']) && TaskScheduler::isTaskBeingExecuted()) {
                $parameters['_restrictSitesToLogin'] = $_GET['_restrictSitesToLogin'];
            }

            $segment = Request::getRawSegmentFromRequest();
            if (!empty($segment)) {
                $parameters['segment'] = $segment;
            }

            $report['imageGraphUrl'] = $urlPrefix . Url::getQueryStringFromParameters($parameters);

            // thanks to API.getRowEvolution, reports with dimensions can now be plotted using an evolution graph
            // however, most reports with a fixed set of dimension values are excluded
            // this is done so Piwik Mobile and Scheduled Reports do not display them
            $reportWithDimensionsSupportsEvolution = empty($report['constantRowsCount']) || in_array($reportUniqueId, self::$CONSTANT_ROW_COUNT_REPORT_EXCEPTIONS);

            $reportSupportsEvolution = !in_array($reportUniqueId, self::$REPORTS_DISABLED_EVOLUTION_GRAPH);

            if ($reportSupportsEvolution
                && $reportWithDimensionsSupportsEvolution
            ) {
                $parameters['period'] = $periodForMultiplePeriodGraph;
                $parameters['date'] = $dateForMultiplePeriodGraph;
                $report['imageGraphEvolutionUrl'] = $urlPrefix . Url::getQueryStringFromParameters($parameters);
            }
        }
    }
}
