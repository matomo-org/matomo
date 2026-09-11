<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\Unit\Commands;

use Piwik\Plugins\CorePluginsAdmin\Commands\InstallPlugin;
use Piwik\Plugins\CorePluginsAdmin\PluginInstallerException;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group CorePluginsAdmin
 * @group InstallPluginTest
 */
class InstallPluginTest extends UnitTestCase
{
    public function testExecuteReturnsFailureWhenMarketplaceInstallThrows()
    {
        $command = new class extends InstallPlugin {
            protected function isMarketplaceEnabled(): bool
            {
                return true;
            }

            protected function installPlugin(string $pluginName): void
            {
                throw new PluginInstallerException('There was an error reading the response from the Marketplace. Please try again later.');
            }
        };

        $application = new Application();
        $application->add($command);

        $tester = new CommandTester($command);
        $status = $tester->execute([
            'command' => $command->getName(),
            'plugin' => ['Bandwidth'],
        ]);

        $this->assertSame(InstallPlugin::FAILURE, $status);
        $this->assertStringContainsString('Unable to install or update plugin Bandwidth', $tester->getDisplay());
        $this->assertStringContainsString('There was an error reading the response from the Marketplace', $tester->getDisplay());
    }

    public function testExecuteReturnsFailureIfAnyPluginFailsWhenInstallingSeveral()
    {
        $command = new class extends InstallPlugin {
            protected function isMarketplaceEnabled(): bool
            {
                return true;
            }

            protected function installPlugin(string $pluginName): void
            {
                if ($pluginName === 'Broken') {
                    throw new PluginInstallerException('There was an error reading the response from the Marketplace. Please try again later.');
                }
            }
        };

        $application = new Application();
        $application->add($command);

        $tester = new CommandTester($command);
        $status = $tester->execute([
            'command' => $command->getName(),
            'plugin' => ['OkPlugin', 'Broken'],
        ]);

        $this->assertSame(InstallPlugin::FAILURE, $status);
        $this->assertStringContainsString('Installed or updated plugin OkPlugin', $tester->getDisplay());
        $this->assertStringContainsString('Unable to install or update plugin Broken', $tester->getDisplay());
    }
}
