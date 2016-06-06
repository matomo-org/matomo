<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Scheduler;

use Piwik\Plugin;
use Piwik\Scheduler\Timetable;
use Piwik\Tests\Framework\Mock\PiwikOption;
use ReflectionProperty;

/**
 * @group Scheduler
 */
class TimetableTest extends \PHPUnit_Framework_TestCase
{
    private $timetable = array(
        'CoreAdminHome.purgeOutdatedArchives' => 1355529607,
        'PrivacyManager.deleteReportData_1'   => 1322229607,
    );

    /**
     * Dataprovider for testGetTimetableFromOptionValue
     */
    public function getTimetableFromOptionValueTestCases()
    {
        return array(

            // invalid option values should return a fresh array
            array(array(), false),
            array(array(), null),
            array(array(), 1),
            array(array(), ''),
            array(array(), 'test'),

            // valid serialized array
            array(
                array(
                    'CoreAdminHome.purgeOutdatedArchives' => 1355529607,
                    'PrivacyManager.deleteReportData'     => 1355529607,
                ),
                'a:2:{s:35:"CoreAdminHome.purgeOutdatedArchives";i:1355529607;s:31:"PrivacyManager.deleteReportData";i:1355529607;}'
            ),
        );
    }

    /**
     * @dataProvider getTimetableFromOptionValueTestCases
     */
    public function testGetTimetableFromOptionValue($expectedTimetable, $option)
    {
        self::stubPiwikOption($option);

        $timetable = new Timetable();
        $this->assertEquals($expectedTimetable, $timetable->getTimetable());

        self::resetPiwikOption();
    }

    /**
     * Dataprovider for testTaskHasBeenScheduledOnce
     */
    public function taskHasBeenScheduledOnceTestCases()
    {
        return array(
            array(true, 'CoreAdminHome.purgeOutdatedArchives', $this->timetable),
            array(true, 'PrivacyManager.deleteReportData_1', $this->timetable),
            array(false, 'ScheduledReports.weeklySchedule"', $this->timetable)
        );
    }

    /**
     * @dataProvider taskHasBeenScheduledOnceTestCases
     */
    public function testTaskHasBeenScheduledOnce($expectedDecision, $taskName, $timetable)
    {
        self::stubPiwikOption(false);

        $timetableObj = new Timetable();
        $timetableObj->setTimetable($timetable);
        $this->assertEquals($expectedDecision, $timetableObj->taskHasBeenScheduledOnce($taskName));

        self::resetPiwikOption();
    }

    /**
     * Dataprovider for testGetScheduledTimeForMethod
     */
    public function getScheduledTimeForMethodTestCases()
    {
        $timetable = serialize($this->timetable);

        return array(
            array(1355529607, 'CoreAdminHome', 'purgeOutdatedArchives', null, $timetable),
            array(1322229607, 'PrivacyManager', 'deleteReportData', 1, $timetable),
            array(false, 'ScheduledReports', 'weeklySchedule', null, $timetable)
        );
    }

    /**
     * Dataprovider for testTaskShouldBeExecuted
     */
    public function taskShouldBeExecutedTestCases()
    {
        $timetable = $this->timetable;

        // set a date in the future (should not run)
        $timetable['CoreAdminHome.purgeOutdatedArchives'] = time() + 60000;

        // set now (should run)
        $timetable['PrivacyManager.deleteReportData_1'] = time();

        return array(
            array(false, 'CoreAdminHome.purgeOutdatedArchives', $timetable),
            array(true, 'PrivacyManager.deleteReportData_1', $timetable),
            array(false, 'ScheduledReports.weeklySchedule"', $timetable)
        );
    }

    /**
     * @dataProvider taskShouldBeExecutedTestCases
     */
    public function testTaskShouldBeExecuted($expectedDecision, $taskName, $timetable)
    {
        self::stubPiwikOption(serialize($timetable));

        $timetable = new Timetable();
        $this->assertEquals($expectedDecision, $timetable->shouldExecuteTask($taskName));

        self::resetPiwikOption();
    }

    private static function stubPiwikOption($timetable)
    {
        self::getReflectedPiwikOptionInstance()->setValue(new PiwikOption($timetable));
    }

    private static function resetPiwikOption()
    {
        self::getReflectedPiwikOptionInstance()->setValue(null);
    }

    private static function getReflectedPiwikOptionInstance()
    {
        $piwikOptionInstance = new ReflectionProperty('Piwik\Option', 'instance');
        $piwikOptionInstance->setAccessible(true);
        return $piwikOptionInstance;
    }
}
