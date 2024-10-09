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
use Piwik\Plugins\Marketplace\Plugins;

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
                    $this->fetchPluginJson($pluginName);
                } catch (\Piwik\Plugins\Marketplace\Api\Exception $e) {
                    $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));
                    continue;
                    // Catch the unnamed core/Http.php(751) exception if no connection:
                    // curl_exec: Could not resolve host: plugins.matomo.org. Hostname requested was: plugins.matomo.org
                } catch (\Exception $e) {
                    $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));
                    continue;
                }

                if ($this->hasMissingDependencies($pluginName, $pluginManager)) {
                    $output->writeln(sprintf('<error>The plugin %s is not compatible with the current Matomo version.</error>', $pluginName));
                    continue;
                }
            }
            try {
                $this->installPlugin($pluginName);
                $output->writeln(sprintf("Installed plugin <info>%s</info>", $pluginName));
            } catch (\Piwik\Plugins\CorePluginsAdmin\PluginInstallerException $e) {
                $output->writeln(sprintf("<error>%s</error>", $e->getMessage()));
                continue;
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param string $pluginName
     */
    private function fetchPluginJson($pluginName): void
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

    /**
     * @param string $pluginName
     * @param Piwik\Plugin\Manager $pluginManager
     */
    private function hasMissingDependencies($pluginName, $pluginManager): bool
    {
        $plugin = $pluginManager->loadPlugin($pluginName);

        return $plugin->hasMissingDependencies();
    }

    /**
     * @param string $pluginName
     */
    private function installPlugin($pluginName): void
    {
        $marketplaceClient = StaticContainer::getContainer()->make('Piwik\Plugins\Marketplace\Api\Client');
        $pluginInstaller = new PluginInstaller($marketplaceClient);
        $pluginInstaller->installOrUpdatePluginFromMarketplace($pluginName);
    }
}
