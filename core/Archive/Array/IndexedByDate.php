<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_Archive
 */
class Piwik_Archive_Array_IndexedByDate extends Piwik_Archive_Array
{
    /**
     * Builds an array of Piwik_Archive of a given date range
     *
     * @param Piwik_Site $oSite
     * @param string $strPeriod eg. 'day' 'week' etc.
     * @param string $strDate A date range, eg. 'last10', 'previous5' or 'YYYY-MM-DD,YYYY-MM-DD'
     * @param Piwik_Segment $segment
     */
    public function __construct(Piwik_Site $oSite, $strPeriod, $strDate, Piwik_Segment $segment)
    {
        $rangePeriod = new Piwik_Period_Range($strPeriod, $strDate, $oSite->getTimezone());
        foreach ($rangePeriod->getSubperiods() as $subPeriod) {
            $startDate = $subPeriod->getDateStart();
            $archive = Piwik_Archive::build($oSite->getId(), $strPeriod, $startDate, $segment->getString());
            $archive->setSegment($segment);
            $this->archives[] = $archive;
        }
        $this->setSite($oSite);
    }

    /**
     * @return string
     */
    protected function getIndexName()
    {
        return 'date';
    }

    /**
     * @param Piwik_Archive $archive
     * @return mixed
     */
    protected function getDataTableLabelValue($archive)
    {
        return $archive->getPrettyDate();
    }

    /**
     * Given a list of fields defining numeric values, it will return a Piwik_DataTable_Array
     * which is an array of Piwik_DataTable_Simple, ordered by chronological order
     *
     * @param array|string $fields  array( fieldName1, fieldName2, ...)  Names of the mysql table fields to load
     * @return Piwik_DataTable_Array
     */
    public function getDataTableFromNumeric($fields)
    {
        $inNames = Piwik_Common::getSqlStringFieldsArray($fields);

        // we select in different shots
        // one per distinct table (case we select last 300 days, maybe we will  select from 10 different tables)
        $queries = array();
        foreach ($this->archives as $archive) {
            $archive->setRequestedReport(is_string($fields) ? $fields : current($fields));
            $archive->prepareArchive();
            if (!$archive->isThereSomeVisits) {
                continue;
            }

            $table = $archive->archiveProcessing->getTableArchiveNumericName();

            // for every query store IDs
            $queries[$table][] = $archive->getIdArchive();
        }
        // we select the requested value
        $db = Zend_Registry::get('db');

        // date => array( 'field1' =>X, 'field2'=>Y)
        // date2 => array( 'field1' =>X2, 'field2'=>Y2)

        $arrayValues = array();
        foreach ($queries as $table => $aIds) {
            $inIds = implode(', ', array_filter($aIds));
            if (empty($inIds)) {
                // Probable timezone configuration error, i.e., mismatch between PHP and MySQL server.
                continue;
            }

            $sql = "SELECT value, name, date1 as startDate
					FROM $table
					WHERE idarchive IN ( $inIds )
					AND name IN ( $inNames )
					ORDER BY date1, name";
            $values = $db->fetchAll($sql, $fields);
            foreach ($values as $value) {
                $timestamp = Piwik_Date::factory($value['startDate'])->getTimestamp();
                $arrayValues[$timestamp][$value['name']] = $this->formatNumericValue($value['value']);
            }
        }

        $contentArray = array();
        // we add empty tables so that every requested date has an entry, even if there is nothing
        // example: <result date="2007-01-01" />
        $archiveByTimestamp = array();
        foreach ($this->archives as $archive) {
            $timestamp = $archive->getTimestampStartDate();
            $archiveByTimestamp[$timestamp] = $archive;
            $contentArray[$timestamp]['table'] = $archive->makeDataTable($simple = true);
            $contentArray[$timestamp]['prettyDate'] = $archive->getPrettyDate();
        }

        foreach ($arrayValues as $timestamp => $aNameValues) {
            // undefined in some edge/unknown cases see http://dev.piwik.org/trac/ticket/2578
            if (isset($contentArray[$timestamp]['table'])) {
                $contentArray[$timestamp]['table']->addRowsFromArray($aNameValues);
            }
        }

        $tableArray = $this->getNewDataTableArray();
        foreach ($contentArray as $timestamp => $aData) {
            $tableArray->addTable($aData['table'], $aData['prettyDate']);
        }
        return $tableArray;
    }
}
