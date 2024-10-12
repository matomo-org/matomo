<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreAdminHome\tests\Fixtures\RunScheduledTasksProcessSignal;

use Piwik\Option;
use RuntimeException;

class StepControl
{
    public const OPTION_PREFIX = 'RunScheduledTasksProcessSignal.';

    private const OPTION_SCHEDULED_TASKS_BLOCKED = self::OPTION_PREFIX . 'ScheduledTasksBlocked';

    /**
     * Block proceeding from the "ScheduledTasks.execute" event.
     */
    public function blockScheduledTasks(): void
    {
        Option::set(self::OPTION_SCHEDULED_TASKS_BLOCKED, true);
    }

    /**
     * DI hook intercepting the "ScheduledTasks.execute" event.
     */
    public function handleScheduledTasksExecute(): void
    {
        $continue = $this->waitForSuccess(static function (): bool {
            // force reading from database
            Option::clearCachedOption(self::OPTION_SCHEDULED_TASKS_BLOCKED);

            return false === Option::get(self::OPTION_SCHEDULED_TASKS_BLOCKED);
        });

        if (!$continue) {
            throw new RuntimeException('Waiting for ScheduledTask option took too long!');
        }
    }

    /**
     * Remove all internal blocks.
     */
    public function reset(): void
    {
        Option::deleteLike(self::OPTION_PREFIX . '%');
    }

    /**
     * Allow proceeding past the "ScheduledTasks.execute" event.
     */
    public function unblockScheduledTasks(): void
    {
        Option::delete(self::OPTION_SCHEDULED_TASKS_BLOCKED);
    }

    /**
     * Wait until a callable returns true or a timeout is reached.
     */
    public function waitForSuccess(callable $check, int $timeoutInSeconds = 10): bool
    {
        $start = time();

        do {
            $now = time();

            if ($check()) {
                return true;
            }

            // 250 millisecond sleep
            usleep(250 * 1000);
        } while ($timeoutInSeconds > $now - $start);

        return false;
    }
}
