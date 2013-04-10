<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_DBStats
 */

/**
 * @see plugins/DBStats/MySQLMetadataProvider.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/DBStats/MySQLMetadataProvider.php';

/**
 * DBStats API is used to request the overall status of the Mysql tables in use by Piwik.
 *
 * @package Piwik_DBStats
 */
class Piwik_DBStats_API
{
    /** Singleton instance of this class. */
    static private $instance = null;

    /**
     * Gets or creates the DBStats API singleton.
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * The MySQLMetadataProvider instance that fetches table/db status information.
     */
    private $metadataProvider;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->metadataProvider = new Piwik_DBStats_MySQLMetadataProvider();
    }

    /**
     * Forces the next table status request to issue a query by reseting the table status cache.
     */
    public function resetTableStatuses()
    {
        Piwik::checkUserIsSuperUser();
        self::getInstance()->metadataProvider = new Piwik_DBStats_MySQLMetadataProvider();
    }

    /**
     * Gets some general information about this Piwik installation, including the count of
     * websites tracked, the count of users and the total space used by the database.
     *
     * @return array Contains the website count, user count and total space used by the database.
     */
    public function getGeneralInformation()
    {
        Piwik::checkUserIsSuperUser();
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
        Piwik::checkUserIsSuperUser();
        return $this->metadataProvider->getDBStatus();
    }

    /**
     * Returns a datatable summarizing how data is distributed among Piwik tables.
     *
     * This function will group tracker tables, numeric archive tables, blob archive tables
     * and other tables together so only four rows are shown.
     *
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getDatabaseUsageSummary()
    {
        Piwik::checkUserIsSuperUser();

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

        $result = new Piwik_DataTable();
        $result->addRowsFromArrayWithIndexLabel($rows);
        return $result;
    }

    /**
     * Returns a datatable describing how much space is taken up by each log table.
     *
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getTrackerDataSummary()
    {
        Piwik::checkUserIsSuperUser();
        return $this->getTablesSummary($this->metadataProvider->getAllLogTableStatus());
    }

    /**
     * Returns a datatable describing how much space is taken up by each numeric
     * archive table.
     *
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getMetricDataSummary()
    {
        Piwik::checkUserIsSuperUser();
        return $this->getTablesSummary($this->metadataProvider->getAllNumericArchiveStatus());
    }

    /**
     * Returns a datatable describing how much space is taken up by each numeric
     * archive table, grouped by year.
     *
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getMetricDataSummaryByYear()
    {
        Piwik::checkUserIsSuperUser();

        $dataTable = $this->getMetricDataSummary();

        $getTableYear = array('Piwik_DBStats_API', 'getArchiveTableYear');
        $dataTable->filter('GroupBy', array('label', $getTableYear));

        return $dataTable;
    }

    /**
     * Returns a datatable describing how much space is taken up by each blob
     * archive table.
     *
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getReportDataSummary()
    {
        Piwik::checkUserIsSuperUser();
        return $this->getTablesSummary($this->metadataProvider->getAllBlobArchiveStatus());
    }

    /**
     * Returns a datatable describing how much space is taken up by each blob
     * archive table, grouped by year.
     *
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getReportDataSummaryByYear()
    {
        Piwik::checkUserIsSuperUser();

        $dataTable = $this->getReportDataSummary();

        $getTableYear = array('Piwik_DBStats_API', 'getArchiveTableYear');
        $dataTable->filter('GroupBy', array('label', $getTableYear));

        return $dataTable;
    }

    /**
     * Returns a datatable describing how much space is taken up by 'admin' tables.
     *
     * An 'admin' table is a table that is not central to analytics functionality.
     * So any table that isn't an archive table or a log table is an 'admin' table.
     *
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getAdminDataSummary()
    {
        Piwik::checkUserIsSuperUser();
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
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getIndividualReportsSummary($forceCache = false)
    {
        Piwik::checkUserIsSuperUser();
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
     * @return Piwik_DataTable A datatable with three columns: 'data_size', 'index_size', 'row_count'.
     */
    public function getIndividualMetricsSummary($forceCache = false)
    {
        Piwik::checkUserIsSuperUser();
        return $this->metadataProvider->getRowCountsAndSizeByMetricName($forceCache);
    }

    /**
     * Returns a datatable representation of a set of table statuses.
     *
     * @param array $statuses The table statuses to summarize.
     * @return Piwik_DataTable
     */
    private function getTablesSummary($statuses)
    {
        $dataTable = new Piwik_DataTable();
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
        return strpos($name, Piwik_Common::prefixTable('archive_numeric_')) === 0;
    }

    /** Returns true if $name is the name of a blob archive table, false if otherwise. */
    private function isBlobArchiveTable($name)
    {
        return strpos($name, Piwik_Common::prefixTable('archive_blob_')) === 0;
    }

    /** Returns true if $name is the name of a log table, false if otherwise. */
    private function isTrackerTable($name)
    {
        return strpos($name, Piwik_Common::prefixTable('log_')) === 0;
    }

    /**
     * Gets the year of an archive table from its name.
     *
     * @param string $tableName
     * @param string The year.
     *
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
