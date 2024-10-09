<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Scheduler\Scheduler;

class RunScheduledTasks extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('scheduled-tasks:run');
        $this->setAliases(array('core:run-scheduled-tasks'));
        $this->setDescription('Will run all scheduled tasks due to run at this time.');
        $this->addOptionalArgument('task', 'Optionally pass the name of a task to run (will run even if not scheduled to run now)');
        $this->addNoValueOption('force', null, 'If set, it will execute all tasks even the ones not due to run at this time.');
    }

    /**
     * Execute command like: ./console core:run-scheduled-tasks
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();

        $this->forceRunAllTasksIfRequested();

        FrontController::getInstance()->init();

        // TODO use dependency injection
        /** @var Scheduler $scheduler */
        $scheduler = StaticContainer::get('Piwik\Scheduler\Scheduler');

        $task = $input->getArgument('task');

        if ($task) {
            $this->runSingleTask($scheduler, $task);
        } else {
            $scheduler->run();
        }

        $this->writeSuccessMessage('Scheduled Tasks executed');

        return self::SUCCESS;
    }

    private function forceRunAllTasksIfRequested()
    {
        $force = $this->getInput()->getOption('force');

        if ($force && !defined('DEBUG_FORCE_SCHEDULED_TASKS')) {
            define('DEBUG_FORCE_SCHEDULED_TASKS', true);
        }
    }

    private function runSingleTask(Scheduler $scheduler, $task)
    {
        try {
            $message = $scheduler->runTaskNow($task);
        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage() . PHP_EOL
                . 'Available tasks:' . PHP_EOL
                . implode(PHP_EOL, $scheduler->getTaskList());

            throw new \Exception($message);
        }

        $this->getOutput()->writeln($message);
    }
}
