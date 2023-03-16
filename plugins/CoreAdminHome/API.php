<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Piwik\Access;
use Piwik\ArchiveProcessor\Rules;
use Piwik\ArchiveProcessor;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Scheduler\Scheduler;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Tracker\Failures;
use Piwik\Url;

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

    /**
     * @var Failures
     */
    private $trackingFailures;

    /**
     * @var OptOutManager
     */
    private $optOutManager;

    public function __construct(Scheduler $scheduler, ArchiveInvalidator $invalidator, Failures $trackingFailures,
                                OptOutManager $optOutManager)
    {
        $this->scheduler = $scheduler;
        $this->invalidator = $invalidator;
        $this->trackingFailures = $trackingFailures;
        $this->optOutManager = $optOutManager;
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
     * @internal
     */
    public function setBrandingSettings($useCustomLogo)
    {
        Piwik::checkUserHasSuperUserAccess();

        $customLogo = new CustomLogo();

        if ($customLogo->isCustomLogoFeatureEnabled()) {
            if ($useCustomLogo) {
                $customLogo->enable();
            } else {
                $customLogo->disable();
            }
        }

        return true;
    }
    /**
     * Invalidates report data, forcing it to be recomputed during the next archiving run.
     *
     * Note: This is done automatically when tracking or importing visits in the past.
     *
     * @param string $idSites Comma separated list of site IDs to invalidate reports for.
     * @param string|string[] $dates Comma separated list of dates of periods to invalidate reports for or array of strings
     *                               (needed if period = range).
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
    public function invalidateArchivedReports($idSites, $dates, $period = false, $segment = false, $cascadeDown = false,
                                              $_forceInvalidateNonexistent = false)
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

        /** Date[]|string[] $dates */
        list($dates, $invalidDates) = $this->getDatesToInvalidateFromString($dates, $period);

        $invalidationResult = $this->invalidator->markArchivesAsInvalidated($idSites, $dates, $period, $segment, (bool)$cascadeDown, (bool)$_forceInvalidateNonexistent);

        $output = $invalidationResult->makeOutputLogs();
        if ($invalidDates) {
            $output[] = 'Warning: some of the Dates to invalidate were invalid: \'' .
                implode("', '", $invalidDates) . "'. Matomo simply ignored those and proceeded with the others.";
        }

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
     * Deletes all tracking failures this user has at least admin access to.
     * A super user will also delete tracking failures for sites that don't exist.
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
     * Deletes a specific tracking failure
     * @param int $idSite
     * @param int $idFailure
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
     * @return array
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
     * @param $idSite
     * @param $period
     * @param $date
     * @param bool $segment
     * @param bool $plugin
     * @param bool $report
     * @return mixed
     * @throws \Piwik\Exception\UnexpectedWebsiteFoundException
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
        $parameters = new ArchiveProcessor\Parameters(
            $site,
            $period,
            new Segment(
                $segment,
                [$idSite],
                $period->getDateTimeStart()->setTimezone($site->getTimezone()),
                $period->getDateTimeEnd()->setTimezone($site->getTimezone())
            )
        );
        if ($report) {
            $parameters->setArchiveOnlyReport($report);
        }

        // TODO: need to test case when there are multiple plugin archives w/ only some data each. does purging remove some that we need?
        $archiveLoader = new ArchiveProcessor\Loader($parameters, $invalidateBeforeArchiving);

        $result = $archiveLoader->prepareArchive($plugin);
        if (!empty($result)) {
            $result = [
                'idarchives' => $result[0],
                'nb_visits' => $result[1],
            ];
        }
        return $result;
    }

    /**
     * Ensure the specified dates are valid.
     * Store invalid date so we can log them
     * @param array|string $dates
     * @return array
     */
    private function getDatesToInvalidateFromString($dates, $period)
    {
        $toInvalidate = array();
        $invalidDates = array();

        if (!is_array($dates)) {
            $dates = explode(',', trim($dates));
        }

        $dates = array_unique($dates);

        foreach ($dates as $theDate) {
            $theDate = trim($theDate);

            if ($period == 'range') {
                try {
                    $period = Factory::build('range', $theDate);
                } catch (\Exception $e) {
                    $invalidDates[] = $theDate;
                    continue;
                }

                if ($period->getRangeString() == $theDate) {
                    $toInvalidate[] = $theDate;
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

        return array($toInvalidate, $invalidDates);
    }

    /**
     * Show the JavaScript opt out code
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
     * @return string
     *
     * @internal
     */
    public function getOptOutJSEmbedCode(string $backgroundColor, string $fontColor,
                                         string $fontSize, string $fontFamily, bool $applyStyling, bool $showIntro,
                                         string $matomoUrl, string $language): string
    {

        return $this->optOutManager->getOptOutJSEmbedCode($matomoUrl, $language, $backgroundColor, $fontColor, $fontSize,
                                                          $fontFamily, $applyStyling, $showIntro);
    }

    /**
     * Show the self-contained JavaScript opt out code
     *
     * @param string $backgroundColor
     * @param string $fontColor
     * @param string $fontSize
     * @param string $fontFamily
     * @param bool   $applyStyling
     * @param bool   $showIntro
     *
     * @return string
     *
     * @internal
     */
    public function getOptOutSelfContainedEmbedCode(string $backgroundColor,
                                                    string $fontColor, string $fontSize, string $fontFamily,
                                                    bool $applyStyling = false, bool $showIntro = true): string
    {
        return $this->optOutManager->getOptOutSelfContainedEmbedCode($backgroundColor, $fontColor, $fontSize, $fontFamily, $applyStyling, $showIntro);
    }


}