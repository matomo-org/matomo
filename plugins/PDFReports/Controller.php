<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package PDFReports
 */
namespace Piwik\Plugins\PDFReports;

use Piwik\Piwik;
use Piwik\Common;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\PDFReports\API;
use Piwik\View;
use Piwik\Plugins\PDFReports\PDFReports;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

/**
 *
 * @package PDFReports
 */
class Controller extends \Piwik\Controller
{
    const DEFAULT_REPORT_TYPE = PDFReports::EMAIL_TYPE;

    public function index()
    {
        $view = new View('@PDFReports/index');
        $this->setGeneralVariablesView($view);

        $view->countWebsites = count(SitesManagerAPI::getInstance()->getSitesIdWithAtLeastViewAccess());

        // get report types
        $reportTypes = API::getReportTypes();
        $view->reportTypes = $reportTypes;
        $view->defaultReportType = self::DEFAULT_REPORT_TYPE;
        $view->defaultReportFormat = PDFReports::DEFAULT_REPORT_FORMAT;

        $reportsByCategoryByType = array();
        $reportFormatsByReportType = array();
        $allowMultipleReportsByReportType = array();
        foreach ($reportTypes as $reportType => $reportTypeIcon) {
            // get report formats
            $reportFormatsByReportType[$reportType] = API::getReportFormats($reportType);
            $allowMultipleReportsByReportType[$reportType] = API::allowMultipleReports($reportType);

            // get report metadata
            $reportsByCategory = array();
            $availableReportMetadata = API::getReportMetadata($this->idSite, $reportType);
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
            $reports = API::getInstance()->getReports($this->idSite, $period = false, $idReport = false, $ifSuperUserReturnOnlySuperUserReports = true);
            foreach ($reports as &$report) {
                $report['recipients'] = API::getReportRecipients($report);
                $reportsById[$report['idreport']] = $report;
            }
        }
        $view->reports = $reports;
        $view->reportsJSON = Common::json_encode($reportsById);

        $view->downloadOutputType = API::OUTPUT_INLINE;

        $view->periods = PDFReports::getPeriodToFrequency();
        $view->defaultPeriod = PDFReports::DEFAULT_PERIOD;
        $view->defaultHour = PDFReports::DEFAULT_HOUR;

        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();

        $view->segmentEditorActivated = false;
        if (API::isSegmentEditorActivated()) {

            $savedSegmentsById = array();
            foreach (SegmentEditorAPI::getInstance()->getAll($this->idSite) as $savedSegment) {
                $savedSegmentsById[$savedSegment['idsegment']] = $savedSegment['name'];
            }
            $view->savedSegmentsById = $savedSegmentsById;
            $view->segmentEditorActivated = true;
        }

        echo $view->render();
    }
}
