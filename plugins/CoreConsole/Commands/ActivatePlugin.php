<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\Manager;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CloudAdmin
 */
class ActivatePlugin extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('plugin:activate');
        $this->setDescription("Activate a plugin.");
        $this->addArgument("plugins", InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Plugin name(s) to activate.');
        $this->addOption('domain', null, InputOption::VALUE_REQUIRED, "The domain to activate the plugin for.");
    }

    /**
     * Execute command like: ./console cloudadmin:plugin activate CustomAlerts --piwik-domain=testcustomer.piwik.pro
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $plugins = $input->getArgument('plugins');
        foreach ($plugins as $plugin) {
            Manager::getInstance()->activatePlugin($plugin);

            $output->writeln("Activating plugin <info>$plugin</info>");
        }
    }
}