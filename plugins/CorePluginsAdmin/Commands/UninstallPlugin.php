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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * plugin:deactivate console command.
 */
class UninstallPlugin extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:uninstall');
        $this->setDescription('Uninstall a plugin.');
        $this->addArgument('plugin', InputArgument::IS_ARRAY, 'The plugin name you want to uninstall. Multiple plugin names can be specified separated by a space.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginManager = Manager::getInstance();

        $plugins = $input->getArgument('plugin');

        foreach ($plugins as $plugin) {
            if ($pluginManager->isPluginLoaded($plugin)) {
                $output->writeln(sprintf('<comment>The plugin %s is still active.</comment>', $plugin));
                continue;
            }

            $pluginManager->uninstallPlugin($plugin);

            $output->writeln("Uninstalled plugin <info>$plugin</info>");
        }
    }
}
