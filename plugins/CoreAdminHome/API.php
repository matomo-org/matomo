<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome;

use Exception;
use Monolog\Handler\StreamHandler;
use Piwik\Changes\UserChanges;
use Piwik\Log\Logger;
use Piwik\Access;
use Piwik\ArchiveProcessor\Rules;
use Piwik\ArchiveProcessor;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Scheduler\Scheduler;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Tracker\Failures;
use Piwik\Url;
use Piwik\Plugins\UsersManager\Model as UsersModel;

/**
 * Core admin API endpoints for scheduled tasks, archiving controls, and system maintenance.
 * Exposes operations for report invalidation, tracking failure maintenance, and opt-out helpers.
 *
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

    /**
     * @var Failures
     */
    private $trackingFailures;

    /**
     * @var OptOutManager
     */
    private $optOutManager;

    public function __construct(
        Scheduler $scheduler,
        ArchiveInvalidator $invalidator,
        Failures $trackingFailures,
        OptOutManager $optOutManager
    ) {
        $this->scheduler = $scheduler;
        $this->invalidator = $invalidator;
        $this->trackingFailures = $trackingFailures;
        $this->optOutManager = $optOutManager;
    }

    /**
     * Runs all scheduled tasks that are due at the time of the request.
     *
     * @return array<int, array{task: string, output: string}> Execution results for each task.
     * @hideExceptForSuperUser
     */
    public function runScheduledTasks()
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->scheduler->run();
    }

    /**
     * Updates archiving settings for browser-triggered archives and the "today" TTL.
     *
     * @param bool $enableBrowserTriggerArchiving Whether browser-triggered archiving is enabled.
     * @param int $todayArchiveTimeToLive Time-to-live in seconds for today's archives; must be greater than zero.
     * @return bool Returns true when settings were applied.
     * @throws Exception If the TTL is invalid or settings access is not enabled.
     * @internal
     */
    public function setArchiveSettings($enableBrowserTriggerArchiving, $todayArchiveTimeToLive)
    {
        Piwik::checkUserHasSuperUserAccess();

        if (!Controller::isGeneralSettingsAdminEnabled()) {
            throw new Exception('General settings admin is not enabled');
        }

        Rules::setBrowserTriggerArchiving((bool)$enableBrowserTriggerArchiving);
        Rules::setTodayArchiveTimeToLive($todayArchiveTimeToLive);

        return true;
    }

    /**
     * Stores the trusted hostnames list in the configuration.
     *
     * @param string|string[] $trustedHosts One hostname or a list of hostnames to trust.
     * @return bool Returns true when the request completes.
     * @internal
     */
    public function setTrustedHosts($trustedHosts)
    {
        Piwik::checkUserHasSuperUserAccess();

        if (!Controller::isGeneralSettingsAdminEnabled()) {
            throw new Exception('General settings admin is not enabled');
        }

        if (!empty($trustedHosts)) {
            Url::saveTrustedHostnameInConfig($trustedHosts);
            Config::getInstance()->forceSave();
        }

        return true;
    }

    /**
     * Enables or disables custom branding assets and publishes uploaded files when present.
     *
     * @param bool $useCustomLogo Whether custom branding should be enabled.
     * @param bool $hasCustomLogo Whether a temporary custom logo is available to publish.
     * @param bool $hasCustomFavicon Whether a temporary custom favicon is available to publish.
     * @return array{useCustomLogo: bool, customLogoPath?: string, customFaviconPath?: string} Flags and published asset paths.
     * @internal
     */
    public function setBrandingSettings($useCustomLogo, $hasCustomLogo, $hasCustomFavicon)
    {
        Piwik::checkUserHasSuperUserAccess();
        $customLogo = new CustomLogo();
        $response = [];

        if (!$useCustomLogo || ($useCustomLogo && !$hasCustomLogo && !$hasCustomFavicon)) {
            $customLogo->removeLogos();
            $customLogo->disable();

            $response['useCustomLogo'] = false;

            return $response;
        }

        $customLogo->enable();
        $response['useCustomLogo'] = true;
        if ($hasCustomLogo && $customLogo->hasTempLogo()) {
            $customLogo->publishUserLogo();
            $response['customLogoPath'] = $customLogo->getPathUserLogo();
        }
        if ($hasCustomFavicon && $customLogo->hasTempFavicon()) {
            $customLogo->publishUserFavicon();
            $response['customFaviconPath'] = $customLogo->getPathUserFavicon();
        }

        return $response;
    }

    /**
     * Invalidates report data, forcing it to be recomputed during the next archiving run.
     *
     * The command will automatically cascade up, invalidating reports for parent periods as
     * well. So invalidating a day will invalidate the week it's in, the month it's in and the
     * year it's in, since those periods will need to be recomputed too.
     *
     * Note: This is done automatically when tracking or importing visits in the past.
     *
     * @param int|string|int[] $idSites                     Website ID(s) to query.
     *                                                      - Single site ID (e.g. 1)
     *                                                      - Multiple site IDs (e.g. [1, 4, 5])
     *                                                      - Comma-separated list ("1,4,5") or "all"
     *
     * @param string|array  $dates                          Dates to process.
     *                                                      Non-range periods: 'YYYY-MM-DD' (or comma list), plus 'today'/'yesterday'.
     *                                                      Range period: a single range string like 'YYYY-MM-DD,YYYY-MM-DD' or 'lastN'/'previousN'.
     *
     * @param string|false  $period                         Period to use: 'day', 'week', 'month', 'year', or 'range'
     *
     * @param string|false  $segment                        (Optional) Custom segment to filter the report.
     *                                                      Example: "referrerName==twitter.com"
     *                                                      Supports AND (;) and OR (,) operators.
     *                                                      [See documentation:](https://developer.matomo.org/api-reference/reporting-api-segmentation)
     *
     * @param bool          $cascadeDown                    If true, child periods will be invalidated as well. So if it is requested to invalidate
     *                                                      a month, then all the weeks and days within that month will also be invalidated. But only
     *                                                      if this parameter is set.
     * @param bool          $_forceInvalidateNonexistent    If true, creates invalidation entries even when no archives exist.
     * @return string[] Log output describing what was invalidated.
     * @throws Exception If the site list is invalid or access is denied.
     * @hideExceptForSuperUser
     */
    public function invalidateArchivedReports(
        $idSites,
        $dates,
        $period = false,
        $segment = false,
        $cascadeDown = false,
        $_forceInvalidateNonexistent = false
    ) {
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

        /** Date[]|string[] $dates */
        [$dates, $invalidDates] = $this->getDatesToInvalidateFromString($dates, $period);

        $invalidationResult = $this->invalidator->markArchivesAsInvalidated($idSites, $dates, $period, $segment, (bool)$cascadeDown, (bool)$_forceInvalidateNonexistent);

        $output = $invalidationResult->makeOutputLogs();
        if ($invalidDates) {
            $output[] = 'Warning: some of the Dates to invalidate were invalid: \'' .
                implode("', '", $invalidDates) . "'. Matomo simply ignored those and proceeded with the others.";
        }

        return $output;
    }

    /**
     * Initiates cron archiving via web request.
     *
     * @return void
     * @hideExceptForSuperUser
     */
    public function runCronArchiving()
    {
        Piwik::checkUserHasSuperUserAccess();

        // HTTP request: logs needs to be dumped in the HTTP response (on top of existing log destinations)
        /** @var \Piwik\Log\Logger $logger */
        $logger = StaticContainer::get(LoggerInterface::class);
        $handler = new StreamHandler('php://output', Logger::INFO);
        $handler->setFormatter(StaticContainer::get('Piwik\Plugins\Monolog\Formatter\LineMessageFormatter'));
        $logger->pushHandler($handler);

        $archiver = new CronArchive();
        $archiver->main();
    }

    /**
     * Deletes all tracking failures this user has at least admin access to.
     * A super user will also delete tracking failures for sites that don't exist.
     *
     * @return void
     */
    public function deleteAllTrackingFailures()
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $this->trackingFailures->deleteAllTrackingFailures();
        } else {
            Piwik::checkUserHasSomeAdminAccess();
            $idSites = Access::getInstance()->getSitesIdWithAdminAccess();
            Piwik::checkUserHasAdminAccess($idSites);
            $this->trackingFailures->deleteTrackingFailures($idSites);
        }
    }

    /**
     * Deletes a specific tracking failure.
     *
     * @param int $idSite     The numeric ID of the website to query.
     * @param int $idFailure  Failure ID.
     * @return void
     */
    public function deleteTrackingFailure($idSite, $idFailure)
    {
        $idSite = (int) $idSite;
        Piwik::checkUserHasAdminAccess($idSite);

        $this->trackingFailures->deleteTrackingFailure($idSite, $idFailure);
    }

    /**
     * Get all tracking failures. A user retrieves only tracking failures for sites with at least admin access.
     * A super user will also retrieve failed requests for sites that don't exist.
     *
     * @return array<int, array<string, mixed>> Tracking failures with additional human-readable fields.
     */
    public function getTrackingFailures()
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $failures = $this->trackingFailures->getAllFailures();
        } else {
            Piwik::checkUserHasSomeAdminAccess();
            $idSites = Access::getInstance()->getSitesIdWithAdminAccess();
            Piwik::checkUserHasAdminAccess($idSites);

            $failures = $this->trackingFailures->getFailuresForSites($idSites);
        }

        return $failures;
    }

    /**
     * Runs a full archiving pass for a site and optional plugin/report.
     *
     * @param int                $idSite  The numeric ID of the website to query.
     *                                    Dates and periods parameters are interpreted in the website timezone.
     *
     * @param string             $period  The period to process, processes data for the period containing the specified date.
     *                                    Allowed values: "day", "week", "month", "year", "range".
     *
     * @param string|\Piwik\Date $date    The date or date range to process.
     *                                    'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                                    or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     *
     * @param string|false       $segment (Optional) Custom segment to filter the report.
     *                                    Example: "referrerName==twitter.com"
     *                                    Supports AND (;) and OR (,) operators.
     *                                    [See documentation:](https://developer.matomo.org/api-reference/reporting-api-segmentation)
     *
     * @param string|false       $plugin   Optional plugin name to archive.
     * @param string|false       $report   Optional report identifier to archive.
     * @return array<string, mixed> Archive preparation result; includes 'idarchives' and 'nb_visits' when available.
     * @throws \Piwik\Exception\UnexpectedWebsiteFoundException If the site ID is invalid.
     * @internal
     */
    public function archiveReports($idSite, $period, $date, $segment = false, $plugin = false, $report = false)
    {
        if (\Piwik\API\Request::getRootApiRequestMethod() === 'CoreAdminHome.archiveReports') {
            Piwik::checkUserHasSuperUserAccess();
        } else {
            Piwik::checkUserHasViewAccess($idSite);
        }

        // if cron archiving is running, we will invalidate in CronArchive, not here
        $isArchivePhpTriggered = SettingsServer::isArchivePhpTriggered();
        $invalidateBeforeArchiving = !$isArchivePhpTriggered;

        $period = Factory::build($period, $date);
        $site = new Site($idSite);
        $segmentObj = new Segment(
            $segment,
            [$idSite],
            $period->getDateTimeStart()->setTimezone($site->getTimezone()),
            $period->getDateTimeEnd()->setTimezone($site->getTimezone())
        );
        $parameters = new ArchiveProcessor\Parameters(
            $site,
            $period,
            $segmentObj
        );
        if ($report) {
            $parameters->setArchiveOnlyReport($report);
        }

        /**
         * Triggered before a full archiveReports run starts.
         *
         * Usage example:
         * Piwik::addAction('CoreAdminHome.archiveReports.start', function ($idSite, $period, $segment, $plugin, $report, $isArchivePhpTriggered) { ... });
         *
         * @internal
         */
        Piwik::postEvent('CoreAdminHome.archiveReports.start', [
            $idSite,
            $period,
            $segmentObj,
            (string) $plugin,
            $report,
            $isArchivePhpTriggered,
        ]);

        // TODO: need to test case when there are multiple plugin archives w/ only some data each. does purging remove some that we need?
        $archiveLoader = new ArchiveProcessor\Loader($parameters, $invalidateBeforeArchiving);

        $result = $archiveLoader->prepareArchive($plugin);
        if (!empty($result)) {
            $result = [
                'idarchives' => $result[0],
                'nb_visits' => $result[1],
            ];
        }

        $idArchives = isset($result['idarchives']) ? (array) $result['idarchives'] : [];
        $wasCached = $archiveLoader->didReuseArchive();

        /**
         * Triggered after a full archiveReports run completes.
         *
         * Usage example:
         * Piwik::addAction('CoreAdminHome.archiveReports.complete', function ($idSite, $period, $segment, $plugin, $report, $isArchivePhpTriggered, $idArchives, $wasCached) { ... });
         *
         * @internal
         */
        Piwik::postEvent('CoreAdminHome.archiveReports.complete', [
            $idSite,
            $period,
            $segmentObj,
            (string) $plugin,
            $report,
            $isArchivePhpTriggered,
            $idArchives,
            $wasCached,
        ]);

        return $result;
    }

    /**
     * Ensure the specified dates are valid.
     * Store invalid date so we can log them
     * @param array|string  $dates
     * @param string        $period
     *
     * @return array
     */
    private function getDatesToInvalidateFromString($dates, string $period): array
    {
        $toInvalidate = [];
        $invalidDates = [];

        if (!is_array($dates)) {
            if ($period !== 'range') {
                $dates = explode(',', trim($dates));
            } else {
                $dates = [trim($dates)];
            }
        }

        $dates = array_unique($dates);

        foreach ($dates as $theDate) {
            $theDate = trim($theDate);

            if ($period == 'range') {
                try {
                    $periodObj = Factory::build('range', $theDate);
                    $subPeriods = $periodObj->getSubperiods();
                } catch (\Exception $e) {
                    $invalidDates[] = $theDate;
                    continue;
                }
                if (count($subPeriods)) {
                    $toInvalidate[] = $periodObj->getRangeString();
                } else {
                    $invalidDates[] = $theDate;
                }
            } else {
                try {
                    $date = Date::factory($theDate);
                } catch (\Exception $e) {
                    $invalidDates[] = $theDate;
                    continue;
                }

                if ($date->toString() == $theDate || $theDate == 'today' || $theDate == 'yesterday') {
                    $toInvalidate[] = $date;
                } else {
                    $invalidDates[] = $theDate;
                }
            }
        }

        return [$toInvalidate, $invalidDates];
    }

    /**
     * Returns the JavaScript opt-out embed code with custom styling.
     *
     * @param string $backgroundColor
     * @param string $fontColor
     * @param string $fontSize
     * @param string $fontFamily
     * @param bool   $applyStyling
     * @param bool   $showIntro
     * @param string $matomoUrl
     * @param string $language
     *
     * @return string Generated embed code.
     *
     * @internal
     */
    public function getOptOutJSEmbedCode(
        string $backgroundColor,
        string $fontColor,
        string $fontSize,
        string $fontFamily,
        bool $applyStyling,
        bool $showIntro,
        string $matomoUrl,
        string $language
    ): string {

        return $this->optOutManager->getOptOutJSEmbedCode(
            $matomoUrl,
            $language,
            $backgroundColor,
            $fontColor,
            $fontSize,
            $fontFamily,
            $applyStyling,
            $showIntro
        );
    }

    /**
     * Returns the self-contained JavaScript opt-out embed code with custom styling.
     *
     * @param string $backgroundColor
     * @param string $fontColor
     * @param string $fontSize
     * @param string $fontFamily
     * @param bool   $applyStyling
     * @param bool   $showIntro
     *
     * @return string Generated embed code.
     *
     * @internal
     */
    public function getOptOutSelfContainedEmbedCode(
        string $backgroundColor,
        string $fontColor,
        string $fontSize,
        string $fontFamily,
        bool $applyStyling = false,
        bool $showIntro = true
    ): string {
        return $this->optOutManager->getOptOutSelfContainedEmbedCode($backgroundColor, $fontColor, $fontSize, $fontFamily, $applyStyling, $showIntro);
    }


    /**
     * Marks all "what's new" changes as read for the current user.
     *
     * @return bool True if changes were marked as read, false otherwise.
     * @internal
     */
    public function whatIsNewMarkAllChangesReadForCurrentUser()
    {
        Piwik::checkUserHasSomeViewAccess();
        Piwik::checkUserIsNotAnonymous();

        $model = new UsersModel();
        $user = $model->getUser(Piwik::getCurrentUserLogin());
        if (!empty($user)) {
            $userChanges = new UserChanges($user);
            $userChanges->markChangesAsRead();
            return true;
        }
        return false;
    }
}
