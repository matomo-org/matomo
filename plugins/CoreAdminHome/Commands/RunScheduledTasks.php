<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\FrontController;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreAdminHome\API;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunScheduledTasks extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:run-scheduled-tasks');
        $this->setDescription('Will run all scheduled tasks due to run at this time.');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'If set, it will execute all tasks even the ones not due to run at this time.');
    }

    /**
     * Execute command like: ./console core:run-scheduled-tasks
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->forceRunAllTasksIfRequested($input);

        FrontController::getInstance()->init();
        $scheduledTasksResults = API::getInstance()->runScheduledTasks();

        foreach ($scheduledTasksResults as $scheduledTasksResult) {
            $output->writeln(sprintf(
                '<comment>%s</comment> - %s',
                $scheduledTasksResult['task'],
                $scheduledTasksResult['output']
            ));
        }

        $this->writeSuccessMessage($output, array('Scheduled Tasks executed'));
    }

    private function forceRunAllTasksIfRequested(InputInterface $input)
    {
        $force = $input->getOption('force');

        if ($force && !defined('DEBUG_FORCE_SCHEDULED_TASKS')) {
            define('DEBUG_FORCE_SCHEDULED_TASKS', true);
        }
    }
}