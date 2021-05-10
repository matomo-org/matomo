<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\CliMulti;
use Piwik\Common;
use Piwik\Option;
use Piwik\Tracker;

class ScheduledTasksRunner
{

    public function shouldRun(Tracker $tracker)
    {
        if (Common::isPhpCliMode()) {
            // don't run scheduled tasks in CLI mode from Tracker, this is the case
            // where we bulk load logs & don't want to lose time with tasks
            return false;
        }

        return $tracker->shouldRecordStatistics();
    }

    /**
     * Tracker requests will automatically trigger the Scheduled tasks.
     * This is useful for users who don't setup the cron,
     * but still want daily/weekly/monthly PDF reports emailed automatically.
     *
     * This is similar to calling the API CoreAdminHome.runScheduledTasks
     */
    public function runScheduledTasks()
    {
        $now = time();

        // Currently, there are no hourly tasks. When there are some,
        // this could be too aggressive minimum interval (some hours would be skipped in case of low traffic)
        $minimumInterval = TrackerConfig::getConfigValue('scheduled_tasks_min_interval');

        // If the user disabled browser archiving, they have already setup a cron
        // To avoid parallel requests triggering the Scheduled Tasks,
        // Get last time tasks started executing
        $cache = Cache::getCacheGeneral();

        if ($minimumInterval <= 0
            || empty($cache['isBrowserTriggerEnabled'])
        ) {
            Common::printDebug("-> Scheduled tasks not running in Tracker: Browser archiving is disabled.");
            return;
        }

        $nextRunTime = $cache['lastTrackerCronRun'] + $minimumInterval;

        if ((defined('DEBUG_FORCE_SCHEDULED_TASKS') && DEBUG_FORCE_SCHEDULED_TASKS)
            || $cache['lastTrackerCronRun'] === false
            || $nextRunTime < $now
        ) {
            $cache['lastTrackerCronRun'] = $now;
            Cache::setCacheGeneral($cache);

            Option::set('lastTrackerCronRun', $cache['lastTrackerCronRun']);
            Common::printDebug('-> Scheduled Tasks: Starting...');

            $invokeScheduledTasksUrl = "?module=API&format=csv&convertToUnicode=0&method=CoreAdminHome.runScheduledTasks&trigger=archivephp";

            $cliMulti = new CliMulti();
            $cliMulti->runAsSuperUser();
            $responses = $cliMulti->request(array($invokeScheduledTasksUrl));
            $resultTasks = reset($responses);

            Common::printDebug($resultTasks);

            Common::printDebug('Finished Scheduled Tasks.');
        } else {
            Common::printDebug("-> Scheduled tasks not triggered.");
        }

        Common::printDebug("Next run will be from: " . date('Y-m-d H:i:s', $nextRunTime) . ' UTC');
    }
}
