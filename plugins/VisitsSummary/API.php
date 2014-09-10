<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary;

use Piwik\Archive;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\SettingsPiwik;

/**
 * VisitsSummary API lets you access the core web analytics metrics (visits, unique visitors,
 * count of actions (page views & downloads & clicks on outlinks), time on site, bounces and converted visits.
 *
 * @method static \Piwik\Plugins\VisitsSummary\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function get($idSite, $period, $date, $segment = false, $columns = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);

        // array values are comma separated
        $columns = Piwik::getArrayFromApiParameter($columns);
        $tempColumns = array();

        $bounceRateRequested = $actionsPerVisitRequested = $averageVisitDurationRequested = false;
        if (!empty($columns)) {
            // make sure base metrics are there for processed metrics
            if (false !== ($bounceRateRequested = array_search('bounce_rate', $columns))) {
                if (!in_array('nb_visits', $columns)) $tempColumns[] = 'nb_visits';
                if (!in_array('bounce_count', $columns)) $tempColumns[] = 'bounce_count';
                unset($columns[$bounceRateRequested]);
            }
            if (false !== ($actionsPerVisitRequested = array_search('nb_actions_per_visit', $columns))) {
                if (!in_array('nb_visits', $columns)) $tempColumns[] = 'nb_visits';
                if (!in_array('nb_actions', $columns)) $tempColumns[] = 'nb_actions';
                unset($columns[$actionsPerVisitRequested]);
            }
            if (false !== ($averageVisitDurationRequested = array_search('avg_time_on_site', $columns))) {
                if (!in_array('nb_visits', $columns)) $tempColumns[] = 'nb_visits';
                if (!in_array('sum_visit_length', $columns)) $tempColumns[] = 'sum_visit_length';
                unset($columns[$averageVisitDurationRequested]);
            }
            $tempColumns = array_unique($tempColumns);
            rsort($tempColumns);
            $columns = array_merge($columns, $tempColumns);
        } else {
            $bounceRateRequested = $actionsPerVisitRequested = $averageVisitDurationRequested = true;
            $columns = $this->getCoreColumns($period);
        }

        $dataTable = $archive->getDataTableFromNumeric($columns);

        // Process ratio metrics from base metrics, when requested
        if ($bounceRateRequested !== false) {
            $dataTable->filter('ColumnCallbackAddColumnPercentage', array('bounce_rate', 'bounce_count', 'nb_visits', 0));
        }
        if ($actionsPerVisitRequested !== false) {
            $dataTable->filter('ColumnCallbackAddColumnQuotient', array('nb_actions_per_visit', 'nb_actions', 'nb_visits', 1));
        }
        if ($averageVisitDurationRequested !== false) {
            $dataTable->filter('ColumnCallbackAddColumnQuotient', array('avg_time_on_site', 'sum_visit_length', 'nb_visits', 0));
        }

        // remove temp metrics that were used to compute processed metrics
        $dataTable->deleteColumns($tempColumns);
        return $dataTable;
    }

    /**
     * @ignore
     */
    public function getColumns($period)
    {
        $columns = $this->getCoreColumns($period);
        $columns = array_merge($columns, array('bounce_rate', 'nb_actions_per_visit', 'avg_time_on_site'));
        return $columns;
    }

    protected function getCoreColumns($period)
    {
        $columns = array(
            'nb_visits',
            'nb_actions',
            'nb_visits_converted',
            'bounce_count',
            'sum_visit_length',
            'max_actions'
        );
        if (SettingsPiwik::isUniqueVisitorsEnabled($period)) {
            $columns = array_merge(array('nb_uniq_visitors', 'nb_users'), $columns);
        }
        $columns = array_values($columns);
        return $columns;
    }

    protected function getNumeric($idSite, $period, $date, $segment, $toFetch)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTableFromNumeric($toFetch);
        return $dataTable;
    }

    public function getVisits($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'nb_visits');
    }

    public function getUniqueVisitors($idSite, $period, $date, $segment = false)
    {
        $metric = 'nb_uniq_visitors';
        $this->checkUniqueIsEnabledOrFail($period, $metric);
        return $this->getNumeric($idSite, $period, $date, $segment, $metric);
    }

    public function getUsers($idSite, $period, $date, $segment = false)
    {
        $metric = 'nb_users';
        $this->checkUniqueIsEnabledOrFail($period, $metric);
        return $this->getNumeric($idSite, $period, $date, $segment, $metric);
    }

    public function getActions($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'nb_actions');
    }

    public function getMaxActions($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'max_actions');
    }

    public function getBounceCount($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'bounce_count');
    }

    public function getVisitsConverted($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'nb_visits_converted');
    }

    public function getSumVisitsLength($idSite, $period, $date, $segment = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, 'sum_visit_length');
    }

    public function getSumVisitsLengthPretty($idSite, $period, $date, $segment = false)
    {
        $table = $this->getSumVisitsLength($idSite, $period, $date, $segment);
        if (is_object($table)) {
            $table->filter('ColumnCallbackReplace',
                array('sum_visit_length', '\Piwik\MetricsFormatter::getPrettyTimeFromSeconds'));
        } else {
            $table = MetricsFormatter::getPrettyTimeFromSeconds($table);
        }
        return $table;
    }

    /**
     * @param $period
     * @param $metric
     * @throws \Exception
     */
    private function checkUniqueIsEnabledOrFail($period, $metric)
    {
        if (!SettingsPiwik::isUniqueVisitorsEnabled($period)) {
            throw new \Exception(
                "The metric " . $metric . " is not enabled for the requested period. " .
                "Please see this FAQ: http://piwik.org/faq/how-to/faq_113/"
            );
        }
    }
}
