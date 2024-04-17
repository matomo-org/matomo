<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager;

/**
 * plugin:deactivate console command.
 */
class UninstallPlugin extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:uninstall');
        $this->setDescription('Uninstall a plugin.');
        $this->addOptionalArgument('plugin', 'The plugin name you want to uninstall. Multiple plugin names can be specified separated by a space.', null, true);
    }

    protected function doExecute(): int
    {
        $pluginManager = Manager::getInstance();

        $plugins = $this->getInput()->getArgument('plugin');

        foreach ($plugins as $plugin) {
            if ($pluginManager->isPluginLoaded($plugin)) {
                $this->getOutput()->writeln(sprintf('<comment>The plugin %s is still active.</comment>', $plugin));
                continue;
            }

            $pluginManager->uninstallPlugin($plugin);

            $this->getOutput()->writeln("Uninstalled plugin <info>$plugin</info>");
        }

        return self::SUCCESS;
    }
}
