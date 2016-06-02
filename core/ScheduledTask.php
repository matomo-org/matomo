<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Scheduler\Task;

/**
 * Contains metadata referencing PHP code that should be executed at regular
 * intervals.
 *
 * See the {@link TaskScheduler} docs to learn more about scheduled tasks.
 *
 * @api
 *
 * @deprecated Use Piwik\Scheduler\Task instead
 * @see \Piwik\Scheduler\Task
 */
class ScheduledTask extends Task
{
}
