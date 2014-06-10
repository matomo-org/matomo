<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    const DEFAULT_REPORT_TYPE = ScheduledReports::EMAIL_TYPE;

    public function index()
    {
        $view = new View('@ScheduledReports/index');
        $this->setGeneralVariablesView($view);

        $view->countWebsites = count(APISitesManager::getInstance()->getSitesIdWithAtLeastViewAccess());

        // get report types
        $reportTypes = API::getReportTypes();
        $view->reportTypes = $reportTypes;
        $view->defaultReportType = self::DEFAULT_REPORT_TYPE;
        $view->defaultReportFormat = ScheduledReports::DEFAULT_REPORT_FORMAT;
        $view->displayFormats = ScheduledReports::getDisplayFormats();

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

        $view->periods = ScheduledReports::getPeriodToFrequency();
        $view->defaultPeriod = ScheduledReports::DEFAULT_PERIOD;
        $view->defaultHour = ScheduledReports::DEFAULT_HOUR;

        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();

        $view->segmentEditorActivated = false;
        if (API::isSegmentEditorActivated()) {

            $savedSegmentsById = array();
            foreach (APISegmentEditor::getInstance()->getAll($this->idSite) as $savedSegment) {
                $savedSegmentsById[$savedSegment['idsegment']] = $savedSegment['name'];
            }
            $view->savedSegmentsById = $savedSegmentsById;
            $view->segmentEditorActivated = true;
        }

        return $view->render();
    }
}
