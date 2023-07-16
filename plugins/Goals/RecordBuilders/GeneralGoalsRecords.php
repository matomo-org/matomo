<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreHome\Columns\VisitsCount;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\Goals\Archiver;
use Piwik\Plugins\Goals\Columns\DaysToConversion;
use Piwik\Plugins\Goals\Columns\IdGoal;
use Piwik\Plugins\Goals\Goals;
use Piwik\Tracker\GoalManager;

class GeneralGoalsRecords extends Base
{
    const VISITS_COUNT_FIELD = 'visitor_count_visits';
    const LOG_CONVERSION_TABLE = 'log_conversion';
    const SECONDS_SINCE_FIRST_VISIT_FIELD = 'visitor_seconds_since_first';

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $idSite = $this->getSiteId($archiveProcessor);
        if (empty($idSite)) {
            return [];
        }

        $totalConversions = 0;
        $totalRevenue = 0;

        $goalMetrics = [];

        /** @var DataTable[] $visitsToConversions */
        $visitsToConversions = [];
        /** @var DataTable[] $daysToConversions */
        $daysToConversions = [];

        $siteHasEcommerceOrGoals = $this->hasAnyGoalOrEcommerce($idSite);

        // Special handling for sites that contain subordinated sites, like in roll up reporting.
        // A roll up site, might not have ecommerce enabled or any configured goals,
        // but if a subordinated site has, we calculate the overview conversion metrics nevertheless
        if ($siteHasEcommerceOrGoals === false) {
            $idSitesToArchive = $archiveProcessor->getParams()->getIdSites();

            foreach ($idSitesToArchive as $idSite) {
                if ($this->hasAnyGoalOrEcommerce($idSite)) {
                    $siteHasEcommerceOrGoals = true;
                    break;
                }
            }
        }

        // try to query goal data only, if goals or ecommerce is actually used
        // otherwise we simply insert empty records
        if ($siteHasEcommerceOrGoals) {
            $query = $archiveProcessor->newLogQuery('log_conversion');
            $query->addDimension(new IdGoal(), 'idgoal');
            $query->addConversionMetrics();
            $query->addHistogram('daysToConversion', new DaysToConversion(), Metrics::INDEX_NB_CONVERSIONS, Archiver::$daysToConvRanges);
            $query->addHistogram('visitsCount', new VisitsCount(), Metrics::INDEX_NB_CONVERSIONS, Archiver::$visitCountRanges);

            $resultSet = $query->execute();
            foreach ($resultSet as $row) {
                $idGoal = $row['idgoal'];

                foreach ($query->getMetricFields() as $field) {
                    $goalMetrics[$idGoal][$field] = ($goalMetrics[$idGoal][$field] ?? 0) + $row[$field];
                }

                if (empty($visitsToConversions[$idGoal])) {
                    $visitsToConversions[$idGoal] = new DataTable();
                }

                $visitsToConversions[$idGoal]->sumSimpleArrayTraversable($row['visitsCount']);

                if (empty($daysToConversions[$idGoal])) {
                    $daysToConversions[$idGoal] = new DataTable();
                }

                $daysToConversions[$idGoal]->sumSimpleArrayTraversable($row['daysToConversion']);

                // We don't want to sum Abandoned cart metrics in the overall revenue/conversions/converted visits
                // since it is a "negative conversion"
                if ($idGoal != GoalManager::IDGOAL_CART) {
                    $totalConversions += $row[Metrics::INDEX_GOAL_NB_CONVERSIONS];
                    $totalRevenue += $row[Metrics::INDEX_GOAL_REVENUE];
                }
            }
        }

        // Stats by goal, for all visitors
        $numericRecords = $this->getConversionsNumericMetrics($goalMetrics);

        $nbConvertedVisits = $archiveProcessor->getNumberOfVisitsConverted();

        $result = array_merge([
            // Stats for all goals
            Archiver::getRecordName('nb_conversions')      => $totalConversions,
            Archiver::getRecordName('nb_visits_converted') => $nbConvertedVisits,
            Archiver::getRecordName('revenue')             => $totalRevenue,
        ], $numericRecords);

        foreach ($visitsToConversions as $idGoal => $table) {
            $recordName = Archiver::getRecordName(Archiver::VISITS_UNTIL_RECORD_NAME, $idGoal);
            $result[$recordName] = $table;
        }
        $result[Archiver::getRecordName(Archiver::VISITS_UNTIL_RECORD_NAME)] = $this->getOverviewFromGoalTables($visitsToConversions);

        foreach ($daysToConversions as $idGoal => $table) {
            $recordName = Archiver::getRecordName(Archiver::DAYS_UNTIL_CONV_RECORD_NAME, $idGoal);
            $result[$recordName] = $table;
        }
        $result[Archiver::getRecordName(Archiver::DAYS_UNTIL_CONV_RECORD_NAME)] = $this->getOverviewFromGoalTables($daysToConversions);

        return $result;
    }

    private function getOverviewFromGoalTables(array $tableByGoal): DataTable
    {
        $overview = new DataTable();
        foreach ($tableByGoal as $idGoal => $table) {
            if ($this->isStandardGoal($idGoal)) {
                $overview->addDataTable($table);
            }
        }
        return $overview;
    }

    private function isStandardGoal(int $idGoal): bool
    {
        return !in_array($idGoal, $this->getEcommerceIdGoals());
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        $goals = API::getInstance()->getGoals($this->getSiteId($archiveProcessor));
        $goals = array_keys($goals);

        if (Manager::getInstance()->isPluginActivated('Ecommerce')) {
            $goals = array_merge($goals, $this->getEcommerceIdGoals());
        }

        // Overall goal metrics
        $goals[] = false;

        $records = [];
        foreach ($goals as $idGoal) {
            $metricsToSum = Goals::getGoalColumns($idGoal);
            foreach ($metricsToSum as $metricName) {
                $records[] = Record::make(Record::TYPE_NUMERIC, Archiver::getRecordName($metricName, $idGoal));
            }

            $records[] = Record::make(Record::TYPE_BLOB, Archiver::getRecordName(Archiver::VISITS_UNTIL_RECORD_NAME, $idGoal));
            $records[] = Record::make(Record::TYPE_BLOB, Archiver::getRecordName(Archiver::DAYS_UNTIL_CONV_RECORD_NAME, $idGoal));
        }
        return $records;
    }

    protected function getConversionsNumericMetrics(array $goals): array
    {
        $numericRecords = [];
        foreach ($goals as $idGoal => $array) {
            foreach ($array as $metricId => $value) {
                $metricName = Metrics::$mappingFromIdToNameGoal[$metricId];
                $recordName = Archiver::getRecordName($metricName, $idGoal);
                $numericRecords[$recordName] = $value;
            }
        }
        return $numericRecords;
    }

    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        return $archiveProcessor->getNumberOfVisitsConverted() > 0;
    }
}
