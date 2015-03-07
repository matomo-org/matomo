<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
class DeactivatePlugin extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:deactivate');
        $this->setDescription('Deactivate a plugin.');
        $this->addArgument('plugin', InputArgument::REQUIRED, 'The plugin name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginManager = Manager::getInstance();

        $plugin = $input->getArgument('plugin');

        if (!$pluginManager->isPluginActivated($plugin)) {
            $output->writeln('<comment>The plugin is already deactivated.</comment>');
            return;
        }

        $pluginManager->deactivatePlugin($plugin);

        $output->writeln("Deactivated plugin <info>$plugin</info>");
    }
}
