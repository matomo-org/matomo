<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin\tests\Unit;

/**
 * @group ExamplePlugin
 * @group SimpleTest
 * @group Plugins
 */
class SimpleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // set up here if needed
    }
    
    public function tearDown()
    {
        // tear down here if needed
    }

    /**
     * All your actual test methods should start with the name "test"
     */
    public function testSimpleAddition()
    {
        $this->assertEquals(2, 1+1);
    }

}
