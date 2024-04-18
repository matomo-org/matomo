<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitorInterest;

class Archiver extends \Piwik\Plugin\Archiver
{
    // third element is unit (s for seconds, default is munutes)
    const TIME_SPENT_RECORD_NAME = 'VisitorInterest_timeGap';
    const PAGES_VIEWED_RECORD_NAME = 'VisitorInterest_pageGap';
    const VISITS_COUNT_RECORD_NAME = 'VisitorInterest_visitsByVisitCount';
    const DAYS_SINCE_LAST_RECORD_NAME = 'VisitorInterest_daysSinceLastVisit';

    public static $timeGap = array(
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
    public static $pageGap = array(
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
    public static $visitNumberGap = array(
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
    public static $daysSinceLastVisitGap = array(
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

    /**
     * Transforms and returns the set of ranges used to calculate the 'visits by total time'
     * report from ranges in minutes to equivalent ranges in seconds.
     */
    public static function getSecondsGap()
    {
        $secondsGap = array();
        foreach (self::$timeGap as $gap) {
            if (count($gap) == 3 && $gap[2] == 's') { // if the units are already in seconds, just assign them
                $secondsGap[] = array($gap[0], $gap[1]);
            } elseif (count($gap) == 2) {
                $secondsGap[] = array($gap[0] * 60, $gap[1] * 60);
            } else {
                $secondsGap[] = array($gap[0] * 60);
            }
        }
        return $secondsGap;
    }
}
