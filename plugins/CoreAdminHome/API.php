<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Exception;
use Piwik\DataAccess\ArchiveInvalidator;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\TaskScheduler;

/**
 * @hideExceptForSuperUser
 * @method static \Piwik\Plugins\CoreAdminHome\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Will run all scheduled tasks due to run at this time.
     *
     * @return array
     */
    public function runScheduledTasks()
    {
        Piwik::checkUserHasSuperUserAccess();
        return TaskScheduler::runTasks();
    }

    /**
     * Return true if plugin is activated, false otherwise
     *
     * @param string $pluginName
     * @return bool
     */
    public function isPluginActivated($pluginName)
    {
        Piwik::checkUserHasSomeViewAccess();
        return \Piwik\Plugin\Manager::getInstance()->isPluginActivated($pluginName);
    }

    /**
     * When tracking data in the past (using Tracking API), this function
     * can be used to invalidate reports for the idSites and dates where new data
     * was added.
     * DEV: If you call this API, the UI should display the data correctly, but will process
     *      in real time, which could be very slow after large data imports.
     *      After calling this function via REST, you can manually force all data
     *      to be reprocessed by visiting the script as the Super User:
     *      http://example.net/piwik/misc/cron/archive.php?token_auth=$SUPER_USER_TOKEN_AUTH_HERE
     * REQUIREMENTS: On large piwik setups, you will need in PHP configuration: max_execution_time = 0
     *    We recommend to use an hourly schedule of the script.
     *    More information: http://piwik.org/setup-auto-archiving/
     *
     * @param string $idSites Comma separated list of idSite that have had data imported for the specified dates
     * @param string $dates Comma separated list of dates to invalidate for all these websites
     * @param string $period If specified (one of day, week, month, year, range) it will only delete archives for this period.
     *                      Note: because week, month, year, range reports aggregate day reports then you need to specifically invalidate day reports to see
     *                      other periods reports processed..
     * @throws Exception
     * @return array
     */
    public function invalidateArchivedReports($idSites, $dates, $period = false)
    {
        $idSites = Site::getIdSitesFromIdSitesString($idSites);

        if (empty($idSites)) {
            throw new Exception("Specify a value for &idSites= as a comma separated list of website IDs, for which your token_auth has 'admin' permission");
        }

        Piwik::checkUserHasAdminAccess($idSites);

        $invalidator = new ArchiveInvalidator();
        $output = $invalidator->markArchivesAsInvalidated($idSites, $dates, $period);

        Site::clearCache();

        return $output;
    }


}
