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

class CheckDirectDependencyUseCommandTest extends SystemTestCase
{
    public function testCommand()
    {
        $pluginName = 'TestRunner';
        $console = new \Piwik\Console(self::$fixture->piwikEnvironment);
        $checkDirectDependencyUse = new CheckDirectDependencyUse();
        $console->addCommands([$checkDirectDependencyUse]);
        $command = $console->find('localdev:check-direct-dependency-use');
        $arguments = array(
            'command'    => 'localdev:check-direct-dependency-usee',
            '--plugin' => $pluginName
        );
        $inputObject = new ArrayInput($arguments);
        $command->run($inputObject, new NullOutput());

        $this->assertEquals([], $checkDirectDependencyUse->usesFoundList[$pluginName]);
    }
}