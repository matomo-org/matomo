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
 * plugin:activate console command.
 */
class ActivatePlugin extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:activate');
        $this->setDescription('Activate a plugin.');
        $this->addArgument('plugin', InputArgument::REQUIRED, 'The plugin name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginManager = Manager::getInstance();

        $plugin = $input->getArgument('plugin');

        if ($pluginManager->isPluginActivated($plugin)) {
            $output->writeln('<comment>The plugin is already activated.</comment>');
            return;
        }

        $pluginManager->activatePlugin($plugin);

        $output->writeln("Activated plugin <info>$plugin</info>");
    }
}
