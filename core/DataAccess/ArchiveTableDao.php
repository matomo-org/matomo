<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics\Formatter;

/**
 * Data Access class for querying numeric & blob archive tables.
 */
class ArchiveTableDao
{
    /**
     * Analyzes numeric & blob tables for a single table date (ie, `'2015_01'`) and returns
     * statistics including:
     *
     * - number of archives present
     * - number of invalidated archives
     * - number of temporary archives
     * - number of error archives
     * - number of segment archives
     * - number of numeric rows
     * - number of blob rows
     *
     * @param string $tableDate ie `'2015_01'`
     * @return array
     */
    public function getArchiveTableAnalysis($tableDate)
    {
        $numericQueryEmptyRow = [
            'count_archives' => '-',
            'count_invalidated_archives' => '-',
            'count_temporary_archives' => '-',
            'count_error_archives' => '-',
            'count_segment_archives' => '-',
            'count_numeric_rows' => '-',
        ];

        $tableDate = str_replace("`", "", $tableDate); // for sanity

        $numericTable = Common::prefixTable("archive_numeric_$tableDate");
        $blobTable = Common::prefixTable("archive_blob_$tableDate");

        // query numeric table
        $sql = "SELECT CONCAT_WS('.', idsite, date1, date2, period) AS label,
                       SUM(CASE WHEN name LIKE 'done%' THEN 1 ELSE 0 END) AS count_archives,
                       SUM(CASE WHEN name LIKE 'done%' AND value = ? THEN 1 ELSE 0 END) AS count_invalidated_archives,
                       SUM(CASE WHEN name LIKE 'done%' AND value = ? THEN 1 ELSE 0 END) AS count_temporary_archives,
                       SUM(CASE WHEN name LIKE 'done%' AND value IN (?, ?) THEN 1 ELSE 0 END) AS count_error_archives,
                       SUM(CASE WHEN name LIKE 'done%' AND CHAR_LENGTH(name) > 32 THEN 1 ELSE 0 END) AS count_segment_archives,
                       SUM(CASE WHEN name NOT LIKE 'done%' THEN 1 ELSE 0 END) AS count_numeric_rows,
                       0 AS count_blob_rows
                  FROM `$numericTable`
              GROUP BY idsite, date1, date2, period ORDER BY idsite, period, date1, date2";

        $rows = Db::fetchAll($sql, array(ArchiveWriter::DONE_INVALIDATED, ArchiveWriter::DONE_OK_TEMPORARY,
            ArchiveWriter::DONE_ERROR, ArchiveWriter::DONE_ERROR_INVALIDATED));

        // index result
        $result = array();
        foreach ($rows as $row) {
            $result[$row['label']] = $row;
        }

        // query blob table & manually merge results (no FULL OUTER JOIN in mysql)
        $sql = "SELECT CONCAT_WS('.', idsite, date1, date2, period) AS label,
                       COUNT(*) AS count_blob_rows,
                       SUM(OCTET_LENGTH(value)) AS sum_blob_length
                  FROM `$blobTable`
              GROUP BY idsite, date1, date2, period ORDER BY idsite, period, date1, date2";

        foreach (Db::fetchAll($sql) as $blobStatsRow) {
            $label = $blobStatsRow['label'];

            if (isset($result[$label])) {
                $result[$label] = array_merge($result[$label], $blobStatsRow);
            } else {
                // ensure rows without numeric entries have the
                // same internal result array key order
                $result[$label] = array_merge(
                    ['label' => $label],
                    $numericQueryEmptyRow,
                    $blobStatsRow
                );
            }
        }

        return $result;
    }

    /**
     * Return invalidation queue table data
     *
     * @param bool $prettyTime
     *
     * @return array
     * @throws \Exception
     */
    public function getInvalidationQueueData(bool $prettyTime = false): array
    {
        $invalidationsTable = Common::prefixTable("archive_invalidations");
        $segmentsTable = Common::prefixTable("segment");
        $sql = "
            SELECT ai.*, s.definition               
            FROM `$invalidationsTable` ai 
            LEFT JOIN `$segmentsTable` s ON SUBSTRING(ai.name, 5) = s.hash
            GROUP BY ai.idinvalidation
            ORDER BY ts_invalidated, idinvalidation ASC";
        $invalidations = Db::fetchAll($sql);

        $metricsFormatter = new Formatter();

        $data = [];
        foreach ($invalidations as $i) {
            $waiting = (int) Date::now()->getTimestampUTC() - Date::factory($i['ts_invalidated'])->getTimestampUTC();
            $processing = (int) $i['ts_started'] ? Date::now()->getTimestampUTC() - (int) $i['ts_started'] : '';

            if ($prettyTime) {
                $waiting = $metricsFormatter->getPrettyTimeFromSeconds($waiting, true);
                if ($processing != '') {
                    $processing = $metricsFormatter->getPrettyTimeFromSeconds($processing, true);
                }
            }

            $d = [];
            $d['Invalidation'] = (int) $i['idinvalidation'];
            $d['Segment'] = $i['definition'];
            $d['Site'] = (int) $i['idsite'];
            $d['Period'] = ($i['period'] == 1 ? 'Day' : ($i['period'] == 2 ? 'Week' : ($i['period'] == 3 ? 'Month' :
                ($i['period'] == 4 ? 'Year' : 'Range'))));
            $d['Date'] = ($i['period'] == 1 ? $i['date1'] : ($i['period'] == 3 ? substr($i['date1'], 0, 7) :
                ($i['period'] == 4 ? substr($i['date1'], 0, 4) : $i['date1'] . ' - ' . $i['date2'])));
            $d['TimeQueued'] = $i['ts_invalidated'];
            $d['Waiting'] = $waiting;
            $d['Started'] = $i['ts_started'];
            $d['Processing'] = $processing;
            $d['Status'] = ($i['status'] == 1 ? 'Processing' : 'Queued');
            $data[] = $d;
        }
        return $data;
    }
}
