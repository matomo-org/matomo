<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports;

use Exception;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Context;
use Piwik\Date;
use Piwik\Db;
use Piwik\Development;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Log;
use Piwik\NoAccessException;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\Dashboard\Dashboard;
use Piwik\Plugins\ImageGraph\ImageGraph;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\ReportRenderer;
use Piwik\Scheduler\RetryableException;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Translation\Translator;
use Piwik\Log\LoggerInterface;

/**
 * The ScheduledReports API lets you manage Scheduled Email reports, as well as generate, download or email any existing report.
 *
 * "generateReport" will generate the requested report (for a specific date range, website and in the requested language).
 * "sendReport" will send the report by email to the recipients specified for this report.
 *
 * You can also get the list of all existing reports via "getReports", create new reports via "addReport",
 * or manage existing reports with "updateReport" and "deleteReport".
 * See also the documentation about <a href='https://matomo.org/docs/email-reports/' rel='noreferrer' target='_blank'>Scheduled Email reports</a> in Matomo.
 *
 * @phpstan-type ScheduledReport array{
 *     idreport: int|string,
 *     idsite: int|string,
 *     login: string,
 *     description: string,
 *     idsegment: int|string|null,
 *     period: 'day'|'week'|'month'|'never',
 *     period_param: 'day'|'week'|'month'|'year'|null,
 *     hour: int|string,
 *     type: string,
 *     format: string,
 *     reports: list<string>,
 *     parameters: array<string, mixed>,
 *     ts_created: string|null,
 *     ts_last_sent: string|null,
 *     deleted: int|string,
 *     evolution_graph_within_period: bool|int|string,
 *     evolution_graph_period_n: int|string
 * }
 * @phpstan-type OutputMode self::OUTPUT_DOWNLOAD|self::OUTPUT_SAVE_ON_DISK|self::OUTPUT_INLINE|self::OUTPUT_RETURN
 * @phpstan-import-type StoredSegment from \Piwik\Plugins\SegmentEditor\API
 * @phpstan-import-type ProcessedReportData from \Piwik\Plugins\API\ProcessedReport
 *
 * @method static \Piwik\Plugins\ScheduledReports\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public const ENFORCE_ORDER_PARAMETER = ScheduledReports::ENFORCE_ORDER_PARAMETER;

    public const VALIDATE_PARAMETERS_EVENT = 'ScheduledReports.validateReportParameters';
    public const GET_REPORT_PARAMETERS_EVENT = 'ScheduledReports.getReportParameters';
    public const GET_REPORT_METADATA_EVENT = 'ScheduledReports.getReportMetadata';
    public const GET_REPORT_TYPES_EVENT = 'ScheduledReports.getReportTypes';
    public const GET_REPORT_FORMATS_EVENT = 'ScheduledReports.getReportFormats';
    public const GET_RENDERER_INSTANCE_EVENT = 'ScheduledReports.getRendererInstance';
    public const PROCESS_REPORTS_EVENT = 'ScheduledReports.processReports';
    public const GET_REPORT_RECIPIENTS_EVENT = 'ScheduledReports.getReportRecipients';
    public const ALLOW_MULTIPLE_REPORTS_EVENT = 'ScheduledReports.allowMultipleReports';
    public const SEND_REPORT_EVENT = 'ScheduledReports.sendReport';

    public const OUTPUT_DOWNLOAD = 1;
    public const OUTPUT_SAVE_ON_DISK = 2;
    public const OUTPUT_INLINE = 3;
    public const OUTPUT_RETURN = 4;

    /**
     * @var bool
     */
    private $enableSaveReportOnDisk = false;

    /**
     * static cache storing reports
     *
     * @var array<string, list<ScheduledReport>>
     */
    public static $cache = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Creates and schedules a new report.
     *
     * @param int $idSite The numeric ID of the website to report on.
     * @param string $description The report title shown in the UI and used in generated filenames.
     *                            Truncated to 250 characters.
     * @param 'day'|'week'|'month'|'never' $period The delivery schedule for the report.
     * @param int $hour The hour of day (0–23) when the report should be sent.
     * @param string $reportType The transport medium identifier, such as `'email'`.
     * @param string $reportFormat The output format identifier, such as `'pdf'` or `'html'`.
     * @param list<string> $reports The report unique IDs to include, e.g. `['VisitsSummary_get', 'Actions_get']`.
     * @param array<string, mixed> $parameters Transport-specific parameters, e.g. `['emailMe' => true,
     *                                         'additionalEmails' => ['user@example.com']]` for email reports.
     * @param int|false $idSegment The saved segment ID to apply, or `false` for no segment filter.
     * @param 'prev'|'each' $evolutionPeriodFor Whether evolution graphs compare previous periods (`'prev'`)
     *                                          or each day within the selected period (`'each'`).
     * @param int|null $evolutionPeriodN The number of previous periods to include when `$evolutionPeriodFor`
     *                                   is `'prev'`. Defaults to the configured graph evolution periods.
     * @param 'day'|'week'|'month'|'year'|null $periodParam The data period to generate on each scheduled send.
     *                                                       Defaults to the delivery schedule period.
     * @return int The newly created scheduled report ID.
     */
    public function addReport(
        $idSite,
        string $description,
        string $period,
        $hour,
        string $reportType,
        string $reportFormat,
        $reports,
        $parameters,
        $idSegment = false,
        string $evolutionPeriodFor = 'prev',
        $evolutionPeriodN = null,
        ?string $periodParam = null
    ) {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasViewAccess($idSite);

        $currentUser = Piwik::getCurrentUserLogin();
        self::ensureLanguageSetForUser($currentUser);

        self::validateCommonReportAttributes($period, $hour, $description, $idSegment, $reportType, $reportFormat, $evolutionPeriodFor, $evolutionPeriodN);

        if (null !== $periodParam) {
            self::validatePeriodParam($periodParam);
        }

        // report parameters validations
        $parameters = self::validateReportParameters($reportType, $parameters);

        // validation of requested reports
        $reports = self::validateRequestedReports($idSite, $reportType, $reports);

        $idReport = $this->getModel()->createReport([
             'idsite'      => $idSite,
             'login'       => $currentUser,
             'description' => $description,
             'idsegment'   => $idSegment,
             'period'      => $period,
             'period_param' => $periodParam,
             'hour'        => $hour,
             'type'        => $reportType,
             'format'      => $reportFormat,
             'parameters'  => $parameters,
             'reports'     => $reports,
             'ts_created'  => Date::now()->getDatetime(),
             'deleted'     => 0,
             'evolution_graph_within_period' => $evolutionPeriodFor == 'each',
             'evolution_graph_period_n' => $evolutionPeriodN ?: ImageGraph::getDefaultGraphEvolutionLastPeriods(),
        ]);

        return $idReport;
    }

    private static function ensureLanguageSetForUser(string $currentUser): void
    {
        $lang = Request::processRequest('LanguagesManager.getLanguageForUser', [
            'login' => $currentUser,
        ]);

        if (empty($lang)) {
            Request::processRequest('LanguagesManager.setLanguageForUser', [
                'login' => $currentUser,
                'languageCode' => LanguagesManager::getLanguageCodeForCurrentUser(),
            ]);
        }
    }

    /**
     * Updates an existing report.
     *
     * @param int $idReport The scheduled report ID to update.
     * @param int $idSite The numeric ID of the website the report belongs to.
     * @param string $description The report title shown in the UI and used in generated filenames.
     *                            Truncated to 250 characters.
     * @param 'day'|'week'|'month'|'never' $period The delivery schedule for the report.
     * @param int $hour The hour of day (0–23) when the report should be sent.
     * @param string $reportType The transport medium identifier, such as `'email'`.
     * @param string $reportFormat The output format identifier, such as `'pdf'` or `'html'`.
     * @param list<string> $reports The report unique IDs to include, e.g. `['VisitsSummary_get', 'Actions_get']`.
     * @param array<string, mixed> $parameters Transport-specific parameters, e.g. `['emailMe' => true,
     *                                         'additionalEmails' => ['user@example.com']]` for email reports.
     * @param int|false $idSegment The saved segment ID to apply, or `false` for no segment filter.
     * @param 'prev'|'each' $evolutionPeriodFor Whether evolution graphs compare previous periods (`'prev'`)
     *                                          or each day within the selected period (`'each'`).
     * @param int|null $evolutionPeriodN The number of previous periods to include when `$evolutionPeriodFor`
     *                                   is `'prev'`. Defaults to the configured graph evolution periods.
     * @param 'day'|'week'|'month'|'year'|null $periodParam The data period to generate on each scheduled send.
     *                                                       Defaults to the delivery schedule period.
     * @see addReport()
     */
    public function updateReport(
        $idReport,
        $idSite,
        string $description,
        string $period,
        $hour,
        string $reportType,
        string $reportFormat,
        $reports,
        $parameters,
        $idSegment = false,
        string $evolutionPeriodFor = 'prev',
        $evolutionPeriodN = null,
        ?string $periodParam = null
    ): void {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasViewAccess($idSite);

        $scheduledReports = $this->getReports($idSite, $periodSearch = false, $idReport);
        $report   = reset($scheduledReports);
        /** @phpstan-var ScheduledReport $report */
        $idReport = $report['idreport'];

        $currentUser = Piwik::getCurrentUserLogin();
        self::ensureLanguageSetForUser($currentUser);

        self::validateCommonReportAttributes($period, $hour, $description, $idSegment, $reportType, $reportFormat, $evolutionPeriodFor, $evolutionPeriodN);

        if (null !== $periodParam) {
            self::validatePeriodParam($periodParam);
        }

        // report parameters validations
        $parameters = self::validateReportParameters($reportType, $parameters);

        // validation of requested reports
        $reports = self::validateRequestedReports($idSite, $reportType, $reports);

        $this->getModel()->updateReport($idReport, [
            'description' => $description,
            'idsegment'   => $idSegment,
            'period'      => $period,
            'period_param' => $periodParam,
            'hour'        => $hour,
            'type'        => $reportType,
            'format'      => $reportFormat,
            'parameters'  => $parameters,
            'reports'     => $reports,
            'evolution_graph_within_period' => $evolutionPeriodFor == 'each',
            'evolution_graph_period_n' => $evolutionPeriodN ?: ImageGraph::getDefaultGraphEvolutionLastPeriods(),
        ]);

        self::$cache = [];
    }

    /**
     * Marks a scheduled report as deleted. The report row is retained in the database with `deleted = 1`.
     *
     * @param int $idReport The scheduled report ID to delete.
     * @return void
     */
    public function deleteReport($idReport)
    {
        $APIScheduledReports = $this->getReports($idSite = false, $periodSearch = false, $idReport);
        $report = reset($APIScheduledReports);
        /** @phpstan-var ScheduledReport $report */
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($report['login']);

        $this->getModel()->updateReport($idReport, [
            'deleted' => 1,
        ]);

        self::$cache = [];
    }

    /**
     * Builds the report selection payload for a dashboard export. Maps dashboard widgets to their
     * corresponding scheduled report IDs.
     *
     * @param int $dashId The dashboard ID to inspect.
     * @param int $idSite The numeric ID of the website the dashboard is being exported for.
     * @param string $segment Custom segment definition to resolve against saved segments.
     * @return array{dashboardName: string, email: array<string, bool>, idSegment: int|null, unmappedWidgets: string[]}
     *         The dashboard name, a map of matching report unique IDs to `true`, the resolved segment ID
     *         (or `null`), and widget names that could not be mapped to reports.
     * @internal
     */
    public function getWidgetReportMap(int $dashId, int $idSite, string $segment = ''): array
    {
        $dashId = $this->validatePositiveIntegerParameter($dashId, 'dashId');
        $idSite = $this->validatePositiveIntegerParameter($idSite, 'idSite');
        $segment = trim($segment);

        Piwik::checkUserHasViewAccess($idSite);
        $idSegment = $this->findIdSegmentForDefinition($segment, $idSite);

        $dashboardInfo = $this->getDashboardNameAndLayout($dashId);
        if ($dashboardInfo) {
            $layout = $dashboardInfo['layout'];
            $dashboardName = $dashboardInfo['name'];
            if (empty($layout)) {
                return [
                    'dashboardName' => $dashboardName,
                    'email' => [],
                    'idSegment' => $idSegment,
                    'unmappedWidgets' => [],
                ];
            }
            $mapper = new WidgetReportMapper();
            $widgetIds = $mapper->extractWidgetIdsFromLayout($layout);
            $widgetReportMapping = $mapper->getMappingForSite((string) $idSite);
            $reportMapping = [];
            $unmappedWidgets = [];
            $widgetNamesById = $mapper->getWidgetNamesById($widgetIds);
            foreach ($widgetIds as $widgetId) {
                $reportKey = $widgetReportMapping[$widgetId] ?? null;
                if ($reportKey) {
                    $reportMapping[$reportKey] = true;
                } elseif (isset($widgetNamesById[$widgetId]) && $widgetNamesById[$widgetId]) {
                    $unmappedWidgets[] = $widgetNamesById[$widgetId];
                }
            }
            return [
                'dashboardName' => $dashboardName,
                'email' => $reportMapping,
                'idSegment' => $idSegment,
                'unmappedWidgets' => $unmappedWidgets,
            ];
        }
        return [
            'dashboardName' => '',
            'email' => [],
            'idSegment' => $idSegment,
            'unmappedWidgets' => [],
        ];
    }

    /**
     * @return array{name: string, layout: mixed}|null
     */
    private function getDashboardNameAndLayout(int $dashId): ?array
    {
        if (Piwik::isUserIsAnonymous()) {
            return null;
        }

        $dashboard = new Dashboard();
        $login = Piwik::getCurrentUserLogin();
        $allDashboards = $dashboard->getAllDashboards($login);
        $name = $layout = '';
        $dashboardFound = false;
        foreach ($allDashboards as $dashbrd) {
            if ((int) $dashbrd['iddashboard'] === $dashId) {
                $dashboardFound = true;
                $layout = $dashbrd['layout'];
                $name = $dashbrd['name'];
                break;
            }
        }
        if ($dashId === 1 && !$dashboardFound) {
            $layout = $dashboard->decodeLayout($dashboard->getDefaultLayout());
            $name = Piwik::translate('Dashboard_Dashboard');
        }
        return ['name' => $name, 'layout' => $layout];
    }

    private function validatePositiveIntegerParameter(int $value, string $parameterName): int
    {
        if ($value < 1) {
            throw new InvalidRequestParameterException("The parameter '$parameterName' contains an invalid value.");
        }

        return $value;
    }

    private function findIdSegmentForDefinition(string $segmentDefinition, int $idSite): ?int
    {
        if ($segmentDefinition === '' || !self::isSegmentEditorActivated()) {
            return null;
        }

        $segmentHash = (new Segment($segmentDefinition, [$idSite]))->getHash();
        $segments = APISegmentEditor::getInstance()->getAll($idSite);
        foreach ($segments as $segment) {
            // SegmentEditor::getAll() is expected to always include a precomputed hash.
            if ($segment['hash'] === $segmentHash) {
                return (int) $segment['idsegment'];
            }
        }

        return null;
    }

    /**
     * Returns scheduled reports that match the supplied filters. All filter parameters are optional;
     * passing `false` disables that filter.
     *
     * @param int|false $idSite Filters reports to a specific website when provided.
     * @param 'day'|'week'|'month'|'never'|false $period Filters reports by delivery schedule when provided.
     * @param int|false $idReport Returns a single scheduled report when provided. Throws if not found.
     * @param bool $ifSuperUserReturnOnlySuperUserReports When `true`, super users only receive their own
     *                                                     reports instead of all reports.
     * @param int|false $idSegment Filters reports to a specific saved segment when provided.
     * @return array<int, array<string, mixed>> The matching scheduled reports, ordered by description. Each entry
     *                                          has decoded `parameters` and `reports` fields.
     * @phpstan-return list<ScheduledReport>
     */
    public function getReports($idSite = false, $period = false, $idReport = false, $ifSuperUserReturnOnlySuperUserReports = false, $idSegment = false): array
    {
        Piwik::checkUserHasSomeViewAccess();

        $cacheKey = (int)$idSite . '.' . (string)$period . '.' . (int)$idReport . '.' . (int)$ifSuperUserReturnOnlySuperUserReports;
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $sqlWhere = '';
        $bind = [];

        // Super user gets all reports back, other users only their own
        if (
            !Piwik::hasUserSuperUserAccess()
            || $ifSuperUserReturnOnlySuperUserReports
        ) {
            $sqlWhere .= "AND login = ?";
            $bind[] = Piwik::getCurrentUserLogin();
        }

        if (!empty($period)) {
            $this->validateReportPeriod($period);
            $sqlWhere .= " AND period = ? ";
            $bind[] = $period;
        }
        if (!empty($idSite)) {
            Piwik::checkUserHasViewAccess($idSite);
            $sqlWhere .= " AND " . Common::prefixTable('site') . ".idsite = ?";
            $bind[] = $idSite;
        }
        if (!empty($idReport)) {
            $sqlWhere .= " AND idreport = ?";
            $bind[] = $idReport;
        }
        if (!empty($idSegment)) {
            $sqlWhere .= " AND idsegment = ?";
            $bind[] = $idSegment;
        }

        // Joining with the site table to work around pre-1.3 where reports could still be linked to a deleted site
        $reports = Db::fetchAll("SELECT report.*
								FROM " . Common::prefixTable('report') . " AS `report`
									JOIN " . Common::prefixTable('site') . "
									USING (idsite)
								WHERE deleted = 0
									$sqlWhere ORDER BY description", $bind);
        // When a specific report was requested and not found, throw an error
        if (
            $idReport !== false
            && empty($reports)
        ) {
            throw new Exception("Requested report couldn't be found.");
        }

        foreach ($reports as &$report) {
            // decode report parameters
            $report['parameters'] = json_decode($report['parameters'], true);

            // decode report list
            $report['reports'] = json_decode($report['reports'], true);

            if (
                !empty($report['parameters']['additionalEmails'])
                && is_array($report['parameters']['additionalEmails'])
            ) {
                $report['parameters']['additionalEmails'] = array_values($report['parameters']['additionalEmails']);
            }

            if (empty($report['evolution_graph_period_n'])) {
                $report['evolution_graph_period_n'] = ImageGraph::getDefaultGraphEvolutionLastPeriods();
            }

            // default the period param to use to the email schedule
            if (empty($report['period_param'])) {
                $periodParam = $report['period'] == Schedule::PERIOD_NEVER ? Schedule::PERIOD_DAY : $report['period'];
                $report['period_param'] = $periodParam;
            }
        }

        // static cache
        self::$cache[$cacheKey] = $reports;

        return $reports;
    }

    /**
     * Generates a scheduled report in the requested output mode.
     *
     * @param int $idReport The scheduled report ID to generate.
     * @param string $date The date or date range to process.
     *                     `'YYYY-MM-DD'`, magic keywords (`today`, `yesterday`, `lastWeek`, `lastMonth`, `lastYear`),
     *                     or date range (e.g. `'YYYY-MM-DD,YYYY-MM-DD'`, `lastX`, `previousX`).
     * @param string|false $language The ISO language code to render the report in (e.g. `'en'`, `'de'`),
     *                               or `false` to use the default language.
     * @param int|false $outputType The output mode controlling how the generated report is delivered.
     *                              Use `OUTPUT_DOWNLOAD` (browser download), `OUTPUT_SAVE_ON_DISK` (temp file for
     *                              sending), `OUTPUT_INLINE` (browser inline display), or `OUTPUT_RETURN` (return
     *                              contents as string). Defaults to `OUTPUT_DOWNLOAD`.
     * @param 'day'|'week'|'month'|'year'|'range'|false $period The data period to generate, or `false` to use
     *                                                        the report's stored period.
     * @param string|false $reportFormat The output format identifier (e.g. `'pdf'`, `'html'`), or `false` to use
     *                                   the stored format.
     * @param array<string, mixed>|false $parameters Transport-specific parameters to override for this generation,
     *                                               or `false` to use the stored parameters.
     * @return array{0: string, 1: string|null, 2: string, 3: string, 4: array<int, mixed>}|string|void
     *         Returns a 5-element array `[$outputFilename, $prettyDate, $reportSubject, $reportTitle,
     *         $additionalFiles]` when using `OUTPUT_SAVE_ON_DISK`, the rendered report string when using
     *         `OUTPUT_RETURN`, or void when streaming to the browser (`OUTPUT_DOWNLOAD` / `OUTPUT_INLINE`).
     * @phpstan-param OutputMode|false $outputType
     * @phpstan-return ($outputType is self::OUTPUT_SAVE_ON_DISK
     *     ? array{0: string, 1: string, 2: string, 3: string, 4: list<mixed>}
     *     : ($outputType is self::OUTPUT_RETURN ? string : void))
     */
    public function generateReport(
        $idReport,
        string $date,
        $language = false,
        $outputType = false,
        $period = false,
        $reportFormat = false,
        $parameters = false
    ) {
        Piwik::checkUserIsNotAnonymous();

        if (!$this->enableSaveReportOnDisk && $outputType == self::OUTPUT_SAVE_ON_DISK) {
            $outputType = self::OUTPUT_DOWNLOAD;
        }

        /** @var Translator $translator */
        $translator = StaticContainer::get('Piwik\Translation\Translator');

        // load specified language
        if (empty($language)) {
            $language = $translator->getDefaultLanguage();
        }

        $translator->setCurrentLanguage($language);

        $reports = $this->getReports($idSite = false, $_period = false, $idReport);
        $report = reset($reports);
        /** @phpstan-var ScheduledReport $report */

        $idReport = $report['idreport'];
        $idSite = $report['idsite'];
        $login  = $report['login'];
        $reportType = $report['type'];

        $this->checkUserHasViewPermission($login, $idSite);

        // override report period
        if (empty($period)) {
            $period = $report['period_param'] ?? $report['period'];
        }

        $this->checkDateAndPeriodCombination($date, $period);

        // override report format
        if (!empty($reportFormat)) {
            self::validateReportFormat($reportType, $reportFormat);
            $report['format'] = $reportFormat;
        } else {
            $reportFormat = $report['format'];
        }

        // override and/or validate report parameters
        $parameters = json_decode(
            self::validateReportParameters($reportType, empty($parameters) ? $report['parameters'] : $parameters),
            true
        );

        if (!is_array($parameters)) {
            $parameters = [];
        }

        $report['parameters'] = $parameters;
        $enforceCustomOrder   = array_key_exists(self::ENFORCE_ORDER_PARAMETER, $parameters)
            && !empty($parameters[self::ENFORCE_ORDER_PARAMETER]);

        $originalShowEvolutionWithinSelectedPeriod      = GeneralConfig::getConfigValue('graphs_show_evolution_within_selected_period');
        $originalDefaultEvolutionGraphLastPeriodsAmount = GeneralConfig::getConfigValue('graphs_default_evolution_graph_last_days_amount');
        $initialFilterTruncate = Common::getRequestVar('filter_truncate', false);
        try {
            Config::setSetting('General', 'graphs_show_evolution_within_selected_period', (bool)$report['evolution_graph_within_period']);
            Config::setSetting('General', 'graphs_default_evolution_graph_last_days_amount', $report['evolution_graph_period_n']);

            // available reports
            $availableReportMetadata = \Piwik\Plugins\API\API::getInstance()->getReportMetadata($idSite);

            $reportMetadata = [];
            if ($enforceCustomOrder) {
                // we need to lookup which reports metadata are registered in this report
                // and keep the order defined
                $reportMetadataByUniqueId = [];
                foreach ($availableReportMetadata as $metadata) {
                    $reportMetadataByUniqueId[$metadata['uniqueId']] = $metadata;
                }

                foreach ($report['reports'] as $reportUniqueId) {
                    if (isset($reportMetadataByUniqueId[$reportUniqueId])) {
                        $reportMetadata[] = $reportMetadataByUniqueId[$reportUniqueId];
                    }
                }
            } else {
                // fallback to default metadata order when the flag isn't set
                foreach ($availableReportMetadata as $metadata) {
                    if (in_array($metadata['uniqueId'], $report['reports'], true)) {
                        $reportMetadata[] = $metadata;
                    }
                }
            }

            // the report will be rendered with the first 23 rows and will aggregate other rows in a summary row
            // 23 rows table fits in one portrait page
            $_GET['filter_truncate'] = GeneralConfig::getConfigValue('scheduled_reports_truncate');

            $prettyDate = '';
            $processedReports = [];
            $segment = self::getSegment($report['idsegment']);
            foreach ($reportMetadata as $action) {
                $apiModule = $action['module'];
                $apiAction = $action['action'];
                $apiParameters = [];
                if (isset($action['parameters'])) {
                    $apiParameters = $action['parameters'];
                }

                $mustRestoreGET = false;

                // all Websites dashboard should not be truncated in the report
                if ($apiModule == 'MultiSites') {
                    $mustRestoreGET = $_GET;
                    $_GET['enhanced'] = true;

                    if ($apiAction == 'getAll') {
                        $_GET['filter_truncate'] = false;
                        $_GET['filter_limit'] = -1; // show all websites in all websites report

                        // when a view/admin user created a report, workaround the fact that "Super User"
                        // is enforced in Scheduled tasks, and ensure Multisites.getAll only return the websites that this user can access
                        $userLogin = $report['login'];
                        if (
                            !empty($userLogin)
                            && !Piwik::hasTheUserSuperUserAccess($userLogin)
                        ) {
                            $_GET['_restrictSitesToLogin'] = $userLogin;
                        }
                    }
                }

                $params = [
                    'idSite' => $idSite,
                    'period' => $period,
                    'date' => $date,
                    'apiModule' => $apiModule,
                    'apiAction' => $apiAction,
                    'apiParameters' => $apiParameters,
                    'flat' => 1,
                    'idGoal' => false,
                    'language' => $language,
                    'serialize' => 0,
                    'format' => 'original',
                ];

                if ($segment != null) {
                    $params['segment'] = urlencode($segment['definition']);
                } else {
                    $params['segment'] = false;
                }

                try {
                    /** @phpstan-var ProcessedReportData $processedReport */
                    $processedReport = Request::processRequest('API.getProcessedReport', $params);
                } catch (\Exception $ex) {
                    // NOTE: can't use warning or error because the log message will appear in the UI as a notification
                    $this->logger->info("Error getting '?{report}' when generating scheduled report: {exception}", [
                        'report' => Http::buildQuery($params),
                        'exception' => $ex->getMessage(),
                    ]);

                    $this->logger->debug($ex);

                    continue;
                }

                $processedReport['segment'] = $segment;

                // TODO add static method getPrettyDate($period, $date) in Period
                $prettyDate = $processedReport['prettyDate'];

                if ($mustRestoreGET) {
                    $_GET = $mustRestoreGET;
                }

                $processedReports[] = $processedReport;
            }
        } finally {
            Config::setSetting('General', 'graphs_show_evolution_within_selected_period', $originalShowEvolutionWithinSelectedPeriod);
            Config::setSetting('General', 'graphs_default_evolution_graph_last_days_amount', $originalDefaultEvolutionGraphLastPeriodsAmount);

            // restore filter truncate parameter value
            if ($initialFilterTruncate !== false) {
                $_GET['filter_truncate'] = $initialFilterTruncate;
            }
        }

        /**
         * Triggered when generating the content of scheduled reports.
         *
         * This event can be used to modify the report data or report metadata of one or more reports
         * in a scheduled report, before the scheduled report is rendered and delivered.
         *
         * TODO: list data available in $report or make it a new class that can be documented (same for
         *       all other events that use a $report)
         *
         * @param array &$processedReports The list of processed reports in the scheduled report. Each entry
         *                                 contains report data and metadata as returned by `API.getProcessedReport`.
         * @param string $reportType A string ID describing how the scheduled report will be sent, e.g.
         *                           `'sms'` or `'email'`.
         * @param int $outputType The scheduled report output mode. One of {@see API::OUTPUT_DOWNLOAD},
         *                        {@see API::OUTPUT_SAVE_ON_DISK}, {@see API::OUTPUT_INLINE},
         *                        or {@see API::OUTPUT_RETURN}.
         * @param array $report An array describing the scheduled report that is being generated.
         *                      See the `ScheduledReport` type on {@see API} for the structure.
         */
        Piwik::postEvent(
            self::PROCESS_REPORTS_EVENT,
            [&$processedReports, $reportType, $outputType, $report]
        );

        /** @var ReportRenderer|null $reportRenderer */
        $reportRenderer = null;

        /**
         * Triggered when obtaining a renderer instance based on the scheduled report output format.
         *
         * Plugins that provide new scheduled report output formats should use this event to
         * handle their new report formats.
         *
         * @param ReportRenderer &$reportRenderer This variable should be set to an instance that
         *                                        extends {@link \Piwik\ReportRenderer} by one of the event
         *                                        subscribers.
         * @param string $reportType A string ID describing how the report is sent, e.g.
         *                           `'sms'` or `'email'`.
         * @param int $outputType The scheduled report output mode. One of {@see API::OUTPUT_DOWNLOAD},
         *                        {@see API::OUTPUT_SAVE_ON_DISK}, {@see API::OUTPUT_INLINE},
         *                        or {@see API::OUTPUT_RETURN}.
         * @param array $report An array describing the scheduled report that is being generated.
         *                      See the `ScheduledReport` type on {@see API} for the structure.
         */
        Piwik::postEvent(
            self::GET_RENDERER_INSTANCE_EVENT,
            [&$reportRenderer, $reportType, $outputType, $report]
        );

        if (is_null($reportRenderer)) {
            throw new Exception("A report renderer was not supplied in the event " . self::GET_RENDERER_INSTANCE_EVENT);
        }

        // init report renderer
        $reportRenderer->setIdSite((int)$idSite);
        $reportRenderer->setLocale($language);
        $reportRenderer->setReport($report);

        // render report
        $description = str_replace(["\r", "\n"], ' ', Common::unsanitizeInputValue($report['description']));

        [$reportSubject, $reportTitle] = self::getReportSubjectAndReportTitle(Common::unsanitizeInputValue(Site::getNameFor((int)$idSite)), $report['reports']);

        // if reporting for a segment, use the segment's name in the title
        if (is_array($segment) && strlen($segment['name'])) {
            $reportTitle .= " - " . $segment['name'];
        }
        $filename = "$reportTitle - $prettyDate - $description";

        $reportRenderer->renderFrontPage($reportTitle, $prettyDate, $description, $reportMetadata, $segment ?? []);
        array_walk($processedReports, [$reportRenderer, 'renderReport']);

        switch ($outputType) {
            case self::OUTPUT_SAVE_ON_DISK:
                // only used for SendReport

                $outputFilename = strtoupper($reportFormat) . ' ' . ucfirst($reportType) . ' Report - ' . $idReport . '.' . $date . '.' . $idSite . '.' . $language;
                $outputFilename .= ' - ' . Common::getRandomString(40, 'abcdefghijklmnoprstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVXYZ_');
                $outputFilename = $reportRenderer->sendToDisk($outputFilename);

                $additionalFiles = $this->getAttachments($reportRenderer, $report, $processedReports, $prettyDate);

                return [
                    $outputFilename,
                    $prettyDate,
                    $reportSubject,
                    $reportTitle,
                    $additionalFiles,
                ];

            case self::OUTPUT_INLINE:
                $reportRenderer->sendToBrowserInline($filename);
                break;

            case self::OUTPUT_RETURN:
                return $reportRenderer->getRenderedReport();

            default:
            case self::OUTPUT_DOWNLOAD:
                $reportRenderer->sendToBrowserDownload($filename);
                break;
        }
    }

    /**
     * Sends a scheduled report immediately. Generates the report, saves it to a temporary file,
     * dispatches it via the configured transport medium, and cleans up.
     *
     * @param int $idReport The scheduled report ID to send.
     * @param 'day'|'week'|'month'|'year'|'range'|false $period The data period to send, or `false` to use the report's
     *                                                 stored period.
     * @param string|false $date The date to generate the report for (e.g. `'2024-01-15'`),
     *                           or `false` to use the previous scheduled period.
     * @param bool $force Whether to send the report even if it has already been sent for the same period.
     */
    public function sendReport($idReport, $period = false, $date = false, $force = false): void
    {
        Piwik::checkUserIsNotAnonymous();

        $reports = $this->getReports($idSite = false, false, $idReport);
        $report = reset($reports);
        /** @phpstan-var ScheduledReport $report */

        if (!empty($period)) {
            self::validatePeriodParam($period, true);
            $report['period_param'] = $period;
        }

        if (empty($date)) {
            $date = Date::now()->subPeriod(1, $report['period'])->toString();
        }

        Context::changeIdSite($report['idsite'], function () use ($report, $idReport, $date, $force) {

            $language = \Piwik\Plugins\LanguagesManager\API::getInstance()->getLanguageForUser($report['login']);

            // generate report
            $this->enableSaveReportOnDisk = true;
            try {
                [$outputFilename, $prettyDate, $reportSubject, $reportTitle, $additionalFiles] =
                    $this->generateReport(
                        $idReport,
                        $date,
                        $language,
                        self::OUTPUT_SAVE_ON_DISK,
                        $report['period_param'] ?? false
                    );
            } catch (NoAccessException $e) {
                // This might occur if for some reason a report exists where the user does no longer have access to the
                // configured site. Normally those reports should be automatically deleted.
                Log::info("Skipping report as user does no longer have access to configured site");
                return;
            } catch (\Throwable $e) {
                $this->enableSaveReportOnDisk = false;
                throw new RetryableException($e->getMessage());
            }

            $this->enableSaveReportOnDisk = false;

            if (!file_exists($outputFilename)) {
                throw new Exception("The report file wasn't found in $outputFilename");
            }

            $contents = file_get_contents($outputFilename);

            if (empty($contents)) {
                Log::warning("Scheduled report file '%s' exists but is empty!", $outputFilename);
            }

            $reportType = $report['type'];

            try {
                /**
                 * Triggered when sending scheduled reports.
                 *
                 * Plugins that provide new scheduled report transport mediums should use this event to
                 * send the scheduled report.
                 *
                 * @param string $reportType A string ID describing how the report is sent, e.g.
                 *                           `'sms'` or `'email'`.
                 * @param array $report An array describing the scheduled report that is being generated.
                 *                      See the `ScheduledReport` type on {@see API} for the structure.
                 * @param string $contents The rendered contents of the scheduled report that should
                 *                         now be sent.
                 * @param string $filename The basename of the temporary file where the report was saved.
                 * @param string $prettyDate A human-readable date string for the data within the
                 *                           scheduled report.
                 * @param string $reportSubject A string describing what's in the scheduled report
                 *                              (typically the site name or "All Websites").
                 * @param string $reportTitle The scheduled report's title as configured by the user.
                 * @param array $additionalFiles The list of additional files (e.g. image attachments)
                 *                               that should be sent with this report.
                 * @param Period $period The period for which the report has been generated.
                 * @param bool $force A report can only be sent once per period. Setting this to true
                 *                    will force to send the report even if it has already been sent.
                 */
                Piwik::postEvent(
                    self::SEND_REPORT_EVENT,
                    [
                        &$reportType,
                        $report,
                        $contents,
                        $filename = basename($outputFilename),
                        $prettyDate,
                        $reportSubject,
                        $reportTitle,
                        $additionalFiles,
                        \Piwik\Period\Factory::build($report['period_param'] ?? $report['period'], $date),
                        $force,
                    ]
                );

                // Update flag in DB
                $now = Date::now()->getDatetime();
                $this->getModel()->updateReport($report['idreport'], ['ts_last_sent' => $now]);
            } finally {
                if (!Development::isEnabled()) {
                    @chmod($outputFilename, 0600);
                    Filesystem::deleteFileIfExists($outputFilename);
                }
            }
        });
    }

    private function getModel(): Model
    {
        return new Model();
    }

    /**
     * @param list<string> $reports
     * @return array{0: string, 1: string}
     */
    private static function getReportSubjectAndReportTitle(string $websiteName, array $reports): array
    {
        // if the only report is "All websites", we don't display the site name
        $reportTitle = $websiteName;
        $reportSubject = $websiteName;
        if (
            count($reports) == 1
            && $reports[0] == 'MultiSites_getAll'
        ) {
            $reportSubject = Piwik::translate('General_MultiSitesSummary');
            $reportTitle = $reportSubject;
        }

        return [$reportSubject, $reportTitle];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private static function validateReportParameters(string $reportType, $parameters): string
    {
        /** @var array<string, bool> $availableParameters */
        $availableParameters = [];

        /**
         * Triggered when gathering the available parameters for a scheduled report type.
         *
         * Plugins that provide their own scheduled report transport mediums should use this
         * event to list the available report parameters for their transport medium.
         *
         * @param array $availableParameters The list of available parameters for this report type.
         *                                   This is an array that maps parameter IDs with a boolean
         *                                   that indicates whether the parameter is mandatory or not.
         * @param string $reportType A string ID describing how the report is sent, eg,
         *                           `'sms'` or `'email'`.
         */
        Piwik::postEvent(self::GET_REPORT_PARAMETERS_EVENT, [&$availableParameters, $reportType]);

        // unset invalid parameters
        $availableParameterKeys = array_keys($availableParameters);
        foreach ($parameters as $key => $value) {
            if (!in_array($key, $availableParameterKeys)) {
                unset($parameters[$key]);
            }
        }

        // test that all required parameters are provided
        foreach ($availableParameters as $parameter => $mandatory) {
            if ($mandatory && !isset($parameters[$parameter])) {
                throw new Exception('Missing parameter : ' . $parameter);
            }
        }

        /**
         * Triggered when validating the parameters for a scheduled report.
         *
         * Plugins that provide their own scheduled reports backend should use this
         * event to validate the custom parameters defined with {@link ScheduledReports::getReportParameters()}.
         *
         * @param array $parameters The list of parameters for the scheduled report.
         * @param string $reportType A string ID describing how the report is sent, eg,
         *                           `'sms'` or `'email'`.
         */
        Piwik::postEvent(self::VALIDATE_PARAMETERS_EVENT, [&$parameters, $reportType]);

        return (string)json_encode($parameters);
    }

    /**
     * Truncates description in place to 250 characters.
     */
    private static function validateAndTruncateDescription(string &$description): void
    {
        $description = substr($description, 0, 250);
    }

    /**
     * @param int|string $idSite
     * @param list<string> $requestedReports
     */
    private static function validateRequestedReports($idSite, string $reportType, array $requestedReports): string
    {
        if (!self::allowMultipleReports($reportType)) {
            //sms can only contain one report, we silently discard all but the first
            $requestedReports = array_slice($requestedReports, 0, 1);
        }

        // retrieve available reports
        $availableReportMetadata = self::getReportMetadata($idSite, $reportType);

        $availableReportIds = [];
        foreach ($availableReportMetadata as $reportMetadata) {
            $availableReportIds[] = $reportMetadata['uniqueId'];
        }

        foreach ($requestedReports as $report) {
            if (!in_array($report, $availableReportIds)) {
                throw new Exception("Report $report is unknown or not available for report type '$reportType'.");
            }
        }

        return (string)json_encode($requestedReports);
    }

    /**
     * @param int|string $hour
     * @param int|false|null $idSegment Normalized in place to `null` when empty.
     * @param int|string|null $evolutionPeriodN
     */
    private static function validateCommonReportAttributes(
        string $period,
        $hour,
        string &$description,
        &$idSegment,
        string $reportType,
        string $reportFormat,
        string $evolutionPeriodFor,
        $evolutionPeriodN
    ): void {
        self::validateReportPeriod($period);
        self::validateReportHour($hour);
        self::validateAndTruncateDescription($description);
        self::validateIdSegment($idSegment);
        self::validateReportType($reportType);
        self::validateReportFormat($reportType, $reportFormat);
        self::validateEvolutionPeriod($evolutionPeriodFor, $evolutionPeriodN);
    }

    private static function validateReportPeriod(string $period): void
    {
        $availablePeriods = ['day', 'week', 'month', 'never'];
        if (!in_array($period, $availablePeriods)) {
            throw new Exception('Period schedule must be one of the following: ' . implode(', ', $availablePeriods) . ' (got ' . $period . ')');
        }
    }

    private static function validatePeriodParam(string $period, bool $allowRange = false): void
    {
        $periodValidator = new Period\PeriodValidator();
        $allowedPeriods = array_flip($periodValidator->getPeriodsAllowedForAPI());
        if (!$allowRange) {
            unset($allowedPeriods['range']);
        }

        if (!array_key_exists($period, $allowedPeriods)) {
            throw new Exception('Report period must be one of the following: ' . implode(', ', array_keys($allowedPeriods)) . ' (got ' . $period . ')');
        }
    }

    /**
     * @param int|string $hour
     */
    private static function validateReportHour($hour): void
    {
        if (!is_numeric($hour) || $hour < 0 || $hour > 23) {
            throw new Exception('Invalid hour schedule. Should be anything from 0 to 23 inclusive.');
        }
    }

    /**
     * @param int|string|false|null $idSegment Normalized in place to `null` when empty.
     */
    private static function validateIdSegment(&$idSegment): void
    {
        if (empty($idSegment) || (is_numeric($idSegment) && $idSegment == 0)) {
            $idSegment = null;
        } elseif (!is_numeric($idSegment)) {
            throw new Exception('Invalid segment identifier. Should be an integer.');
        } elseif (self::getSegment($idSegment) == null) {
            throw new Exception('Segment with id ' . $idSegment . ' does not exist or SegmentEditor is not activated.');
        }
    }

    private static function validateReportType(string $reportType): void
    {
        $reportTypes = array_keys(self::getReportTypes());

        if (!in_array($reportType, $reportTypes)) {
            throw new Exception(
                'Report type \'' . $reportType . '\' not valid. Try one of the following ' . implode(', ', $reportTypes)
            );
        }
    }

    private static function validateReportFormat(string $reportType, string $reportFormat): void
    {
        $reportFormats = array_keys(self::getReportFormats($reportType));

        if (!in_array($reportFormat, $reportFormats)) {
            throw new Exception(
                Piwik::translate(
                    'General_ExceptionInvalidReportRendererFormat',
                    [$reportFormat, implode(', ', $reportFormats)]
                )
            );
        }
    }

    /**
     * @param int|string|null $evolutionPeriodN
     */
    private static function validateEvolutionPeriod(string $evolutionPeriodFor, $evolutionPeriodN): void
    {
        if ($evolutionPeriodFor !== 'prev' && $evolutionPeriodFor !== 'each') {
            throw new \Exception('Invalid evolutionPeriodFor value, can only be "prev" or "each" (got ' . $evolutionPeriodFor . ').');
        }

        if ($evolutionPeriodFor === 'each' && !empty($evolutionPeriodN)) {
            throw new \Exception('The evolutionPeriodN param has no effect when evolutionPeriodFor is "each".');
        }

        if (
            !empty($evolutionPeriodN)
            && (!is_numeric($evolutionPeriodN) || (int)$evolutionPeriodN < 0)
        ) {
            throw new \Exception('Evolution period amount must be a positive number (got ' . $evolutionPeriodN . ').');
        }
    }

    /**
     * @param int|string $idSite
     * @param string $reportType
     * @return list<array<string, mixed>>
     * @ignore
     */
    public static function getReportMetadata($idSite, $reportType): array
    {
        $availableReportMetadata = [];

        /**
         * TODO: change this event so it returns a list of API methods instead of report metadata arrays.
         * Triggered when gathering the list of Matomo reports that can be used with a certain
         * transport medium.
         *
         * Plugins that provide their own transport mediums should use this
         * event to list the Matomo reports that their backend supports.
         *
         * @param array &$availableReportMetadata An array containing report metadata for each supported
         *                                        report.
         * @param string $reportType A string ID describing how the report is sent, eg,
         *                           `'sms'` or `'email'`.
         * @param int $idSite The ID of the site we're getting available reports for.
         */
        Piwik::postEvent(
            self::GET_REPORT_METADATA_EVENT,
            [&$availableReportMetadata, $reportType, $idSite]
        );

        return $availableReportMetadata;
    }

    /**
     * @param string $reportType
     * @return bool|null
     * @ignore
     */
    public static function allowMultipleReports($reportType)
    {
        $allowMultipleReports = null;

        /**
         * Triggered when we're determining if a scheduled report transport medium can
         * handle sending multiple Matomo reports in one scheduled report or not.
         *
         * Plugins that provide their own transport mediums should use this
         * event to specify whether their backend can send more than one Matomo report
         * at a time.
         *
         * @param bool &$allowMultipleReports Whether the backend type can handle multiple
         *                                    Matomo reports or not.
         * @param string $reportType A string ID describing how the report is sent, eg,
         *                           `'sms'` or `'email'`.
         */
        Piwik::postEvent(
            self::ALLOW_MULTIPLE_REPORTS_EVENT,
            [&$allowMultipleReports, $reportType]
        );
        return $allowMultipleReports;
    }

    /**
     * @return array<string, string>
     * @ignore
     */
    public static function getReportTypes(): array
    {
        $reportTypes = [];

        /**
         * Triggered when gathering all available transport mediums.
         *
         * Plugins that provide their own transport mediums should use this
         * event to make their medium available.
         *
         * @param array &$reportTypes An array mapping transport medium IDs with the paths to those
         *                            mediums' icons. Add your new backend's ID to this array.
         */
        Piwik::postEvent(self::GET_REPORT_TYPES_EVENT, [&$reportTypes]);

        return $reportTypes;
    }

    /**
     * @param string $reportType
     * @return array<string, string>
     * @ignore
     */
    public static function getReportFormats($reportType): array
    {
        $reportFormats = [];

        /**
         * Triggered when gathering all available scheduled report formats.
         *
         * Plugins that provide their own scheduled report format should use
         * this event to make their format available.
         *
         * @param array &$reportFormats An array mapping string IDs for each available
         *                              scheduled report format with icon paths for those
         *                              formats. Add your new format's ID to this array.
         * @param string $reportType A string ID describing how the report is sent, eg,
         *                           `'sms'` or `'email'`.
         */
        Piwik::postEvent(
            self::GET_REPORT_FORMATS_EVENT,
            [&$reportFormats, $reportType]
        );

        return $reportFormats;
    }

    /**
     * @param ScheduledReport $report
     * @return list<string>
     * @ignore
     */
    public static function getReportRecipients(array $report): array
    {
        $recipients = [];

        /**
         * Triggered when getting the list of recipients of a scheduled report.
         *
         * Plugins that provide their own scheduled report transport medium should use this event
         * to extract the list of recipients their backend's specific scheduled report
         * format.
         *
         * @param array &$recipients An array of strings describing each of the scheduled
         *                           reports recipients. Can be, for example, a list of email
         *                           addresses or phone numbers or whatever else your plugin
         *                           uses.
         * @param string $reportType A string ID describing how the report is sent, eg,
         *                           `'sms'` or `'email'`.
         * @param array $report An array describing the scheduled report that is being
         *                      generated.
         */
        Piwik::postEvent(self::GET_REPORT_RECIPIENTS_EVENT, [&$recipients, $report['type'], $report]);

        return $recipients;
    }

    /**
     * @param int|string|false|null $idSegment
     * @return array<string, mixed>|null
     * @phpstan-return StoredSegment|null
     * @ignore
     */
    public static function getSegment($idSegment)
    {
        if (self::isSegmentEditorActivated() && !empty($idSegment)) {
            $segment = APISegmentEditor::getInstance()->get((int)$idSegment);

            if ($segment) {
                // segment name is returned sanitized
                $segment['name'] = Common::unsanitizeInputValue($segment['name']);
                return $segment;
            }
        }

        return null;
    }

    /**
     * @ignore
     */
    public static function isSegmentEditorActivated(): bool
    {
        return \Piwik\Plugin\Manager::getInstance()->isPluginActivated('SegmentEditor');
    }

    /**
     * @param array<string, mixed> $report
     * @param list<array<string, mixed>> $processedReports
     * @return list<mixed>
     * @phpstan-param ScheduledReport $report
     */
    private function getAttachments(ReportRenderer $reportRenderer, array $report, array $processedReports, ?string $prettyDate): array
    {
        return $reportRenderer->getAttachments($report, $processedReports, $prettyDate);
    }

    /**
     * @param int|string|null $idSite
     */
    private function checkUserHasViewPermission(string $login, $idSite): void
    {
        if (empty($idSite)) {
            return;
        }

        $idSitesUserHasAccess = SitesManagerApi::getInstance()->getSitesIdWithAtLeastViewAccess($login);

        if (
            empty($idSitesUserHasAccess)
            || !in_array($idSite, $idSitesUserHasAccess)
        ) {
            throw new NoAccessException(Piwik::translate('General_ExceptionPrivilege', ["'view'"]));
        }
    }

    private function checkDateAndPeriodCombination(string $date, string $period): void
    {
        if ('range' === $period) {
            Period::checkDateFormat($date);

            return;
        }

        if (Period::isMultiplePeriod($date, $period)) {
            throw new Http\BadRequestException("This API method does not support multiple periods.");
        }

        Date::factory($date);
    }
}
