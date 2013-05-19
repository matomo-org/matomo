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
 *
 * @package Piwik_PDFReports
 */
class Piwik_PDFReports_Controller extends Piwik_Controller
{
    const DEFAULT_REPORT_TYPE = Piwik_PDFReports::EMAIL_TYPE;

    public function index()
    {
        $view = Piwik_View::factory('index');
        $this->setGeneralVariablesView($view);

        $view->countWebsites = count(Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess());

        // get report types
        $reportTypes = Piwik_PDFReports_API::getReportTypes();
        $view->reportTypes = $reportTypes;
        $view->defaultReportType = self::DEFAULT_REPORT_TYPE;
        $view->defaultReportFormat = Piwik_PDFReports::DEFAULT_REPORT_FORMAT;

        $reportsByCategoryByType = array();
        $reportFormatsByReportType = array();
        $allowMultipleReportsByReportType = array();
        foreach ($reportTypes as $reportType => $reportTypeIcon) {
            // get report formats
            $reportFormatsByReportType[$reportType] = Piwik_PDFReports_API::getReportFormats($reportType);
            $allowMultipleReportsByReportType[$reportType] = Piwik_PDFReports_API::allowMultipleReports($reportType);

            // get report metadata
            $reportsByCategory = array();
            $availableReportMetadata = Piwik_PDFReports_API::getReportMetadata($this->idSite, $reportType);
            foreach ($availableReportMetadata as $reportMetadata) {
                $reportsByCategory[$reportMetadata['category']][] = $reportMetadata;
            }
            $reportsByCategoryByType[$reportType] = $reportsByCategory;
        }
        $view->reportsByCategoryByReportType = $reportsByCategoryByType;
        $view->reportFormatsByReportType = $reportFormatsByReportType;
        $view->allowMultipleReportsByReportType = $allowMultipleReportsByReportType;

        $reports = array();
        $reportsById = array();
        if (!Piwik::isUserIsAnonymous()) {
            $reports = Piwik_PDFReports_API::getInstance()->getReports($this->idSite, $period = false, $idReport = false, $ifSuperUserReturnOnlySuperUserReports = true);
            foreach ($reports as &$report) {
                $report['recipients'] = Piwik_PDFReports_API::getReportRecipients($report);
                $reportsById[$report['idreport']] = $report;
            }
        }
        $view->reports = $reports;
        $view->reportsJSON = Piwik_Common::json_encode($reportsById);

        $view->downloadOutputType = Piwik_PDFReports_API::OUTPUT_INLINE;

        $view->periods = Piwik_PDFReports::getPeriodToFrequency();
        $view->defaultPeriod = Piwik_PDFReports::DEFAULT_PERIOD;
        $view->defaultHour = Piwik_PDFReports::DEFAULT_HOUR;

        $view->language = Piwik_LanguagesManager::getLanguageCodeForCurrentUser();

        $view->segmentEditorActivated = false;
        if (Piwik_PDFReports_API::isSegmentEditorActivated()) {

            $savedSegmentsById = array();
            foreach (Piwik_SegmentEditor_API::getInstance()->getAll($this->idSite) as $savedSegment) {
                $savedSegmentsById[$savedSegment['idsegment']] = $savedSegment['name'];
            }
            $view->savedSegmentsById = $savedSegmentsById;
            $view->segmentEditorActivated = true;
        }

        echo $view->render();
    }
}
