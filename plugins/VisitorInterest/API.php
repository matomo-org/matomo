<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitorInterest
 */

/**
 * VisitorInterest API lets you access two Visitor Engagement reports: number of visits per number of pages,
 * and number of visits per visit duration.
 *
 * @package Piwik_VisitorInterest
 */
class Piwik_VisitorInterest_API
{
    static private $instance = null;

    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function getDataTable($name, $idSite, $period, $date, $segment, $column = Piwik_Archive::INDEX_NB_VISITS)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Piwik_Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    public function getNumberOfVisitsPerVisitDuration($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('VisitorInterest_timeGap', $idSite, $period, $date, $segment);
        $dataTable->queueFilter('Sort', array('label', 'asc', true));
        $dataTable->queueFilter('BeautifyTimeRangeLabels', array(
                                                                Piwik_Translate('VisitorInterest_BetweenXYSeconds'),
                                                                Piwik_Translate('VisitorInterest_OneMinute'),
                                                                Piwik_Translate('VisitorInterest_PlusXMin')));
        return $dataTable;
    }

    public function getNumberOfVisitsPerPage($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable('VisitorInterest_pageGap', $idSite, $period, $date, $segment);
        $dataTable->queueFilter('Sort', array('label', 'asc', true));
        $dataTable->queueFilter('BeautifyRangeLabels', array(
                                                            Piwik_Translate('VisitorInterest_OnePage'),
                                                            Piwik_Translate('VisitorInterest_NPages')));
        return $dataTable;
    }

    /**
     * Returns a DataTable that associates counts of days (N) with the count of visits that
     * occurred within N days of the last visit.
     *
     * @param int $idSite The site to select data from.
     * @param string $period The period type.
     * @param string $date The date type.
     * @param string|bool $segment The segment.
     * @return Piwik_DataTable the archived report data.
     */
    public function getNumberOfVisitsByDaysSinceLast($idSite, $period, $date, $segment = false)
    {
        $recordName = 'VisitorInterest_daysSinceLastVisit';
        $dataTable = $this->getDataTable(
            $recordName, $idSite, $period, $date, $segment, Piwik_Archive::INDEX_NB_VISITS);

        $dataTable->queueFilter('BeautifyRangeLabels', array(
                                                            Piwik_Translate('General_OneDay'), Piwik_Translate('General_NDays')));

        return $dataTable;
    }

    /**
     * Returns a DataTable that associates ranges of visit numbers with the count of visits
     * whose visit number falls within those ranges.
     *
     * @param int $idSite The site to select data from.
     * @param string $period The period type.
     * @param string $date The date type.
     * @param string|bool $segment The segment.
     * @return Piwik_DataTable the archived report data.
     */
    public function getNumberOfVisitsByVisitCount($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(
            'VisitorInterest_visitsByVisitCount', $idSite, $period, $date, $segment, Piwik_Archive::INDEX_NB_VISITS);

        $dataTable->queueFilter('BeautifyRangeLabels', array(
                                                            Piwik_Translate('General_OneVisit'), Piwik_Translate('General_NVisits')));

        // add visit percent column
        self::addVisitsPercentColumn($dataTable);

        return $dataTable;
    }

    /**
     * Utility function that adds a visit percent column to a data table,
     * regardless of whether the data table is an data table array or just
     * a data table.
     *
     * @param Piwik_DataTable $dataTable The data table to modify.
     */
    private static function addVisitsPercentColumn($dataTable)
    {
        if ($dataTable instanceof Piwik_DataTable_Array) {
            foreach ($dataTable->getArray() as $table) {
                self::addVisitsPercentColumn($table);
            }
        } else {
            $totalVisits = array_sum($dataTable->getColumn(Piwik_Archive::INDEX_NB_VISITS));
            $dataTable->queueFilter('ColumnCallbackAddColumnPercentage', array(
                                                                              'nb_visits_percentage', 'nb_visits', $totalVisits));
        }
    }
}
