<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitsSummary;

use Matomo\Cache\Transient;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Segment;

class MajorityProfilable
{
    /**
     * TODO
     *
     * @param int|null $getId
     * @param string|null $getLabel
     * @param string $dateString
     * @param $segment
     * @return boolean
     */
    public function isPeriodMajorityProfilable($idSite = null, $period = null, $date = null, $segment = null)
    {
        if (self::isProfilableCheckDisabledInTests()) {
            return true;
        }

        $idSite = $idSite ?: Common::getRequestVar('idSite', false);
        $period = $period ?: Common::getRequestVar('period', false);
        $date = $date ?: Common::getRequestVar('date', false);
        $segment = $segment === null ? Request::getRawSegmentFromRequest() : '';

        if ($idSite === false
            || $period === false
            || $date === false
            || !is_numeric($idSite)
            || Period::isMultiplePeriod($date, $period)
        ) {
            return true;
        }

        // if the current period is a day period, we want to check whether the entire week has profilable
        // data rather than just the day. this way, if some days have profilable data, but not all, users
        // will not consistently see the message appear and disappear as they change periods.
        if ($period == 'day') {
            $period = 'week';
        }

        // to make sure we don't have cache misses in case the date is not the same, even though
        // the period date range stays the same.
        if ($period != 'range') {
            $date = Period\Factory::build($period, $date)->getDateStart()->toString();
        }

        $transientCache = StaticContainer::get(Transient::class);

        $segmentObj = new Segment($segment, [$idSite]);

        $cacheKey = "VisitsSummary.isProfilable.$idSite.$period.$date." . $segmentObj->getHash();
        if (!$transientCache->contains($cacheKey)) {
            /** @var DataTable $summary */
            $summary = Request::processRequest('VisitsSummary.get', [
                'idSite' => $idSite,
                'period' => $period,
                'date' => $date,
                'segment' => $segment,
                'disable_profilable_check' => 1, // prevent infinite recursion
            ]);

            $isProfilable = $this->calculateIsProfilable($summary->getFirstRow());

            $transientCache->save($cacheKey, $isProfilable);
        } else {
            $isProfilable = (bool)$transientCache->fetch($cacheKey);
        }

        return $isProfilable;
    }

    private function isProfilableCheckDisabledInTests()
    {
        if (!defined('PIWIK_TEST_MODE')) {
            return false;
        }

        try {
            $isDisabled = (bool) StaticContainer::get('tests.isProfilableCheckDisabled');
            return $isDisabled;
        } catch (\Exception $ex) {
            return false;
        }
    }

    // public for tests
    public function calculateIsProfilable(Row $summary)
    {
        $columns = $summary->getColumns();

        if (
            empty($columns['nb_visits']) // no visits
            || !isset($columns['nb_profilable']) // no profilable metric
            || $columns['nb_profilable'] === false
        ) {
            $value = true;
        } else {
            $nbProfilable = $columns['nb_profilable'];
            if ($nbProfilable < 0) { // sanity check
                $nbProfilable = 0;
            }

            // check that nb_profilable / nb_visits >= 0.01
            $value = $nbProfilable * 100 >= $columns['nb_visits'];
        }

        return $value;
    }

    public function getMetricsToRemoveifNotProfilable()
    {
        $metricsToRemove = [
            Metrics::INDEX_NB_UNIQ_VISITORS,
            Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS,
            Metrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS,
            Metrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS,
            Metrics::INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS,
            Metrics::INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS,
        ];

        $metricIdToNameMap = Metrics::getMappingFromIdToName();
        foreach ($metricsToRemove as $indexMetric) {
            $metricsToRemove[] = $metricIdToNameMap[$indexMetric];
        }

        return $metricsToRemove;
    }
}