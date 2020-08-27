<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Db;

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
        $numericQueryEmptyRow = array(
            'count_archives' => '-',
            'count_invalidated_archives' => '-',
            'count_temporary_archives' => '-',
            'count_error_archives' => '-',
            'count_segment_archives' => '-',
            'count_numeric_rows' => '-',
        );

        $tableDate = str_replace("`", "", $tableDate); // for sanity

        $numericTable = Common::prefixTable("archive_numeric_$tableDate");
        $blobTable = Common::prefixTable("archive_blob_$tableDate");

        // query numeric table
        $sql = "SELECT CONCAT_WS('.', idsite, date1, date2, period) AS label,
                       SUM(CASE WHEN name LIKE 'done%' THEN 1 ELSE 0 END) AS count_archives,
                       SUM(CASE WHEN name LIKE 'done%' AND value = ? THEN 1 ELSE 0 END) AS count_invalidated_archives,
                       SUM(CASE WHEN name LIKE 'done%' AND value = ? THEN 1 ELSE 0 END) AS count_temporary_archives,
                       SUM(CASE WHEN name LIKE 'done%' AND value = ? THEN 1 ELSE 0 END) AS count_error_archives,
                       SUM(CASE WHEN name LIKE 'done%' AND CHAR_LENGTH(name) > 32 THEN 1 ELSE 0 END) AS count_segment_archives,
                       SUM(CASE WHEN name NOT LIKE 'done%' THEN 1 ELSE 0 END) AS count_numeric_rows,
                       0 AS count_blob_rows
                  FROM `$numericTable`
              GROUP BY idsite, date1, date2, period";

        $rows = Db::fetchAll($sql, array(ArchiveWriter::DONE_INVALIDATED, ArchiveWriter::DONE_OK_TEMPORARY,
            ArchiveWriter::DONE_ERROR));

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
              GROUP BY idsite, date1, date1, period";

        foreach (Db::fetchAll($sql) as $blobStatsRow) {
            $label = $blobStatsRow['label'];
            if (isset($result[$label])) {
                $result[$label] = array_merge($result[$label], $blobStatsRow);
            } else {
                $result[$label] = $blobStatsRow + $numericQueryEmptyRow;
            }
        }

        return $result;
    }
}