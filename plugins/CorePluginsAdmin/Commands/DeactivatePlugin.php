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
 * plugin:deactivate console command.
 */
class DeactivatePlugin extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:deactivate');
        $this->setDescription('Deactivate a plugin.');
        $this->addOptionalArgument('plugin', 'The plugin name you want to deactivate. Multiple plugin names can be specified separated by a space.', null, true);
    }

    protected function doExecute(): int
    {
        $pluginManager = Manager::getInstance();

        $plugins = $this->getInput()->getArgument('plugin');

        foreach ($plugins as $plugin) {
            if (!$pluginManager->isPluginActivated($plugin)) {
                $this->getOutput()->writeln(sprintf('<comment>The plugin %s is already deactivated.</comment>', $plugin));
                continue;
            }

            $pluginManager->deactivatePlugin($plugin);

            $this->getOutput()->writeln("Deactivated plugin <info>$plugin</info>");
        }

        return self::SUCCESS;
    }
}
