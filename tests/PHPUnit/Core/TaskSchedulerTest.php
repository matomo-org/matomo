<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\EventDispatcher;
use Piwik\ScheduledTask;
use Piwik\ScheduledTaskTimetable;
use Piwik\ScheduledTime\Daily;
use Piwik\TaskScheduler;

class TaskSchedulerTest extends PHPUnit_Framework_TestCase
{
    private static function getTestTimetable()
    {
        return array(
            'CoreAdminHome.purgeOutdatedArchives' => 1355529607,
            'PrivacyManager.deleteReportData_1'   => 1322229607,
        );
    }

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
     * @group Core
     * @dataProvider getTimetableFromOptionValueTestCases
     */
    public function testGetTimetableFromOptionValue($expectedTimetable, $option)
    {
        self::stubPiwikOption($option);

        $timetable = new ScheduledTaskTimetable();
        $this->assertEquals($expectedTimetable, $timetable->getTimetable());
    }

    /**
     * Dataprovider for testTaskHasBeenScheduledOnce
     */
    public function taskHasBeenScheduledOnceTestCases()
    {
        $timetable = self::getTestTimetable();

        return array(
            array(true, 'CoreAdminHome.purgeOutdatedArchives', $timetable),
            array(true, 'PrivacyManager.deleteReportData_1', $timetable),
            array(false, 'ScheduledReports.weeklySchedule"', $timetable)
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider taskHasBeenScheduledOnceTestCases
     */
    public function testTaskHasBeenScheduledOnce($expectedDecision, $taskName, $timetable)
    {
        $timetableObj = new ScheduledTaskTimetable();
        $timetableObj->setTimetable($timetable);
        $this->assertEquals($expectedDecision, $timetableObj->taskHasBeenScheduledOnce($taskName));
    }

    /**
     * Dataprovider for testGetScheduledTimeForMethod
     */
    public function getScheduledTimeForMethodTestCases()
    {
        $timetable = serialize(self::getTestTimetable());

        return array(
            array(1355529607, 'CoreAdminHome', 'purgeOutdatedArchives', null, $timetable),
            array(1322229607, 'PrivacyManager', 'deleteReportData', 1, $timetable),
            array(false, 'ScheduledReports', 'weeklySchedule', null, $timetable)
        );
    }

    /**
     * @group Core
     * @dataProvider getScheduledTimeForMethodTestCases
     */
    public function testGetScheduledTimeForMethod($expectedTime, $className, $methodName, $methodParameter, $timetable)
    {
        self::stubPiwikOption($timetable);

        $this->assertEquals($expectedTime, TaskScheduler::getScheduledTimeForMethod($className, $methodName, $methodParameter));

        self::resetPiwikOption();
    }

    /**
     * Dataprovider for testTaskShouldBeExecuted
     */
    public function taskShouldBeExecutedTestCases()
    {
        $timetable = self::getTestTimetable();

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
     * @group Core
     * 
     * @dataProvider taskShouldBeExecutedTestCases
     */
    public function testTaskShouldBeExecuted($expectedDecision, $taskName, $timetable)
    {
        self::stubPiwikOption(serialize($timetable));

        $timetable = new ScheduledTaskTimetable();
        $this->assertEquals($expectedDecision, $timetable->shouldExecuteTask($taskName));
    }

    /**
     * Dataprovider for testExecuteTask
     */
    public function executeTaskTestCases()
    {
        return array(
            array('scheduledTaskOne', null),
            array('scheduledTaskTwo', 'parameterValue'),
            array('scheduledTaskTwo', 1),
        );
    }

    /**
     * @group Core
     * 
     * @dataProvider executeTaskTestCases
     */
    public function testExecuteTask($methodName, $parameterValue)
    {
        // assert the scheduled method is executed once with the correct parameter
        $mock = $this->getMock('TaskSchedulerTest', array($methodName));
        $mock->expects($this->once())->method($methodName)->with($this->equalTo($parameterValue));

        $executeTask = new ReflectionMethod('\Piwik\TaskScheduler', 'executeTask');
        $executeTask->setAccessible(true);

        $this->assertNotEmpty($executeTask->invoke(
            new TaskScheduler(),
            new ScheduledTask ($mock, $methodName, $parameterValue, \Piwik\ScheduledTime::factory('daily'))
        ));
    }

    /**
     * @group Core
     *
     * Dataprovider for testRunTasks
     */
    public function testRunTasksTestCases()
    {
        $systemTime = time();

        $dailySchedule = $this->getMock('\Piwik\ScheduledTime\Daily', array('getTime'));
        $dailySchedule->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($systemTime));

        $scheduledTaskOne = new ScheduledTask ($this, 'scheduledTaskOne', null, $dailySchedule);
        $scheduledTaskTwo = new ScheduledTask ($this, 'scheduledTaskTwo', 1, $dailySchedule);
        $scheduledTaskThree = new ScheduledTask ($this, 'scheduledTaskThree', null, $dailySchedule);

        $caseOneExpectedTable = array(
            'TaskSchedulerTest.scheduledTaskOne'   => $scheduledTaskOne->getRescheduledTime(),
            'TaskSchedulerTest.scheduledTaskTwo_1' => $systemTime + 60000,
            'TaskSchedulerTest.scheduledTaskThree' => $scheduledTaskThree->getRescheduledTime(),
        );

        $caseTwoTimetableBeforeExecution = $caseOneExpectedTable;
        $caseTwoTimetableBeforeExecution['TaskSchedulerTest.scheduledTaskThree'] = $systemTime; // simulate elapsed time between case 1 and 2

        return array(

            // case 1) contains :
            // - scheduledTaskOne: already scheduled before, should be executed and rescheduled
            // - scheduledTaskTwo: already scheduled before, should not be executed and therefore not rescheduled
            // - scheduledTaskThree: not already scheduled before, should be scheduled but not executed
            array(
                $caseOneExpectedTable,

                // methods that should be executed
                array(
                    'TaskSchedulerTest.scheduledTaskOne'
                ),

                // timetable before task execution
                array(
                    'TaskSchedulerTest.scheduledTaskOne'   => $systemTime,
                    'TaskSchedulerTest.scheduledTaskTwo_1' => $systemTime + 60000,
                ),
                // configured tasks
                array(
                    $scheduledTaskOne,
                    $scheduledTaskTwo,
                    $scheduledTaskThree,
                )
            ),

            // case 2) follows case 1) with :
            // - scheduledTaskOne: already scheduled before, should not be executed and therefore not rescheduled
            // - scheduledTaskTwo: not configured for execution anymore, should be removed from the timetable
            // - scheduledTaskThree: already scheduled before, should be executed and rescheduled
            array(
                // expected timetable
                array(
                    'TaskSchedulerTest.scheduledTaskOne'   => $scheduledTaskOne->getRescheduledTime(),
                    'TaskSchedulerTest.scheduledTaskThree' => $scheduledTaskThree->getRescheduledTime()
                ),

                // methods that should be executed
                array(
                    'TaskSchedulerTest.scheduledTaskThree'
                ),

                // timetable before task execution
                $caseTwoTimetableBeforeExecution,

                // configured tasks
                array(
                    $scheduledTaskOne,
//					$scheduledTaskTwo, Not configured anymore (ie. not returned after TaskScheduler::GET_TASKS_EVENT is issued)
                    $scheduledTaskThree,
                )
            ),
        );
    }

    public function scheduledTaskOne()
    {
    } // nothing to do
    public function scheduledTaskTwo($param)
    {
    } // nothing to do
    public function scheduledTaskThree()
    {
    } // nothing to do

    /**
     * @group Core
     * 
     * @dataProvider testRunTasksTestCases
     */
    public function testRunTasks($expectedTimetable, $expectedExecutedTasks, $timetableBeforeTaskExecution, $configuredTasks)
    {
        // temporarily unload plugins
        $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPlugins();
        $plugins = array_map(function ($p) { return $p->getPluginName(); }, $plugins);

        \Piwik\Plugin\Manager::getInstance()->unloadPlugins();
        
        // make sure the get tasks event returns our configured tasks
        \Piwik\Piwik::addAction(TaskScheduler::GET_TASKS_EVENT, function(&$tasks) use($configuredTasks) {
            $tasks = $configuredTasks;
        });

        // stub the piwik option object to control the returned option value
        self::stubPiwikOption(serialize($timetableBeforeTaskExecution));
        TaskScheduler::unsetInstance();

        // execute tasks
        $executionResults = TaskScheduler::runTasks();

        // assert methods are executed
        $executedTasks = array();
        foreach ($executionResults as $executionResult) {
            $executedTasks[] = $executionResult['task'];
            $this->assertNotEmpty($executionResult['output']);
        }
        $this->assertEquals($expectedExecutedTasks, $executedTasks);

        // assert the timetable is correctly updated
        $timetable = new ScheduledTaskTimetable();
        $this->assertEquals($expectedTimetable, $timetable->getTimetable());

        // restore loaded plugins & piwik options
        EventDispatcher::getInstance()->clearObservers(TaskScheduler::GET_TASKS_EVENT);
        \Piwik\Plugin\Manager::getInstance()->loadPlugins($plugins);
        self::resetPiwikOption();
    }

    private static function stubPiwikOption($timetable)
    {
        self::getReflectedPiwikOptionInstance()->setValue(new MockPiwikOption($timetable));
    }

    private static function resetPiwikOption()
    {
        self::getReflectedPiwikOptionInstance()->setValue(null);
    }

    private static function getReflectedPiwikOptionInstance()
    {
        $piwikOptionInstance = new ReflectionProperty('\Piwik\Option', 'instance');
        $piwikOptionInstance->setAccessible(true);
        return $piwikOptionInstance;
    }
}
