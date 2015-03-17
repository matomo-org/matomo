<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;

/**
 * DBStats API is used to request the overall status of the Mysql tables in use by Piwik.
 * @hideExceptForSuperUser
 * @method static \Piwik\Plugins\DBStats\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * The MySQLMetadataProvider instance that fetches table/db status information.
     */
    private $metadataProvider;

    public function __construct(MySQLMetadataProvider $metadataProvider)
    {
        $this->metadataProvider = $metadataProvider;
    }

    /**
     * Gets some general information about this Piwik installation, including the count of
     * websites tracked, the count of users and the total space used by the database.
     *
     * 
     * @return array Contains the website count, user count and total space used by the database.
     */
    public function getGeneralInformation()
    {
        Piwik::checkUserHasSuperUserAccess();
        // calculate total size
        $totalSpaceUsed = 0;
        foreach ($this->metadataProvider->getAllTablesStatus() as $status) {
            $totalSpaceUsed += $status['Data_length'] + $status['Index_length'];
        }

        $siteTableStatus = $this->metadataProvider->getTableStatus('site');
        $userTableStatus = $this->metadataProvider->getTableStatus('user');

        $siteCount = $siteTableStatus['Rows'];
        $userCount = $userTableStatus['Rows'];

        return array($siteCount, $userCount, $totalSpaceUsed);
    }

    /**
     * Gets general database info that is not specific to any table.
     *
     * @return array See http://dev.mysql.com/doc/refman/5.1/en/show-status.html .
     */
    public function getDBStatus()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->metadataProvider->getDBStatus();
    }

    /**
     * Returns a datatable summarizing how data is distributed among Piwik tables.
     *
     * This function will group tracker tables, numeric archive tables, blob archive tables
     * and other tables together so only four rows are shown.
     *
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getDatabaseUsageSummary()
    {
        Piwik::checkUserHasSuperUserAccess();

        $emptyRow = array('data_size' => 0, 'index_size' => 0, 'row_count' => 0);
        $rows = array(
            'tracker_data' => $emptyRow,
            'metric_data'  => $emptyRow,
            'report_data'  => $emptyRow,
            'other_data'   => $emptyRow
        );

        foreach ($this->metadataProvider->getAllTablesStatus() as $status) {
            if ($this->isNumericArchiveTable($status['Name'])) {
                $rowToAddTo = & $rows['metric_data'];
            } else if ($this->isBlobArchiveTable($status['Name'])) {
                $rowToAddTo = & $rows['report_data'];
            } else if ($this->isTrackerTable($status['Name'])) {
                $rowToAddTo = & $rows['tracker_data'];
            } else {
                $rowToAddTo = & $rows['other_data'];
            }

            $rowToAddTo['data_size'] += $status['Data_length'];
            $rowToAddTo['index_size'] += $status['Index_length'];
            $rowToAddTo['row_count'] += $status['Rows'];
        }

        return DataTable::makeFromIndexedArray($rows);
    }

    /**
     * Returns a datatable describing how much space is taken up by each log table.
     *
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getTrackerDataSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->getTablesSummary($this->metadataProvider->getAllLogTableStatus());
    }

    /**
     * Returns a datatable describing how much space is taken up by each numeric
     * archive table.
     *
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getMetricDataSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->getTablesSummary($this->metadataProvider->getAllNumericArchiveStatus());
    }

    /**
     * Returns a datatable describing how much space is taken up by each numeric
     * archive table, grouped by year.
     *
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getMetricDataSummaryByYear()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dataTable = $this->getMetricDataSummary();

        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\API::getArchiveTableYear'));

        return $dataTable;
    }

    /**
     * Returns a datatable describing how much space is taken up by each blob
     * archive table.
     *
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getReportDataSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->getTablesSummary($this->metadataProvider->getAllBlobArchiveStatus());
    }

    /**
     * Returns a datatable describing how much space is taken up by each blob
     * archive table, grouped by year.
     *
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getReportDataSummaryByYear()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dataTable = $this->getReportDataSummary();

        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\API::getArchiveTableYear'));

        return $dataTable;
    }

    /**
     * Returns a datatable describing how much space is taken up by 'admin' tables.
     *
     * An 'admin' table is a table that is not central to analytics functionality.
     * So any table that isn't an archive table or a log table is an 'admin' table.
     *
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getAdminDataSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->getTablesSummary($this->metadataProvider->getAllAdminTableStatus());
    }

    /**
     * Returns a datatable describing how much total space is taken up by each
     * individual report type.
     *
     * Goal reports and reports of the format .*_[0-9]+ are grouped together.
     *
     * @param bool $forceCache false to use the cached result, true to run the queries again and
     *                         cache the result.
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getIndividualReportsSummary($forceCache = false)
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->metadataProvider->getRowCountsAndSizeByBlobName($forceCache);
    }

    /**
     * Returns a datatable describing how much total space is taken up by each
     * individual metric type.
     *
     * Goal metrics, metrics of the format .*_[0-9]+ and 'done...' metrics are grouped together.
     *
     * @param bool $forceCache false to use the cached result, true to run the queries again and
     *                         cache the result.
     * @return DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getIndividualMetricsSummary($forceCache = false)
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->metadataProvider->getRowCountsAndSizeByMetricName($forceCache);
    }

    /**
     * Returns a datatable representation of a set of table statuses.
     *
     * @param array $statuses The table statuses to summarize.
     * @return DataTable
     */
    private function getTablesSummary($statuses)
    {
        $dataTable = new DataTable();
        foreach ($statuses as $status) {
            $dataTable->addRowFromSimpleArray(array(
                                                   'label'      => $status['Name'],
                                                   'data_size'  => $status['Data_length'],
                                                   'index_size' => $status['Index_length'],
                                                   'row_count'  => $status['Rows']
                                              ));
        }
        return $dataTable;
    }

    /** Returns true if $name is the name of a numeric archive table, false if otherwise. */
    private function isNumericArchiveTable($name)
    {
        return strpos($name, Common::prefixTable('archive_numeric_')) === 0;
    }

    /** Returns true if $name is the name of a blob archive table, false if otherwise. */
    private function isBlobArchiveTable($name)
    {
        return strpos($name, Common::prefixTable('archive_blob_')) === 0;
    }

    /** Returns true if $name is the name of a log table, false if otherwise. */
    private function isTrackerTable($name)
    {
        return strpos($name, Common::prefixTable('log_')) === 0;
    }

    /**
     * Gets the year of an archive table from its name.
     *
     * @param string $tableName
     *
     * @return string  the year
     * @ignore
     */
    public static function getArchiveTableYear($tableName)
    {
        if (preg_match("/archive_(?:numeric|blob)_([0-9]+)_/", $tableName, $matches) === 0) {
            return '';
        }

        return $matches[1];
    }
}
