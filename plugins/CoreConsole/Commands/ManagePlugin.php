<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CorePluginsAdmin\Commands\ActivatePlugin;
use Piwik\Plugins\CorePluginsAdmin\Commands\DeactivatePlugin;
use Piwik\Plugins\CorePluginsAdmin\Commands\ListPlugins;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * core:plugin console command.
 *
 * @deprecated This command has been replaced with `plugin:*` commands.
 */
class ManagePlugin extends ConsoleCommand
{
    private $operations = array();

    protected function configure()
    {
        $this->setName('core:plugin');
        $this->setDescription("Perform various actions regarding one or more plugins.");
        $this->addArgument("operation", InputArgument::REQUIRED, "Operation to apply (can be 'activate' or 'deactivate' or 'list').");
        $this->addArgument("plugins", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Plugin name(s) to activate.');
        $this->addOption('domain', null, InputOption::VALUE_REQUIRED, "The domain to activate the plugin for.");

        $this->operations['activate'] = 'activatePlugin';
        $this->operations['deactivate'] = 'deactivatePlugin';
        $this->operations['list'] = 'listPlugins';
    }

    /**
     * Execute command like: ./console core:plugin activate CustomAlerts --piwik-domain=testcustomer.piwik.pro
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument("operation");
        $plugins = $input->getArgument('plugins');

        if (empty($this->operations[$operation])) {
            throw new \Exception("Invalid operation '$operation'.");
        }

        $fn = $this->operations[$operation];


        if($fn == 'listPlugins') {
            call_user_func(array($this, $fn), $input, $output);
        } else {
            $this->applyOperationToEachPlugin($input, $output, $plugins, $fn);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $plugins
     * @param $fn
     */
    protected function applyOperationToEachPlugin(InputInterface $input, OutputInterface $output, $plugins, $fn)
    {
        foreach ($plugins as $plugin) {
            call_user_func(array($this, $fn), $input, $output, $plugin);
        }
    }

    private function activatePlugin(InputInterface $input, OutputInterface $output, $plugin)
    {
        $output->writeln('<comment>Warning: the command core:plugin is deprecated, use plugin:activate instead.</comment>');

        $command = new ActivatePlugin();
        $input = new ArrayInput(array(
            'plugin' => $plugin,
        ));
        return $command->run($input, $output);
    }

    private function deactivatePlugin(InputInterface $input, OutputInterface $output, $plugin)
    {
        $output->writeln('<comment>Warning: the command core:plugin is deprecated, use plugin:deactivate instead.</comment>');

        $command = new DeactivatePlugin();
        $input = new ArrayInput(array(
            'plugin' => $plugin,
        ));
        return $command->run($input, $output);
    }

    private function listPlugins(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Warning: the command core:plugin is deprecated, use plugin:list instead.</comment>');

        $command = new ListPlugins();
        $input = new ArrayInput(array());
        return $command->run($input, $output);
    }
}