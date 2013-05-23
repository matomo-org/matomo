<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_PDFReports
 */

/**
 * The PDFReports API lets you manage Scheduled Email reports, as well as generate, download or email any existing report.
 *
 * "generateReport" will generate the requested report (for a specific date range, website and in the requested language).
 * "sendEmailReport" will send the report by email to the recipients specified for this report.
 *
 * You can also get the list of all existing reports via "getReports", create new reports via "addReport",
 * or manage existing reports with "updateReport" and "deleteReport".
 * See also the documentation about <a href='http://piwik.org/docs/email-reports/' target='_blank'>Scheduled Email reports</a> in Piwik.
 *
 * @package Piwik_PDFReports
 */
class Piwik_PDFReports_API
{
    const VALIDATE_PARAMETERS_EVENT = 'PDFReports.validateReportParameters';
    const GET_REPORT_PARAMETERS_EVENT = 'PDFReports.getReportParameters';
    const GET_REPORT_METADATA_EVENT = 'PDFReports.getReportMetadata';
    const GET_REPORT_TYPES_EVENT = 'PDFReports.getReportTypes';
    const GET_REPORT_FORMATS_EVENT = 'PDFReports.getReportFormats';
    const GET_RENDERER_INSTANCE_EVENT = 'PDFReports.getRendererInstance';
    const PROCESS_REPORTS_EVENT = 'PDFReports.processReports';
    const GET_REPORT_RECIPIENTS_EVENT = 'PDFReports.getReportRecipients';
    const ALLOW_MULTIPLE_REPORTS_EVENT = 'PDFReports.allowMultipleReports';
    const SEND_REPORT_EVENT = 'PDFReports.sendReport';

    const OUTPUT_DOWNLOAD = 1;
    const OUTPUT_SAVE_ON_DISK = 2;
    const OUTPUT_INLINE = 3;
    const OUTPUT_RETURN = 4;

    const REPORT_TYPE_INFO_KEY = 'reportType';
    const OUTPUT_TYPE_INFO_KEY = 'outputType';
    const ID_SITE_INFO_KEY = 'idSite';
    const REPORT_KEY = 'report';
    const REPORT_CONTENT_KEY = 'contents';
    const FILENAME_KEY = 'filename';
    const PRETTY_DATE_KEY = 'prettyDate';
    const REPORT_SUBJECT_KEY = 'reportSubject';
    const REPORT_TITLE_KEY = 'reportTitle';
    const ADDITIONAL_FILES_KEY = 'additionalFiles';

    const REPORT_TRUNCATE = 23;

    static private $instance = null;

    /**
     * @return Piwik_PDFReports_API
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Creates a new report and schedules it.
     *
     * @param int $idSite
     * @param string $description Report description
     * @param string $period Schedule frequency: day, week or month
     * @param int $hour Hour (0-23) when the report should be sent
     * @param string $reportType 'email' or any other format provided via the PDFReports.getReportTypes hook
     * @param string $reportFormat 'pdf', 'html' or any other format provided via the PDFReports.getReportFormats hook
     * @param array $reports array of reports
     * @param array $parameters array of parameters
     * @param int $idSegment Segment Identifier
     *
     * @return int idReport generated
     */
    public function addReport($idSite, $description, $period, $hour, $reportType, $reportFormat, $reports, $parameters, $idSegment = false)
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasViewAccess($idSite);

        $currentUser = Piwik::getCurrentUserLogin();
        self::ensureLanguageSetForUser($currentUser);

        self::validateCommonReportAttributes($period, $hour, $description, $idSegment, $reportType, $reportFormat);

        // report parameters validations
        $parameters = self::validateReportParameters($reportType, $parameters);

        // validation of requested reports
        $reports = self::validateRequestedReports($idSite, $reportType, $reports);

        $db = Zend_Registry::get('db');
        $idReport = $db->fetchOne("SELECT max(idreport) + 1 FROM " . Piwik_Common::prefixTable('report'));

        if ($idReport == false) {
            $idReport = 1;
        }

