<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * core:plugin console command.
 */
class ManagePlugin extends ConsoleCommand
{
    private $operations = array();

    protected function configure()
    {
        $this->setName('core:plugin');
        $this->setDescription("Perform various actions regarding one or more plugins.");
        $this->addArgument("operation", InputArgument::REQUIRED, "Operation to apply (can be 'activate' or 'deactivate').");
        $this->addArgument("plugins", InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Plugin name(s) to activate.');
        $this->addOption('domain', null, InputOption::VALUE_REQUIRED, "The domain to activate the plugin for.");

        $this->operations['activate'] = 'activatePlugin';
        $this->operations['deactivate'] = 'deactivatePlugin';
    }

    /**
     * Execute command like: ./console cloudadmin:plugin activate CustomAlerts --piwik-domain=testcustomer.piwik.pro
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument("operation");
        $plugins = $input->getArgument('plugins');

        if (empty($this->operations[$operation])) {
            throw new \Exception("Invalid operation '$operation'.");
        }

        $fn = $this->operations[$operation];
        foreach ($plugins as $plugin) {
            call_user_func(array($this, $fn), $input, $output, $plugin);
        }
    }

    private function activatePlugin(InputInterface $input, OutputInterface $output, $plugin)
    {
        Manager::getInstance()->activatePlugin($plugin, $input, $output);

        $output->writeln("Activated plugin <info>$plugin</info>");
    }

    private function deactivatePlugin(InputInterface $input, OutputInterface $output, $plugin)
    {
        Manager::getInstance()->deactivatePlugin($plugin, $input, $output);

        $output->writeln("Deactivated plugin <info>$plugin</info>");
    }
}