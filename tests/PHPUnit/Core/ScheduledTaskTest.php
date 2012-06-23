<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
class ScheduledTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     * @group ScheduledTask
     */
    public function testGetClassName()
    {
        $scheduledTask = new Piwik_ScheduledTask ( "className", null, null );
        $className = $scheduledTask->getClassName();
        $this->assertInternalType('string', $className);
        $this->assertNotEmpty($className);
    }
    
    /**
     * @group Core
     * @group ScheduledTask
     */
    public function testGetMethodName()
    {
        $scheduledTask = new Piwik_ScheduledTask ( null, "methodname", null );
        $methodName = $scheduledTask->getMethodName();
        $this->assertInternalType('string', $methodName);
        $this->assertNotEmpty($methodName);
    }
    
    /**
     * @group Core
     * @group ScheduledTask
     */
    public function testGetScheduledTime()
    {
        $scheduledTask = new Piwik_ScheduledTask ( null, null, new Piwik_ScheduledTime_Hourly() );
        $scheduledTime = $scheduledTask->getScheduledTime();
        $this->assertInstanceOf("Piwik_ScheduledTime_Hourly", $scheduledTime);
    }
}
