<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Marketplace\Plugins;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Container\StaticContainer;

/**
 * plugin:install console command.
 */
class InstallPlugin extends ConsoleCommand
{
    protected function configure()
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

        $pluginNames = $input->getArgument('plugin');

        foreach ($pluginNames as $pluginName) {
            if ($pluginManager->isPluginInstalled($pluginName)) {
                $output->writeln(sprintf('<comment>The plugin %s is already installed.</comment>', $pluginName));
                continue;
            }

            if (!$pluginManager->isPluginInFilesystem($pluginName)) {
                if (!Marketplace::isMarketplaceEnabled()) {
                    $output->writeln(sprintf("<error>Marketplace is not enabled, can't install plugins.</error>"));
                    return self::FAILURE;
                }
                try {
                    $this->fetchPluginJson($pluginName, $output);
                } catch (\Piwik\Plugins\Marketplace\Api\Exception $e) {
                    $output->writeln(sprintf("<error>Requested plugin does not exist.</error>"));
                    continue;
                }
            }

            $plugin = $pluginManager->loadPlugin($pluginName);

            if ($plugin->hasMissingDependencies()) {
                $output->writeln(sprintf('<error>The plugin %s is not compatible with the current Matomo version.</error>', $pluginName));
                continue;
            }

            $pluginManager->installLoadedPlugins();
            $output->writeln("Installed plugin <info>$pluginName</info>");
        }

        return self::SUCCESS;
    }

    private function fetchPluginJson($pluginName, $output): void
    {
        $marketplacePlugins = StaticContainer::get(Plugins::class);
        $pluginInfo = $marketplacePlugins->getPluginInfo($pluginName);
        $pluginJson = json_encode((array)$pluginInfo, JSON_PRETTY_PRINT);
        $pluginDir = PIWIK_INCLUDE_PATH . "/plugins/$pluginName";
        $pluginJsonPath = $pluginDir . "/plugin.json";

        if (!is_dir($pluginDir)) {
            mkdir($pluginDir, 0755, true);
        }

        file_put_contents($pluginJsonPath, $pluginJson);
    }
}
