<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Scheduler\Scheduler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunScheduledTasks extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('scheduled-tasks:run');
        $this->setAliases(array('core:run-scheduled-tasks'));
        $this->setDescription('Will run all scheduled tasks due to run at this time.');
        $this->addArgument('task', InputArgument::OPTIONAL, 'Optionally pass the name of a task to run (will run even if not scheduled to run now)');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'If set, it will execute all tasks even the ones not due to run at this time.');
    }

    /**
     * Execute command like: ./console core:run-scheduled-tasks
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->forceRunAllTasksIfRequested($input);

        FrontController::getInstance()->init();

        // TODO use dependency injection
        /** @var Scheduler $scheduler */
        $scheduler = StaticContainer::get('Piwik\Scheduler\Scheduler');

        $task = $input->getArgument('task');

        if ($task) {
            $this->runSingleTask($scheduler, $task, $output);
        } else {
            $scheduler->run();
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

    private function runSingleTask(Scheduler $scheduler, $task, OutputInterface $output)
    {
        try {
            $message = $scheduler->runTaskNow($task);
        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage() . PHP_EOL
                . 'Available tasks:' . PHP_EOL
                . implode(PHP_EOL, $scheduler->getTaskList());

            throw new \Exception($message);
        }

        $output->writeln($message);
    }
}
