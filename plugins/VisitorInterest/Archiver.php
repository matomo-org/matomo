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

class Piwik_VisitorInterest_Archiver extends Piwik_PluginsArchiver
{
    // third element is unit (s for seconds, default is munutes)
    const TIME_SPENT_RECORD_NAME = 'VisitorInterest_timeGap';
    const PAGES_VIEWED_RECORD_NAME = 'VisitorInterest_pageGap';
    const VISITS_COUNT_RECORD_NAME = 'VisitorInterest_visitsByVisitCount';
    const DAYS_SINCE_LAST_RECORD_NAME = 'VisitorInterest_daysSinceLastVisit';

    protected static $timeGap = array(
        array(0, 10, 's'),
        array(11, 30, 's'),
        array(31, 60, 's'),
        array(1, 2),
        array(2, 4),
        array(4, 7),
        array(7, 10),
        array(10, 15),
        array(15, 30),
        array(30)
    );
    protected static $pageGap = array(
        array(1, 1),
        array(2, 2),
        array(3, 3),
        array(4, 4),
        array(5, 5),
        array(6, 7),
        array(8, 10),
        array(11, 14),
        array(15, 20),
        array(20)
    );
    /**
     * The set of ranges used when calculating the 'visitors who visited at least N times' report.
     */
    protected static $visitNumberGap = array(
        array(1, 1),
        array(2, 2),
        array(3, 3),
        array(4, 4),
        array(5, 5),
        array(6, 6),
        array(7, 7),
        array(8, 8),
        array(9, 14),
        array(15, 25),
        array(26, 50),
        array(51, 100),
        array(101, 200),
        array(200)
    );
    /**
     * The set of ranges used when calculating the 'days since last visit' report.
     */
    protected static $daysSinceLastVisitGap = array(
        array(0, 0),
        array(1, 1),
        array(2, 2),
        array(3, 3),
        array(4, 4),
        array(5, 5),
        array(6, 6),
        array(7, 7),
        array(8, 14),
        array(15, 30),
        array(31, 60),
        array(61, 120),
        array(121, 364),
        array(364)
    );

    public function archiveDay()
    {
        // these prefixes are prepended to the 'SELECT as' parts of each SELECT expression. detecting
        // these prefixes allows us to get all the data in one query.
        $prefixes = array(
            self::TIME_SPENT_RECORD_NAME => 'tg',
            self::PAGES_VIEWED_RECORD_NAME => 'pg',
            self::VISITS_COUNT_RECORD_NAME => 'vbvn',
            self::DAYS_SINCE_LAST_RECORD_NAME => 'dslv',
        );
        $row = $this->aggregateFromVisits($prefixes);

        foreach($prefixes as $recordName => $selectAsPrefix) {
            $processor = $this->getProcessor();
            $dataTable = $processor->getSimpleDataTableFromRow($row, Piwik_Archive::INDEX_NB_VISITS, $selectAsPrefix);
            $processor->insertBlobRecord($recordName, $dataTable->getSerialized());
        }
    }

    protected function aggregateFromVisits($prefixes)
    {
        // extra condition for the SQL SELECT that makes sure only returning visits are counted
        // when creating the 'days since last visit' report. the SELECT expression below it
        // is used to count all new visits.
        $daysSinceLastExtraCondition = 'and log_visit.visitor_returning = 1';
        $selectAs = $prefixes[self::DAYS_SINCE_LAST_RECORD_NAME] . 'General_NewVisits';
        $newVisitCountSelect = "sum(case when log_visit.visitor_returning = 0 then 1 else 0 end) as `$selectAs`";

        // create the select expressions to use
        $timeGapSelects = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visit_total_time', self::getSecondsGap(), 'log_visit', $prefixes[self::TIME_SPENT_RECORD_NAME]);

        $pageGapSelects = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visit_total_actions', self::$pageGap, 'log_visit', $prefixes[self::PAGES_VIEWED_RECORD_NAME]);

        $visitsByVisitNumSelects = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visitor_count_visits', self::$visitNumberGap, 'log_visit', $prefixes[self::VISITS_COUNT_RECORD_NAME]);

        $daysSinceLastVisitSelects = Piwik_ArchiveProcessing_Day::buildReduceByRangeSelect(
            'visitor_days_since_last', self::$daysSinceLastVisitGap, 'log_visit', $prefixes[self::DAYS_SINCE_LAST_RECORD_NAME],
            $daysSinceLastExtraCondition);

        array_unshift($daysSinceLastVisitSelects, $newVisitCountSelect);

        $selects = array_merge(
            $timeGapSelects, $pageGapSelects, $visitsByVisitNumSelects, $daysSinceLastVisitSelects);

        // select data for every report
        $row = $this->getProcessor()->queryVisitsSimple(implode(',', $selects));
        return $row;
    }

    /**
     * Transforms and returns the set of ranges used to calculate the 'visits by total time'
     * report from ranges in minutes to equivalent ranges in seconds.
     */
    protected static function getSecondsGap()
    {
        $secondsGap = array();
        foreach (self::$timeGap as $gap) {
            if (count($gap) == 3 && $gap[2] == 's') // if the units are already in seconds, just assign them
            {
                $secondsGap[] = array($gap[0], $gap[1]);
            } else if (count($gap) == 2) {
                $secondsGap[] = array($gap[0] * 60, $gap[1] * 60);
            } else {
                $secondsGap[] = array($gap[0] * 60);
            }
        }
        return $secondsGap;
    }

    public function archivePeriod()
    {
        $dataTableToSum = array(
            self::TIME_SPENT_RECORD_NAME,
            self::PAGES_VIEWED_RECORD_NAME,
            self::VISITS_COUNT_RECORD_NAME,
            self::DAYS_SINCE_LAST_RECORD_NAME
        );
        $this->getProcessor()->archiveDataTable($dataTableToSum);
    }
}