        $db->insert(Piwik_Common::prefixTable('report'),
            array(
                 'idreport'    => $idReport,
                 'idsite'      => $idSite,
                 'login'       => $currentUser,
                 'description' => $description,
                 'idsegment'   => $idSegment,
                 'period'      => $period,
                 'hour'        => $hour,
                 'type'        => $reportType,
                 'format'      => $reportFormat,
                 'parameters'  => $parameters,
                 'reports'     => $reports,
                 'ts_created'  => Piwik_Date::now()->getDatetime(),
                 'deleted'     => 0,
            ));

        return $idReport;
    }

    private static function ensureLanguageSetForUser($currentUser)
    {
        $lang = Piwik_LanguagesManager_API::getInstance()->getLanguageForUser($currentUser);
        if (empty($lang)) {
            Piwik_LanguagesManager_API::getInstance()->setLanguageForUser($currentUser, Piwik_LanguagesManager::getLanguageCodeForCurrentUser());
        }
    }

    /**
     * Updates an existing report.
     *
     * @see addReport()
     */
    public function updateReport($idReport, $idSite, $description, $period, $hour, $reportType, $reportFormat, $reports, $parameters, $idSegment = false)
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasViewAccess($idSite);

        $pdfReports = $this->getReports($idSite, $periodSearch = false, $idReport);
        $report = reset($pdfReports);
        $idReport = $report['idreport'];

        $currentUser = Piwik::getCurrentUserLogin();
        self::ensureLanguageSetForUser($currentUser);

        self::validateCommonReportAttributes($period, $hour, $description, $idSegment, $reportType, $reportFormat);

        // report parameters validations
        $parameters = self::validateReportParameters($reportType, $parameters);

        // validation of requested reports
        $reports = self::validateRequestedReports($idSite, $reportType, $reports);

        Zend_Registry::get('db')->update(Piwik_Common::prefixTable('report'),
            array(
                 'description' => $description,
                 'idsegment'   => $idSegment,
                 'period'      => $period,
                 'hour'        => $hour,
                 'type'        => $reportType,
                 'format'      => $reportFormat,
                 'parameters'  => $parameters,
                 'reports'     => $reports,
            ),
            "idreport = '$idReport'"
        );

        self::$cache = array();
    }

    /**
     * Deletes a specific report
     *
     * @param int $idReport
     */
    public function deleteReport($idReport)
    {
        $pdfReports = $this->getReports($idSite = false, $periodSearch = false, $idReport);
        $report = reset($pdfReports);
        Piwik::checkUserIsSuperUserOrTheUser($report['login']);

        Zend_Registry::get('db')->update(Piwik_Common::prefixTable('report'),
            array(
                 'deleted' => 1,
            ),
            "idreport = '$idReport'"
        );
        self::$cache = array();
    }

    // static cache storing reports
    public static $cache = array();

    /**
     * Returns the list of reports matching the passed parameters
     *
     * @param int $idSite If specified, will filter reports that belong to a specific idsite
     * @param string $period If specified, will filter reports that are scheduled for this period (day,week,month)
     * @param int $idReport If specified, will filter the report that has the given idReport
     * @param int $idSegment If specified, will filter the report that has the given idSegment
     * @return array
     * @throws Exception if $idReport was specified but the report wasn't found
     */
    public function getReports($idSite = false, $period = false, $idReport = false, $ifSuperUserReturnOnlySuperUserReports = false, $idSegment = false)
    {
        Piwik::checkUserHasSomeViewAccess();
        $cacheKey = (int)$idSite . '.' . (string)$period . '.' . (int)$idReport . '.' . (int)$ifSuperUserReturnOnlySuperUserReports;
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $sqlWhere = '';
        $bind = array();

        // Super user gets all reports back, other users only their own
        if (!Piwik::isUserIsSuperUser()
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
            $sqlWhere .= " AND " . Piwik_Common::prefixTable('site') . ".idsite = ?";
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
        $reports = Piwik_FetchAll("SELECT *
								FROM " . Piwik_Common::prefixTable('report') . "
									JOIN " . Piwik_Common::prefixTable('site') . "
									USING (idsite)
								WHERE deleted = 0
									$sqlWhere", $bind);
        // When a specific report was requested and not found, throw an error
        if ($idReport !== false
            && empty($reports)
        ) {
            throw new Exception("Requested report couldn't be found.");
        }

        foreach ($reports as &$report) {
            // decode report parameters
            $report['parameters'] = Piwik_Common::json_decode($report['parameters'], true);

            // decode report list
            $report['reports'] = Piwik_Common::json_decode($report['reports'], true);
        }

        // static cache
        self::$cache[$cacheKey] = $reports;

        return $reports;
    }

    /**
     * Generates a report file.
     *
     * @param int $idReport ID of the report to generate.
     * @param string $date YYYY-MM-DD
     * @param bool|false|string $language If not passed, will use default language.
     * @param bool|false|int $outputType 1 = download report, 2 = save report to disk, 3 = output report in browser, 4 = return report content to caller, defaults to download
     * @param bool|false|string $period Defaults to 'day'. If not specified, will default to the report's period set when creating the report
     * @param bool|false|string $reportFormat 'pdf', 'html' or any other format provided via the PDFReports.getReportFormats hook
     * @param bool|false|array $parameters array of parameters
     * @return array|void
     */
    public function generateReport($idReport, $date, $language = false, $outputType = false, $period = false, $reportFormat = false, $parameters = false)
    {
        Piwik::checkUserIsNotAnonymous();

        // load specified language
        if (empty($language)) {
            $language = Piwik_Translate::getInstance()->getLanguageDefault();
        }

        Piwik_Translate::getInstance()->reloadLanguage($language);

        $reports = $this->getReports($idSite = false, $_period = false, $idReport);
        $report = reset($reports);

        $idSite = $report['idsite'];
        $reportType = $report['type'];

        // override report period
        if (empty($period)) {
            $period = $report['period'];
        }

        // override report format
        if (!empty($reportFormat)) {
            self::validateReportFormat($reportType, $reportFormat);
            $report['format'] = $reportFormat;
        } else {
            $reportFormat = $report['format'];
        }

        // override and/or validate report parameters
        $report['parameters'] = Piwik_Common::json_decode(
            self::validateReportParameters($reportType, empty($parameters) ? $report['parameters'] : $parameters),
            true
        );

        // available reports
        $availableReportMetadata = Piwik_API_API::getInstance()->getReportMetadata($idSite);

        // we need to lookup which reports metadata are registered in this report
        $reportMetadata = array();
        foreach ($availableReportMetadata as $metadata) {
            if (in_array($metadata['uniqueId'], $report['reports'])) {
                $reportMetadata[] = $metadata;
            }
        }

        // the report will be rendered with the first 23 rows and will aggregate other rows in a summary row
        // 23 rows table fits in one portrait page
        $initialFilterTruncate = Piwik_Common::getRequestVar('filter_truncate', false);
        $_GET['filter_truncate'] = self::REPORT_TRUNCATE;

        $prettyDate = null;
        $processedReports = array();
        $segment = self::getSegment($report['idsegment']);
        foreach ($reportMetadata as $action) {
            $apiModule = $action['module'];
            $apiAction = $action['action'];
            $apiParameters = array();
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

                    // when a view/admin user created a report, workaround the fact that "Super User"
                    // is enforced in Scheduled tasks, and ensure Multisites.getAll only return the websites that this user can access
                    $userLogin = $report['login'];
                    if (!empty($userLogin)
                        && $userLogin != Piwik::getSuperUserLogin()
                    ) {
                        $_GET['_restrictSitesToLogin'] = $userLogin;
                    }
                }
            }

            $processedReport = Piwik_API_API::getInstance()->getProcessedReport(
                $idSite, $period, $date, $apiModule, $apiAction,
                $segment != null ? urlencode($segment['definition']) : false,
                $apiParameters, $idGoal = false, $language
            );

            $processedReport['segment'] = $segment;

            // TODO add static method getPrettyDate($period, $date) in Piwik_Period
            $prettyDate = $processedReport['prettyDate'];

            if ($mustRestoreGET) {
                $_GET = $mustRestoreGET;
            }

            $processedReports[] = $processedReport;
        }

        // restore filter truncate parameter value
        if ($initialFilterTruncate !== false) {
            $_GET['filter_truncate'] = $initialFilterTruncate;
        }

        $notificationInfo = array(
            self::REPORT_TYPE_INFO_KEY => $reportType,
            self::OUTPUT_TYPE_INFO_KEY => $outputType,
            self::REPORT_KEY           => $report,
        );

        // allow plugins to alter processed reports
        Piwik_PostEvent(
            self::PROCESS_REPORTS_EVENT,
            $processedReports,
            $notificationInfo
        );

        // retrieve report renderer instance
        $reportRenderer = null;
        Piwik_PostEvent(
            self::GET_RENDERER_INSTANCE_EVENT,
            $reportRenderer,
            $notificationInfo
        );

        // init report renderer
        $reportRenderer->setLocale($language);

        // render report
        $description = str_replace(array("\r", "\n"), ' ', $report['description']);

        list($reportSubject, $reportTitle) = self::getReportSubjectAndReportTitle(Piwik_Site::getNameFor($idSite), $report['reports']);
        $filename = "$reportTitle - $prettyDate - $description";

        $reportRenderer->renderFrontPage($reportTitle, $prettyDate, $description, $reportMetadata, $segment);
        array_walk($processedReports, array($reportRenderer, 'renderReport'));

        switch ($outputType) {
            case self::OUTPUT_SAVE_ON_DISK:
                $outputFilename = strtoupper($reportFormat) . ' ' . ucfirst($reportType) . ' Report - ' . $idReport . '.' . $date . '.' . $idSite . '.' . $language;
                $outputFilename = $reportRenderer->sendToDisk($outputFilename);

                $additionalFiles = array();
                if ($reportRenderer instanceof Piwik_ReportRenderer_Html) {
                    foreach ($processedReports as &$report) {
                        if ($report['displayGraph']) {
                            $additionalFile = array();
                            $additionalFile['filename'] = $report['metadata']['name'] . '.png';
                            $additionalFile['cid'] = $report['metadata']['uniqueId'];
                            $additionalFile['content'] =
                                Piwik_ReportRenderer::getStaticGraph(
                                    $report['metadata'],
                                    Piwik_ReportRenderer_Html::IMAGE_GRAPH_WIDTH,
                                    Piwik_ReportRenderer_Html::IMAGE_GRAPH_HEIGHT,
                                    $report['evolutionGraph'],
                                    $segment
                                );
                            $additionalFile['mimeType'] = 'image/png';
                            $additionalFile['encoding'] = Zend_Mime::ENCODING_BASE64;

                            $additionalFiles[] = $additionalFile;
                        }
                    }
                }

                return array(
                    $outputFilename,
                    $prettyDate,
                    $reportSubject,
                    $reportTitle,
                    $additionalFiles,
                );
                break;

            case self::OUTPUT_INLINE:

                $reportRenderer->sendToBrowserInline($filename);
                break;

            case self::OUTPUT_RETURN:

                return $reportRenderer->getRenderedReport();
                break;

            default:
            case self::OUTPUT_DOWNLOAD:
                $reportRenderer->sendToBrowserDownload($filename);
                break;
        }
    }

    public function sendReport($idReport, $period = false, $date = false)
    {
        Piwik::checkUserIsNotAnonymous();

        $reports = $this->getReports($idSite = false, false, $idReport);
        $report = reset($reports);

        if ($report['period'] == 'never') {
            $report['period'] = 'day';
        }

        if (!empty($period)) {
            $report['period'] = $period;
        }

        if (empty($date)) {
            $date = Piwik_Date::now()->subPeriod(1, $report['period'])->toString();
        }

        $language = Piwik_LanguagesManager_API::getInstance()->getLanguageForUser($report['login']);

        // generate report
        list($outputFilename, $prettyDate, $reportSubject, $reportTitle, $additionalFiles) =
            $this->generateReport(
                $idReport,
                $date,
                $language,
                self::OUTPUT_SAVE_ON_DISK,
                $report['period']
            );

        if (!file_exists($outputFilename)) {
            throw new Exception("The report file wasn't found in $outputFilename");
        }

        $filename = basename($outputFilename);
        $handle = fopen($outputFilename, "r");
        $contents = fread($handle, filesize($outputFilename));
        fclose($handle);

        $notificationObject = null;
        Piwik_PostEvent(
            self::SEND_REPORT_EVENT,
            $notificationObject,
            $notificationInfo = array(
                self::REPORT_TYPE_INFO_KEY => $report['type'],
                self::REPORT_KEY           => $report,
                self::REPORT_CONTENT_KEY   => $contents,
                self::FILENAME_KEY         => $filename,
                self::PRETTY_DATE_KEY      => $prettyDate,
                self::REPORT_SUBJECT_KEY   => $reportSubject,
                self::REPORT_TITLE_KEY     => $reportTitle,
                self::ADDITIONAL_FILES_KEY => $additionalFiles,
            )
        );

        // Update flag in DB
        Zend_Registry::get('db')->update(Piwik_Common::prefixTable('report'),
            array('ts_last_sent' => Piwik_Date::now()->getDatetime()),
            "idreport = " . $report['idreport']
        );

        // If running from piwik.php with debug, do not delete the PDF after sending the email
        if (!isset($GLOBALS['PIWIK_TRACKER_DEBUG']) || !$GLOBALS['PIWIK_TRACKER_DEBUG']) {
            @chmod($outputFilename, 0600);
        }
    }

    private static function getReportSubjectAndReportTitle($websiteName, $reports)
    {
        // if the only report is "All websites", we don't display the site name
        $reportTitle = Piwik_Translate('General_Website') . " " . $websiteName;
        $reportSubject = $websiteName;
        if (count($reports) == 1
            && $reports[0] == 'MultiSites_getAll'
        ) {
            $reportSubject = Piwik_Translate('General_MultiSitesSummary');
            $reportTitle = $reportSubject;
        }

        return array($reportSubject, $reportTitle);
    }

    private static function validateReportParameters($reportType, $parameters)
    {
        // get list of valid parameters
        $availableParameters = array();

        $notificationInfo = array(
            self::REPORT_TYPE_INFO_KEY => $reportType
        );

        Piwik_PostEvent(self::GET_REPORT_PARAMETERS_EVENT, $availableParameters, $notificationInfo);

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

        // delegate report parameter validation
        Piwik_PostEvent(self::VALIDATE_PARAMETERS_EVENT, $parameters, $notificationInfo);

        return Piwik_Common::json_encode($parameters);
    }

    private static function validateAndTruncateDescription(&$description)
    {
        $description = substr($description, 0, 250);
    }

    private static function validateRequestedReports($idSite, $reportType, $requestedReports)
    {
        if (!self::allowMultipleReports($reportType)) {
            //sms can only contain one report, we silently discard all but the first
            $requestedReports = array_slice($requestedReports, 0, 1);
        }

        // retrieve available reports
        $availableReportMetadata = self::getReportMetadata($idSite, $reportType);

        $availableReportIds = array();
        foreach ($availableReportMetadata as $reportMetadata) {
            $availableReportIds[] = $reportMetadata['uniqueId'];
        }

        foreach ($requestedReports as $report) {
            if (!in_array($report, $availableReportIds)) {
                throw new Exception("Report $report is unknown or not available for report type '$reportType'.");
            }
        }

        return Piwik_Common::json_encode($requestedReports);
    }

    private static function validateCommonReportAttributes($period, $hour, &$description, &$idSegment, $reportType, $reportFormat)
    {
        self::validateReportPeriod($period);
        self::validateReportHour($hour);
        self::validateAndTruncateDescription($description);
        self::validateIdSegment($idSegment);
        self::validateReportType($reportType);
        self::validateReportFormat($reportType, $reportFormat);
    }

    private static function validateReportPeriod($period)
    {
        $availablePeriods = array('day', 'week', 'month', 'never');
        if (!in_array($period, $availablePeriods)) {
            throw new Exception('Period schedule must be one of the following: ' . implode(', ', $availablePeriods));
        }
    }

    private static function validateReportHour($hour)
    {
        if (!is_numeric($hour) || $hour < 0 || $hour > 23) {
            throw new Exception('Invalid hour schedule. Should be anything from 0 to 23 inclusive.');
        }
    }

    private static function validateIdSegment(&$idSegment)
    {
        if (empty($idSegment) || (is_numeric($idSegment) && $idSegment == 0)) {

            $idSegment = null;

        } elseif (!is_numeric($idSegment)) {

            throw new Exception('Invalid segment identifier. Should be an integer.');

        } elseif (self::getSegment($idSegment) == null) {

            throw new Exception('Segment with id ' . $idSegment . ' does not exist or SegmentEditor is not activated.');
        }
    }

    private static function validateReportType($reportType)
    {
        $reportTypes = array_keys(self::getReportTypes());

        if (!in_array($reportType, $reportTypes)) {
            throw new Exception(
                'Report type \'' . $reportType . '\' not valid. Try one of the following ' . implode(', ', $reportTypes)
            );
        }
    }

    private static function validateReportFormat($reportType, $reportFormat)
    {
        $reportFormats = array_keys(self::getReportFormats($reportType));

        if (!in_array($reportFormat, $reportFormats)) {
            throw new Exception(
                Piwik_TranslateException(
                    'General_ExceptionInvalidReportRendererFormat',
                    array($reportFormat, implode(', ', $reportFormats))
                )
            );
        }
    }

    /**
     * @ignore
     */
    static public function getReportMetadata($idSite, $reportType)
    {
        $notificationInfo = array(
            self::REPORT_TYPE_INFO_KEY => $reportType,
            self::ID_SITE_INFO_KEY     => $idSite,
        );

        // retrieve available reports
        $availableReportMetadata = array();
        Piwik_PostEvent(
            self::GET_REPORT_METADATA_EVENT,
            $availableReportMetadata,
            $notificationInfo
        );

        return $availableReportMetadata;
    }

    /**
     * @ignore
     */
    static public function allowMultipleReports($reportType)
    {
        $allowMultipleReports = null;
        Piwik_PostEvent(
            self::ALLOW_MULTIPLE_REPORTS_EVENT,
            $allowMultipleReports,
            $notificationInfo = array(
                self::REPORT_TYPE_INFO_KEY => $reportType,
            )
        );
        return $allowMultipleReports;
    }

    /**
     * @ignore
     */
    static public function getReportTypes()
    {
        $reportTypes = array();
        Piwik_PostEvent(self::GET_REPORT_TYPES_EVENT, $reportTypes);

        return $reportTypes;
    }

    /**
     * @ignore
     */
    static public function getReportFormats($reportType)
    {
        $reportFormats = array();

        Piwik_PostEvent(
            self::GET_REPORT_FORMATS_EVENT,
            $reportFormats,
            $notificationInfo = array(
                self::REPORT_TYPE_INFO_KEY => $reportType
            )
        );

        return $reportFormats;
    }

    /**
     * @ignore
     */
    static public function getReportRecipients($report)
    {
        $notificationInfo = array(
            self::REPORT_TYPE_INFO_KEY => $report['type'],
            self::REPORT_KEY           => $report,
        );

        // retrieve report renderer instance
        $recipients = array();
        Piwik_PostEvent(self::GET_REPORT_RECIPIENTS_EVENT, $recipients, $notificationInfo);

        return $recipients;
    }

    /**
     * @ignore
     */
    static public function getSegment($idSegment)
    {
        if (self::isSegmentEditorActivated() && !empty($idSegment)) {

            $segment = Piwik_SegmentEditor_API::getInstance()->get($idSegment);

            if ($segment) {
                return $segment;
            }
        }

        return null;
    }

    /**
     * @ignore
     */
    public static function isSegmentEditorActivated()
    {
        return Piwik_PluginsManager::getInstance()->isPluginActivated('SegmentEditor');
    }
}
