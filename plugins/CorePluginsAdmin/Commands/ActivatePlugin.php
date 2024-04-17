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

/**
 * plugin:activate console command.
 */
class ActivatePlugin extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:activate');
        $this->setDescription('Activate a plugin.');
        $this->addOptionalArgument('plugin', 'The plugin name you want to activate. Multiple plugin names can be specified separated by a space.', null, true);
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $pluginManager = Manager::getInstance();

        $plugins = $input->getArgument('plugin');

        foreach ($plugins as $plugin) {
            if ($pluginManager->isPluginActivated($plugin)) {
                $output->writeln(sprintf('<comment>The plugin %s is already activated.</comment>', $plugin));
                continue;
            }

            if (!$pluginManager->isPluginInFilesystem($plugin)) {
                $output->writeln("<error>Cannot find plugin files for $plugin.</error>");
                continue;
            }

            if ($dependencies = $pluginManager->loadPlugin($plugin)->getMissingDependenciesAsString()) {
                $output->writeln("<error>$dependencies</error>");
                continue;
            }

            $pluginManager->activatePlugin($plugin);

            $output->writeln("Activated plugin <info>$plugin</info>");
        }

        return self::SUCCESS;
    }
}
