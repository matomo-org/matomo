<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TestRunner\tests\System;

use Piwik\Plugins\TestRunner\Commands\CheckDirectDependencyUse;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @group TestRunner
 * @group TestRunner
 * @group Plugins
 */
class CheckDirectDependencyUseCommandTest extends SystemTestCase
{
    /**
     * @dataProvider getTestDataForDependencyCheck()
     */
    public function testCommand($pluginName, $expectedResult)
    {
        $console = new \Piwik\Console(self::$fixture->piwikEnvironment);
        $checkDirectDependencyUse = new CheckDirectDependencyUse();
        $console->addCommands([$checkDirectDependencyUse]);
        $command = $console->find('testRunner:check-direct-dependency-use');
        $arguments = array(
            'command' => 'testRunner:check-direct-dependency-use',
            '--plugin' => $pluginName
        );
        $inputObject = new ArrayInput($arguments);
        $command->run($inputObject, new NullOutput());

        $this->assertEquals($expectedResult, $checkDirectDependencyUse->usesFoundList[$pluginName]);
    }

    public function getTestDataForDependencyCheck()
    {
        return [
            ['TestRunner', []],
            ['Provider', ['Matomo\Network\\' => ['Provider/Columns/Provider.php']]],
        ];
    }
}
