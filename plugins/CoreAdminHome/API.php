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
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Piwik\Container\StaticContainer;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Scheduler\Scheduler;
use Piwik\Site;

/**
 * @method static \Piwik\Plugins\CoreAdminHome\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    public function __construct(Scheduler $scheduler, ArchiveInvalidator $invalidator)
    {
        $this->scheduler = $scheduler;
        $this->invalidator = $invalidator;
    }

    /**
     * Will run all scheduled tasks due to run at this time.
     *
     * @return array
     * @hideExceptForSuperUser
     */
    public function runScheduledTasks()
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->scheduler->run();
    }

    /**
     * Invalidates report data, forcing it to be recomputed during the next archiving run.
     *
     * Note: This is done automatically when tracking or importing visits in the past.
     *
     * @param string $idSites Comma separated list of site IDs to invalidate reports for.
     * @param string $dates Comma separated list of dates of periods to invalidate reports for.
     * @param string|bool $period The type of period to invalidate: either 'day', 'week', 'month', 'year', 'range'.
     *                            The command will automatically cascade up, invalidating reports for parent periods as
     *                            well. So invalidating a day will invalidate the week it's in, the month it's in and the
     *                            year it's in, since those periods will need to be recomputed too.
     * @param string|bool $segment Optional. The segment to invalidate reports for.
     * @param bool $cascadeDown If true, child periods will be invalidated as well. So if it is requested to invalidate
     *                          a month, then all the weeks and days within that month will also be invalidated. But only
     *                          if this parameter is set.
     * @throws Exception
     * @return array
     * @hideExceptForSuperUser
     */
    public function invalidateArchivedReports($idSites, $dates, $period = false, $segment = false, $cascadeDown = false)
    {
        $idSites = Site::getIdSitesFromIdSitesString($idSites);
        if (empty($idSites)) {
            throw new Exception("Specify a value for &idSites= as a comma separated list of website IDs, for which your token_auth has 'admin' permission");
        }

        Piwik::checkUserHasAdminAccess($idSites);

        if (!empty($segment)) {
            $segment = new Segment($segment, $idSites);
        } else {
            $segment = null;
        }

        list($dateObjects, $invalidDates) = $this->getDatesToInvalidateFromString($dates);

        $invalidationResult = $this->invalidator->markArchivesAsInvalidated($idSites, $dateObjects, $period, $segment, (bool)$cascadeDown);

        $output = $invalidationResult->makeOutputLogs();
        if ($invalidDates) {
            $output[] = 'Warning: some of the Dates to invalidate were invalid: ' .
                implode(", ", $invalidDates) . ". Piwik simply ignored those and proceeded with the others.";
        }

        Site::clearCache(); // TODO: is this needed? it shouldn't be needed...

        return $invalidationResult->makeOutputLogs();
    }

    /**
     * Initiates cron archiving via web request.
     *
     * @hideExceptForSuperUser
     */
    public function runCronArchiving()
    {
        Piwik::checkUserHasSuperUserAccess();

        // HTTP request: logs needs to be dumped in the HTTP response (on top of existing log destinations)
        /** @var \Monolog\Logger $logger */
        $logger = StaticContainer::get('Psr\Log\LoggerInterface');
        $handler = new StreamHandler('php://output', Logger::INFO);
        $handler->setFormatter(StaticContainer::get('Piwik\Plugins\Monolog\Formatter\LineMessageFormatter'));
        $logger->pushHandler($handler);

        $archiver = new CronArchive();
        $archiver->main();
    }

    /**
     * Ensure the specified dates are valid.
     * Store invalid date so we can log them
     * @param array $dates
     * @return Date[]
     */
    private function getDatesToInvalidateFromString($dates)
    {
        $toInvalidate = array();
        $invalidDates = array();

        $dates = explode(',', trim($dates));
        $dates = array_unique($dates);

        foreach ($dates as $theDate) {
            $theDate = trim($theDate);
            try {
                $date = Date::factory($theDate);
            } catch (\Exception $e) {
                $invalidDates[] = $theDate;
                continue;
            }

            if ($date->toString() == $theDate) {
                $toInvalidate[] = $date;
            } else {
                $invalidDates[] = $theDate;
            }
        }

        return array($toInvalidate, $invalidDates);
    }
}