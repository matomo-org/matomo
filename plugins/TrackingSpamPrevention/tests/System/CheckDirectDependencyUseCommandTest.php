<?php

/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\System;

use Piwik\Plugins\TestRunner\Commands\CheckDirectDependencyUse;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Version;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CheckDirectDependencyUseCommandTest extends SystemTestCase
{
    public function testCommand()
    {
        if (version_compare(Version::VERSION, '5.0.3', '<=') && !file_exists(PIWIK_INCLUDE_PATH . '/plugins/TestRunner/Commands/CheckDirectDependencyUse.php')) {
            $this->markTestSkipped('tests:check-direct-dependency-use is not available in this version');
        }

        $pluginName = 'TrackingSpamPrevention';

        $checkDirectDependencyUse = new CheckDirectDependencyUse();

        $console = new \Piwik\Console(self::$fixture->piwikEnvironment);
        $console->addCommands([$checkDirectDependencyUse]);
        $command = $console->find('tests:check-direct-dependency-use');
        $arguments = [
            'command'    => 'tests:check-direct-dependency-use',
            '--plugin' => $pluginName,
            '--grep-vendor',
        ];

        $inputObject = new ArrayInput($arguments);
        $command->run($inputObject, new NullOutput());

        $this->assertEquals([
            'Symfony\Component\Console' => [
                'TrackingSpamPrevention/tests/System/CheckDirectDependencyUseCommandTest.php'
            ],
            'Matomo\Network' => [
                'TrackingSpamPrevention/AllowListIpRange.php',
                'TrackingSpamPrevention/BlockListIpRange.php',
                'TrackingSpamPrevention/BlockedIpRanges/DigitalOcean.php',
                'TrackingSpamPrevention/BlockedIpRanges.php',
                'TrackingSpamPrevention/TrackingSpamPrevention.php',
                'TrackingSpamPrevention/tests/Integration/BlockedIpRanges/ProvidersTest.php',
            ],
            'DI' => [
                'TrackingSpamPrevention/TrackingSpamPrevention.php',
            ]
        ], $checkDirectDependencyUse->usesFoundList[$pluginName]);
    }
}
