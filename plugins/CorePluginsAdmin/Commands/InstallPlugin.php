<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CorePluginsAdmin\PluginInstaller;
use Piwik\Plugins\Marketplace\Marketplace;

/**
 * plugin:install console command.
 */
class InstallPlugin extends ConsoleCommand
{
    protected function configure(): void
    {
        $this->setName('plugin:install');
        $this->setDescription('Install a plugin.');
        $this->addOptionalArgument('plugin', 'The plugin name you want to install. Multiple plugin names can be specified separated by a space.', null, true);
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $pluginManager = Manager::getInstance();

        if (!Marketplace::isMarketplaceEnabled()) {
            $output->writeln(sprintf("<error>Marketplace is not enabled, can't install plugins.</error>"));
            return self::FAILURE;
        }

        $pluginNames = $input->getArgument('plugin');

        foreach ($pluginNames as $pluginName) {
            if ($pluginManager->isPluginInstalled($pluginName)) {
                $output->writeln(sprintf('<comment>The plugin %s is already installed.</comment>', $pluginName));
                continue;
            }

            try {
                $this->installPlugin($pluginName);
                $output->writeln(sprintf("Installed plugin <info>%s</info>", $pluginName));
            } catch (\Piwik\Plugins\CorePluginsAdmin\PluginInstallerException $e) {
                $output->writeln(sprintf("<error>Unable to install plugin %s. %s</error>", $pluginName, $e->getMessage()));
                continue;
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param string $pluginName
     */
    private function installPlugin(string $pluginName): void
    {
        $pluginInstaller = StaticContainer::get(PluginInstaller::class);
        $pluginInstaller->installOrUpdatePluginFromMarketplace($pluginName);

        $pluginManager = Manager::getInstance();
        $pluginManager->loadPlugin($pluginName);
        $pluginManager->installLoadedPlugins();
    }
}
