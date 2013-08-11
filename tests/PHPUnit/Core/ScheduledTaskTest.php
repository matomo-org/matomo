<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Plugins\PDFReports\PDFReports;
use Piwik\ScheduledTask;

require_once PIWIK_INCLUDE_PATH . '/plugins/PDFReports/PDFReports.php';

class ScheduledTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group ScheduledTask
     */
    public function testGetClassName()
    {
        $scheduledTask = new ScheduledTask (new PDFReports(), null, null, null);
        $this->assertEquals('PDFReports', $scheduledTask->getClassName());
    }

    /**
     * Dataprovider for testGetTaskName
     */
    public function getTaskNameTestCases()
    {
        return array(
            array('CoreAdminHome.purgeOutdatedArchives', 'CoreAdminHome', 'purgeOutdatedArchives', null),
            array('CoreAdminHome.purgeOutdatedArchives_previous30', 'CoreAdminHome', 'purgeOutdatedArchives', 'previous30'),
            array('PDFReports.weeklySchedule', 'PDFReports', 'weeklySchedule', null),
            array('PDFReports.weeklySchedule_1', 'PDFReports', 'weeklySchedule', 1),
        );
    }

    /**
     * @group Core
     * @group ScheduledTask
     * @dataProvider getTaskNameTestCases
     */
    public function testGetTaskName($expectedTaskName, $className, $methodName, $methodParameter)
    {
        $this->assertEquals($expectedTaskName, ScheduledTask::getTaskName($className, $methodName, $methodParameter));
    }

}
