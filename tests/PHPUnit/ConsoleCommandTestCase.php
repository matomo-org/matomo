<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests;

use Piwik\Config;
use Piwik\Console;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * Base class for test cases that test Piwik console commands. Derives from IntegrationTestCase
 * so the entire Piwik environment is set up.
 *
 * This will create an ApplicationTester instance (provided by Symfony) which should be used to
 * test commands like this:
 *
 *     public function testThisAndThat()
 *     {
 *         $result = $this->applicationTester->run(array(
 *             'command' => 'my-command',
 *             'arg1' => 'value1',
 *             'arg2' => 'value2',
 *             '--option' => true,
 *             '--another-option' => 'value3'
 *         ));
 *         $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());
 *
 *         // other checks
 *     }
 */
class ConsoleCommandTestCase extends IntegrationTestCase
{
    protected $applicationTester = null;

    public function setUp()
    {
        parent::setUp();

        $application = new Console();
        $application->setAutoExit(false);

        $this->applicationTester = new ApplicationTester($application);

        Config::unsetInstance();
    }

    protected function getCommandDisplayOutputErrorMessage()
    {
        return "Command did not behave as expected. Command output: " . $this->applicationTester->getDisplay();
    }
}