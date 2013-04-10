<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitFrequency
 */

/**
 * VisitFrequency API lets you access a list of metrics related to Returning Visitors.
 * @package Piwik_VisitFrequency
 */
class Piwik_VisitFrequency_API
{
    static private $instance = null;

    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function get($idSite, $period, $date, $segment = false, $columns = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Piwik_Archive::build($idSite, $period, $date, $segment);

        // array values are comma separated
        $columns = Piwik::getArrayFromApiParameter($columns);
        $tempColumns = array();

        $bounceRateReturningRequested = $averageVisitDurationReturningRequested = $actionsPerVisitReturningRequested = false;
        if (!empty($columns)) {
            // make sure base metrics are there for processed metrics
            if (false !== ($bounceRateReturningRequested = array_search('bounce_rate_returning', $columns))) {
                if (!in_array('nb_visits_returning', $columns)) $tempColumns[] = 'nb_visits_returning';
                if (!in_array('bounce_count_returning', $columns)) $tempColumns[] = 'bounce_count_returning';
                unset($columns[$bounceRateReturningRequested]);
            }
            if (false !== ($actionsPerVisitReturningRequested = array_search('nb_actions_per_visit_returning', $columns))) {
                if (!in_array('nb_actions_returning', $columns)) $tempColumns[] = 'nb_actions_returning';
                if (!in_array('nb_visits_returning', $columns)) $tempColumns[] = 'nb_visits_returning';
                unset($columns[$actionsPerVisitReturningRequested]);
            }
            if (false !== ($averageVisitDurationReturningRequested = array_search('avg_time_on_site_returning', $columns))) {
                if (!in_array('sum_visit_length_returning', $columns)) $tempColumns[] = 'sum_visit_length_returning';
                if (!in_array('nb_visits_returning', $columns)) $tempColumns[] = 'nb_visits_returning';
                unset($columns[$averageVisitDurationReturningRequested]);
            }

            $tempColumns = array_unique($tempColumns);
            $columns = array_merge($columns, $tempColumns);
        } else {
            $bounceRateReturningRequested = $averageVisitDurationReturningRequested = $actionsPerVisitReturningRequested = true;
            $columns = array(
                'nb_visits_returning',
                'nb_actions_returning',
                'max_actions_returning',
                'sum_visit_length_returning',
                'bounce_count_returning',
                'nb_visits_converted_returning',
            );

            if ($period == 'day') {
                $columns = array_merge(array('nb_uniq_visitors_returning'), $columns);
            }
        }
        $dataTable = $archive->getDataTableFromNumeric($columns);

        // Process ratio metrics
        if ($bounceRateReturningRequested !== false) {
            $dataTable->filter('ColumnCallbackAddColumnPercentage', array('bounce_rate_returning', 'bounce_count_returning', 'nb_visits_returning', 0));
        }
        if ($actionsPerVisitReturningRequested !== false) {
            $dataTable->filter('ColumnCallbackAddColumnQuotient', array('nb_actions_per_visit_returning', 'nb_actions_returning', 'nb_visits_returning', 1));
        }
        if ($averageVisitDurationReturningRequested !== false) {
            $dataTable->filter('ColumnCallbackAddColumnQuotient', array('avg_time_on_site_returning', 'sum_visit_length_returning', 'nb_visits_returning', 0));
        }

        // remove temporary metrics that were used to compute processed metrics
        $dataTable->deleteColumns($tempColumns);

        return $dataTable;
    }

    protected function getNumeric($idSite, $period, $date, $toFetch)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Piwik_Archive::build($idSite, $period, $date);
        $dataTable = $archive->getNumeric($toFetch);
        return $dataTable;
    }

    /**
     * @ignore
     */
    public function getVisitsReturning($idSite, $period, $date)
    {
        return $this->getNumeric($idSite, $period, $date, 'nb_visits_returning');
    }

    /**
     * @ignore
     */
    public function getActionsReturning($idSite, $period, $date)
    {
        return $this->getNumeric($idSite, $period, $date, 'nb_actions_returning');
    }

    /**
     * @ignore
     */
    public function getSumVisitsLengthReturning($idSite, $period, $date)
    {
        return $this->getNumeric($idSite, $period, $date, 'sum_visit_length_returning');
    }

    /**
     * @ignore
     */
    public function getBounceCountReturning($idSite, $period, $date)
    {
        return $this->getNumeric($idSite, $period, $date, 'bounce_count_returning');
    }

    /**
     * @ignore
     */
    public function getConvertedVisitsReturning($idSite, $period, $date)
    {
        return $this->getNumeric($idSite, $period, $date, 'nb_visits_converted_returning');
    }
}
