<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreAdminHome\tests\Fixtures\CoreArchiverProcessSignal;

use Piwik\Option;
use RuntimeException;

class StepControl
{
    public const OPTION_PREFIX = 'CoreArchiverProcessSignal.';

    private const OPTION_ARCHIVE_REPORTS_BLOCKED = self::OPTION_PREFIX . 'ArchiveReportsBlocked';
    private const OPTION_CRON_ARCHIVE_BLOCKED = self::OPTION_PREFIX . 'CronArchiveBlocked';
    private const OPTION_SCHEDULED_TASKS_BLOCKED = self::OPTION_PREFIX . 'ScheduledTasksBlocked';
    private const OPTION_SCHEDULED_TASKS_EXECUTE = self::OPTION_PREFIX . 'ScheduledTasksExecute';

    /**
     * Block proceeding from the "API.CoreAdminHome.archiveReports" event.
     *
     * @param array{segment: string, period: string, date: string} $blockSpec
     */
    public function blockAPIArchiveReports(array $blockSpec): void
    {
        Option::set(self::OPTION_ARCHIVE_REPORTS_BLOCKED, json_encode($blockSpec));
    }

    /**
     * Block proceeding from the "ScheduledTasks.execute" event.
     */
    public function blockScheduledTasks(): void
    {
        Option::set(self::OPTION_SCHEDULED_TASKS_BLOCKED, true);
    }

    /**
     * Block proceeding from the "CronArchive.init.finish" event.
     */
    public function blockCronArchiveStart(): void
    {
        Option::set(self::OPTION_CRON_ARCHIVE_BLOCKED, true);
    }

    /**
     * Force scheduled tasks to execute.
     */
    public function executeScheduledTasks(): void
    {
        Option::set(self::OPTION_SCHEDULED_TASKS_EXECUTE, true);
    }

    /**
     * DI hook intercepting the "API.CoreAdminHome.archiveReports" event.
     */
    public function handleAPIArchiveReports($parameters): void
    {
        $continue = $this->waitForSuccess(static function () use ($parameters): bool {
            // force reading from database
            Option::clearCachedOption(self::OPTION_ARCHIVE_REPORTS_BLOCKED);

            $option = Option::get(self::OPTION_ARCHIVE_REPORTS_BLOCKED) ?: '';
            $block = json_decode($option, true);

            if (!is_array($block)) {
                return true;
            }

            return (
                $block['segment'] !== urldecode($parameters['segment'] ?: '')
                || $block['period'] !== $parameters['period']
                || $block['date'] !== $parameters['date']
            );
        });

        if (!$continue) {
            throw new RuntimeException('Waiting for ArchiveReports option took too long!');
        }
    }

    /**
     * DI hook intercepting the "CronArchive.init.finish" event.
     */
    public function handleCronArchiveStart(): void
    {
        $continue = $this->waitForSuccess(static function (): bool {
            // force reading from database
            Option::clearCachedOption(self::OPTION_CRON_ARCHIVE_BLOCKED);

            return false === Option::get(self::OPTION_CRON_ARCHIVE_BLOCKED);
        });

        if (!$continue) {
            throw new RuntimeException('Waiting for CronArchive option took too long!');
        }
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
     * DI hook intercepting the "ScheduledTasks.shouldExecuteTask" event.
     */
    public function handleScheduledTasksShouldExecute(bool &$shouldExecuteTask): void
    {
        if (Option::get(self::OPTION_SCHEDULED_TASKS_EXECUTE)) {
            $shouldExecuteTask = true;
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
     * Allow proceeding past the "API.CoreAdminHome.archiveReports" event.
     */
    public function unblockAPIArchiveReports(): void
    {
        Option::delete(self::OPTION_ARCHIVE_REPORTS_BLOCKED);
    }

    /**
     * Allow proceeding past the "CronArchive.init.start" event.
     */
    public function unblockCronArchiveStart(): void
    {
        Option::delete(self::OPTION_CRON_ARCHIVE_BLOCKED);
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
