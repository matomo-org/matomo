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
use Piwik\API\Request;
use Piwik\Changes\UserChanges;
use Piwik\Log\Logger;
use Piwik\Access;
use Piwik\ArchiveProcessor\Rules;
use Piwik\ArchiveProcessor;
use Piwik\Config;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Metrics\Formatter;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Request as PiwikRequest;
use Piwik\Segment;
use Piwik\Scheduler\Scheduler;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Tracker\Failures;
use Piwik\Url;
use Piwik\Plugins\UsersManager\Model as UsersModel;

/**
 * Provides administrative API methods for scheduling, archiving, tracking failures, and opt-out code generation.
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
     * Will run all scheduled tasks due to run at this time.
     *
     * @return array<int, array{task:string, output:string}> Results for each executed scheduled task.
     * @hideExceptForSuperUser
     */
    public function runScheduledTasks(): array
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->scheduler->run();
    }

    /**
     * @param bool|string $enableBrowserTriggerArchiving
     * @param int|string $todayArchiveTimeToLive
     * @return true
     * @internal
     */
    public function setArchiveSettings($enableBrowserTriggerArchiving, $todayArchiveTimeToLive): bool
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
     * @param string[] $trustedHosts
     * @return true
     * @internal
     */
    public function setTrustedHosts($trustedHosts): bool
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
     * @param bool|string $useCustomLogo
     * @param bool|string $hasCustomLogo
     * @param bool|string $hasCustomFavicon
     * @return array<string, mixed>
     * @internal
     */
    public function setBrandingSettings($useCustomLogo, $hasCustomLogo, $hasCustomFavicon): array
    {
        Piwik::checkUserHasSuperUserAccess();
        $customLogo = new CustomLogo();
        $response = [];

        if (!$useCustomLogo || (!$hasCustomLogo && !$hasCustomFavicon)) {
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
     * Note: This is done automatically when tracking or importing visits in the past.
     *
     * @param string $idSites Comma-separated list of site IDs to invalidate reports for.
     * @param string|string[] $dates Comma-separated list of dates or date ranges to invalidate.
     *                               Use an array of strings when `$period` is `range`.
     * @param 'day'|'week'|'month'|'year'|'range'|false $period The period type to invalidate.
     *                                                          Invalidating one period also invalidates its parent periods.
     * @param string|false $segment Optional segment to invalidate reports for.
     *                               Example: "referrerName==example.com"
     *                               Supports AND (;) and OR (,) operators.
     * @param bool $cascadeDown If true, child periods will be invalidated as well. So if it is requested to invalidate
     *                          a month, then all the weeks and days within that month will also be invalidated. But only
     *                          if this parameter is set.
     * @param bool $_forceInvalidateNonexistent Whether to also invalidate archives that do not currently exist.
     * @return string[] Log messages describing the invalidation work that was scheduled.
     * @hideExceptForSuperUser
     */
    public function invalidateArchivedReports(
        $idSites,
        $dates,
        $period = false,
        $segment = false,
        $cascadeDown = false,
        $_forceInvalidateNonexistent = false
    ): array {
        $idSites = Site::getIdSitesFromIdSitesString($idSites, false, true);
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
        [$dates, $invalidDates] = $this->getDatesToInvalidateFromString($dates, $period ?: null);

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
     * @hideExceptForSuperUser
     */
    public function runCronArchiving(): void
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
     */
    public function deleteAllTrackingFailures(): void
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
     * @param int $idSite Site ID that owns the tracking failure.
     * @param int|string $idFailure Tracking failure ID to delete.
     */
    public function deleteTrackingFailure(int $idSite, $idFailure): void
    {
        Piwik::checkUserHasAdminAccess($idSite);

        $this->trackingFailures->deleteTrackingFailure($idSite, $idFailure);
    }

    /**
     * Get all tracking failures. A user retrieves only tracking failures for sites with at least admin access.
     * A superuser will also retrieve failed requests for sites that don't exist.
     *
     * @return array<int, array<string, mixed>> Tracking failures visible to the current user.
     */
    public function getTrackingFailures(): array
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
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @param string|null|false $plugin
     * @param string|string[]|null|false $report
     * @return array<string, mixed>
     * @internal
     */
    public function archiveReports(int $idSite, string $period, string $date, $segment = false, $plugin = false, $report = false)
    {
        if ($this->shouldRequireSuperUserForArchiveReports()) {
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

        if (is_array($result) && $isArchivePhpTriggered) {
            $result = $this->addPeakMemoryUsageToResult($result);
        }

        return $result;
    }

    private function addPeakMemoryUsageToResult(array $result): array
    {
        if (!function_exists('memory_get_peak_usage')) {
            return $result;
        }

        $peakMemoryBytes = memory_get_peak_usage(true);
        $formatter = new Formatter();

        $result['peak_memory_usage'] = $peakMemoryBytes;
        $result['peak_memory_usage_pretty'] = $formatter->getPrettySizeFromBytes($peakMemoryBytes);

        return $result;
    }

    private function shouldRequireSuperUserForArchiveReports(): bool
    {
        $rootApiMethod = $this->normalizeApiMethodName(Request::getRootApiRequestMethod() ?: '');
        $currentApiMethod = $this->normalizeApiMethodName(PiwikRequest::fromRequest()->getStringParameter('method', ''));

        // Require superuser for direct archiveReports calls and archiveReports inside bulk requests.
        return $rootApiMethod === 'CoreAdminHome.archiveReports'
            || ($rootApiMethod === 'API.getBulkRequest' && $currentApiMethod === 'CoreAdminHome.archiveReports');
    }

    private function normalizeApiMethodName(string $method): string
    {
        return preg_replace('/[^\w\.]+/', '', Common::sanitizeInputValue($method));
    }

    /**
     * @param array<int, string>|string $dates
     * @param 'day'|'week'|'month'|'year'|'range'|null $period
     * @return array{0: array<int, Date|string>, 1: string[]}
     */
    private function getDatesToInvalidateFromString($dates, ?string $period): array
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
     * @internal
     */
    public function whatIsNewMarkAllChangesReadForCurrentUser(): bool
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
