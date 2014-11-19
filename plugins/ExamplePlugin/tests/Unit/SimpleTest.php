<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin\tests\Unit;

use Piwik\Tests\Framework\TestCase\UnitTestCase;

/**
 * @group ExamplePlugin
 * @group SimpleTest
 * @group Plugins
 */
class SimpleTest extends UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        // set up here if needed
    }
    
    public function tearDown()
    {
        // tear down here if needed
        parent::tearDown();
    }

    /**
     * All your actual test methods should start with the name "test"
     */
    public function testSimpleAddition()
    {
        $this->assertEquals(2, 1+1);
    }

}
