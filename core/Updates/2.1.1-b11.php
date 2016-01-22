<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updates;

use Piwik\ArchiveProcessor\Rules;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db\BatchInsert;
use Piwik\Db;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyApi;
use Piwik\Segment;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_2_1_1_b11 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $returningMetrics = array(
            'nb_visits_returning',
            'nb_actions_returning',
            'max_actions_returning',
            'sum_visit_length_returning',
            'bounce_count_returning',
            'nb_visits_converted_returning',
            'nb_uniq_visitors_returning'
        );

        $now = Date::factory('now')->getDatetime();

        $archiveNumericTables = Db::get()->fetchCol("SHOW TABLES LIKE '%archive_numeric%'");

        // for each numeric archive table, copy *_returning metrics to VisitsSummary metrics w/ the appropriate
        // returning visit segment
        foreach ($archiveNumericTables as $table) {
            // get archives w/ *._returning
            $sql = "SELECT idarchive, idsite, period, date1, date2 FROM $table
                    WHERE name IN ('" . implode("','", $returningMetrics) . "')
                    GROUP BY idarchive";
            $idArchivesWithReturning = Db::fetchAll($sql);

            // get archives for visitssummary returning visitor segment
            $sql = "SELECT idarchive, idsite, period, date1, date2 FROM $table
                    WHERE name = ?  GROUP BY idarchive";
            $visitSummaryReturningSegmentDone = Rules::getDoneFlagArchiveContainsOnePlugin(
                new Segment(VisitFrequencyApi::RETURNING_VISITOR_SEGMENT, $idSites = array()), 'VisitsSummary');
            $idArchivesWithVisitReturningSegment = Db::fetchAll($sql, array($visitSummaryReturningSegmentDone));

            // collect info for new visitssummary archives have to be created to match archives w/ *._returning
            // metrics
            $missingIdArchives = array();
            $idArchiveMappings = array();
            foreach ($idArchivesWithReturning as $row) {
                $withMetricsIdArchive = $row['idarchive'];
                foreach ($idArchivesWithVisitReturningSegment as $segmentRow) {
                    if ($row['idsite'] == $segmentRow['idsite']
                        && $row['period'] == $segmentRow['period']
                        && $row['date1'] == $segmentRow['date1']
                        && $row['date2'] == $segmentRow['date2']
                    ) {
                        $idArchiveMappings[$withMetricsIdArchive] = $segmentRow['idarchive'];
                    }
                }

                if (!isset($idArchiveMappings[$withMetricsIdArchive])) {
                    $missingIdArchives[$withMetricsIdArchive] = $row;
                }
            }

            // if there are missing idarchives, fill out new archive row values
            if (!empty($missingIdArchives)) {
                $newIdArchiveStart = Db::fetchOne("SELECT MAX(idarchive) FROM $table") + 1;
                foreach ($missingIdArchives as $withMetricsIdArchive => &$rowToInsert) {
                    $idArchiveMappings[$withMetricsIdArchive] = $newIdArchiveStart;

                    $rowToInsert['idarchive'] = $newIdArchiveStart;
                    $rowToInsert['ts_archived'] = $now;
                    $rowToInsert['name'] = $visitSummaryReturningSegmentDone;
                    $rowToInsert['value'] = ArchiveWriter::DONE_OK;

                    ++$newIdArchiveStart;
                }

                // add missing archives
                try {
                    $params = array();
                    foreach ($missingIdArchives as $missingIdArchive) {
                        $params[] = array_values($missingIdArchive);
                    }
                    BatchInsert::tableInsertBatch($table, array_keys(reset($missingIdArchives)), $params, $throwException = false, $charset = 'latin1');
                } catch (\Exception $ex) {
                    Updater::handleQueryError($ex, "<batch insert>", false, __FILE__);
                }
            }

            // update idarchive & name columns in rows with *._returning metrics
            $updateSqlPrefix = "UPDATE $table
                                   SET idarchive = CASE idarchive ";
            $updateSqlSuffix = " END, name = CASE name ";
            foreach ($returningMetrics as $metric) {
                $newMetricName = substr($metric, 0, strlen($metric) - strlen(VisitFrequencyApi::COLUMN_SUFFIX));
                $updateSqlSuffix .= "WHEN '$metric' THEN '" . $newMetricName . "' ";
            }
            $updateSqlSuffix .= " END WHERE idarchive IN (%s)
                                        AND name IN ('" . implode("','", $returningMetrics) . "')";

            // update only 1000 rows at a time so we don't send too large an SQL query to MySQL
            foreach (array_chunk($missingIdArchives, 1000, $preserveKeys = true) as $chunk) {
                $idArchives = array();

                $updateSql = $updateSqlPrefix;
                foreach ($chunk as $withMetricsIdArchive => $row) {
                    $updateSql .= "WHEN $withMetricsIdArchive THEN {$row['idarchive']} ";

                    $idArchives[] = $withMetricsIdArchive;
                }
                $updateSql .= sprintf($updateSqlSuffix, implode(',', $idArchives));

                Updater::executeMigrationQuery($updateSql, false, __FILE__);
            }
        }
    }
}
