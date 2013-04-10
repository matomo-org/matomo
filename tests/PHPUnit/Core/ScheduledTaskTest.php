<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/PDFReports/PDFReports.php';

class ScheduledTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group ScheduledTask
     */
    public function testGetClassName()
    {
        $scheduledTask = new Piwik_ScheduledTask (new Piwik_PDFReports(), null, null, null);
        $this->assertEquals('Piwik_PDFReports', $scheduledTask->getClassName());
    }

    /**
     * Dataprovider for testGetTaskName
     */
    public function getTaskNameTestCases()
    {
        return array(
            array('Piwik_CoreAdminHome.purgeOutdatedArchives', 'Piwik_CoreAdminHome', 'purgeOutdatedArchives', null),
            array('Piwik_CoreAdminHome.purgeOutdatedArchives_previous30', 'Piwik_CoreAdminHome', 'purgeOutdatedArchives', 'previous30'),
            array('Piwik_PDFReports.weeklySchedule', 'Piwik_PDFReports', 'weeklySchedule', null),
            array('Piwik_PDFReports.weeklySchedule_1', 'Piwik_PDFReports', 'weeklySchedule', 1),
        );
    }

    /**
     * @group Core
     * @group ScheduledTask
     * @dataProvider getTaskNameTestCases
     */
    public function testGetTaskName($expectedTaskName, $className, $methodName, $methodParameter)
    {
        $this->assertEquals($expectedTaskName, Piwik_ScheduledTask::getTaskName($className, $methodName, $methodParameter));
    }

}
