<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Scheduler;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugin\Tasks;

/**
 * Loads scheduled tasks.
 */
class TaskLoader
{
    /**
     * @return Task[]
     */
    public function loadTasks()
    {
        $tasks = array();

        /** @var Tasks[] $pluginTasks */
        $pluginTasks = PluginManager::getInstance()->findComponents('Tasks', 'Piwik\Plugin\Tasks');

        foreach ($pluginTasks as $pluginTask) {
            $pluginTask = StaticContainer::get($pluginTask);
            $pluginTask->schedule();

            foreach ($pluginTask->getScheduledTasks() as $task) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }
}
