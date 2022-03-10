<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\ImageGraph\ImageGraph;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
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

        $view->countWebsites      = count(APISitesManager::getInstance()->getSitesIdWithAtLeastViewAccess());

        // get report types
        $reportTypes = API::getReportTypes();
        $reportTypeOptions = array();
        foreach ($reportTypes as $reportType => $icon) {
            $reportTypeOptions[$reportType] = mb_strtoupper($reportType);
        }
        $view->reportTypes = $reportTypes;
        $view->reportTypeOptions = $reportTypeOptions;
        $view->defaultReportType = self::DEFAULT_REPORT_TYPE;
        $view->defaultReportFormat = ScheduledReports::DEFAULT_REPORT_FORMAT;
        $view->defaultEvolutionPeriodN = ImageGraph::getDefaultGraphEvolutionLastPeriods();
        $view->displayFormats = ScheduledReports::getDisplayFormats();

        $view->paramPeriods = [];
        foreach (Piwik::$idPeriods as $label => $id) {
            if ($label === 'range') {
                continue;
            }

            $view->paramPeriods[$label] = Piwik::translate('Intl_Period' . ucfirst($label));
        }

        $reportsByCategoryByType = array();
        $reportFormatsByReportTypeOptions = array();
        $reportFormatsByReportType = array();
        $allowMultipleReportsByReportType = array();
        foreach ($reportTypes as $reportType => $reportTypeIcon) {
            // get report formats
            $reportFormatsByReportType[$reportType] = API::getReportFormats($reportType);
            $reportFormatsByReportTypeOptions[$reportType] = $reportFormatsByReportType[$reportType];
            foreach ($reportFormatsByReportTypeOptions[$reportType] as $type => $icon) {
                $reportFormatsByReportTypeOptions[$reportType][$type] = mb_strtoupper($type);
            }
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
        $view->reportFormatsByReportTypeOptions = $reportFormatsByReportTypeOptions;
        $view->allowMultipleReportsByReportType = $allowMultipleReportsByReportType;

        $reports = array();
        $reportsById = array();
        if (!Piwik::isUserIsAnonymous()) {
            $reports = API::getInstance()->getReports($this->idSite, $period = false, $idReport = false, $ifSuperUserReturnOnlySuperUserReports = true);
            foreach ($reports as &$report) {
                $report['evolutionPeriodFor'] = $report['evolution_graph_within_period'] ? 'each' : 'prev';
                $report['evolutionPeriodN'] = (int) $report['evolution_graph_period_n'] ?: ImageGraph::getDefaultGraphEvolutionLastPeriods();
                $report['periodParam'] = $report['period_param'];

                $report['recipients'] = API::getReportRecipients($report);
                $reportsById[$report['idreport']] = $report;
            }
        }
        $view->reports = $reports;
        $view->reportsJSON = json_encode($reportsById);

        $view->downloadOutputType = API::OUTPUT_INLINE;

        $view->periods = ScheduledReports::getPeriodToFrequency();
        $view->defaultPeriod = ScheduledReports::DEFAULT_PERIOD;
        $view->defaultHour = ScheduledReports::DEFAULT_HOUR;
        $view->periodTranslations = ScheduledReports::getPeriodFrequencyTranslations();

        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();

        $view->segmentEditorActivated = false;
        if (API::isSegmentEditorActivated()) {

            $savedSegmentsById = array(
                '' => Piwik::translate('SegmentEditor_DefaultAllVisits')
            );
            $response = Request::processRequest("SegmentEditor.getAll", ['idSite' => $this->idSite], $defaultRequest = []);
            foreach ($response as $savedSegment) {
                $savedSegmentsById[$savedSegment['idsegment']] = $savedSegment['name'];
            }
            $view->savedSegmentsById = $savedSegmentsById;
            $view->segmentEditorActivated = true;
        }

        return $view->render();
    }

    public function unsubscribe()
    {
        $view = new View('@ScheduledReports/unsubscribe');
        $this->setBasicVariablesView($view);
        $view->linkTitle = Piwik::getRandomTitle();

        $token = Common::getRequestVar('token', '', 'string');

        if (empty($token)) {
            $view->error = Piwik::translate('ScheduledReports_NoTokenProvided');
            return $view->render();
        }

        $subscriptionModel = new SubscriptionModel();
        $subscription      = $subscriptionModel->getSubscription($token);

        $report = Access::doAsSuperUser(function() use ($subscription) {
            $reports = Request::processRequest('ScheduledReports.getReports', array(
                'idReport'    => $subscription['idreport'],
            ));
            return reset($reports);
        });

        if (empty($subscription)) {
            $view->error = Piwik::translate('ScheduledReports_NoSubscriptionFound');
            return $view->render();
        }

        $confirm = Common::getRequestVar('confirm', '', 'string');

        $view->reportName = $report['description'];

        $nonce = Common::getRequestVar('nonce', '', 'string');

        if (!empty($confirm) && Nonce::verifyNonce('Report.Unsubscribe', $nonce)) {
            Nonce::discardNonce('Report.Unsubscribe');
            $subscriptionModel->unsubscribe($token);
            $view->success = true;
        } else {
            $view->nonce = Nonce::getNonce('Report.Unsubscribe');
        }

        return $view->render();
    }
}
