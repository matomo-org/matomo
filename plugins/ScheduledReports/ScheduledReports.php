<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log;
use Piwik\Option;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Model as UserModel;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\ReportRenderer;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\SettingsPiwik;
use Piwik\Tracker;
use Piwik\View;

/**
 *
 */
class ScheduledReports extends \Piwik\Plugin
{

    const DISPLAY_FORMAT_GRAPHS_ONLY_FOR_KEY_METRICS = 1; // Display Tables Only (Graphs only for key metrics)
    const DISPLAY_FORMAT_GRAPHS_ONLY = 2; // Display Graphs Only for all reports
    const DISPLAY_FORMAT_TABLES_AND_GRAPHS = 3; // Display Tables and Graphs for all reports
    const DISPLAY_FORMAT_TABLES_ONLY = 4; // Display only tables for all reports
    const DEFAULT_DISPLAY_FORMAT = self::DISPLAY_FORMAT_GRAPHS_ONLY_FOR_KEY_METRICS;

    const DEFAULT_REPORT_FORMAT = ReportRenderer::HTML_FORMAT;
    const DEFAULT_PERIOD = 'week';
    const DEFAULT_HOUR = '0';

    const EMAIL_ME_PARAMETER = 'emailMe';
    const EVOLUTION_GRAPH_PARAMETER = 'evolutionGraph';
    const ADDITIONAL_EMAILS_PARAMETER = 'additionalEmails';
    const DISPLAY_FORMAT_PARAMETER = 'displayFormat';
    const EMAIL_ME_PARAMETER_DEFAULT_VALUE = true;
    const EVOLUTION_GRAPH_PARAMETER_DEFAULT_VALUE = false;

    const EMAIL_TYPE = 'email';

    private static $availableParameters = array(
        self::EMAIL_ME_PARAMETER          => false,
        self::EVOLUTION_GRAPH_PARAMETER   => false,
        self::ADDITIONAL_EMAILS_PARAMETER => false,
        self::DISPLAY_FORMAT_PARAMETER    => true,
    );

    private static $managedReportTypes = array(
        self::EMAIL_TYPE => 'plugins/Morpheus/images/email.png'
    );

    private static $managedReportFormats = array(
        ReportRenderer::HTML_FORMAT => 'plugins/Morpheus/images/html_icon.png',
        ReportRenderer::PDF_FORMAT  => 'plugins/Morpheus/icons/dist/plugins/pdf.png',
        ReportRenderer::CSV_FORMAT  => 'plugins/Morpheus/images/export.png',
        ReportRenderer::TSV_FORMAT  => 'plugins/Morpheus/images/export.png',
    );

    const OPTION_KEY_LAST_SENT_DATERANGE = 'report_last_sent_daterange_';

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'           => 'getJsFiles',
            'AssetManager.getStylesheetFiles'           => 'getStylesheetFiles',
            'MobileMessaging.deletePhoneNumber'         => 'deletePhoneNumber',
            'ScheduledReports.getReportParameters'      => 'getReportParameters',
            'ScheduledReports.validateReportParameters' => 'validateReportParameters',
            'ScheduledReports.getReportMetadata'        => 'getReportMetadata',
            'ScheduledReports.getReportTypes'           => 'getReportTypes',
            'ScheduledReports.getReportFormats'         => 'getReportFormats',
            'ScheduledReports.getRendererInstance'      => 'getRendererInstance',
            'ScheduledReports.getReportRecipients'      => 'getReportRecipients',
            'ScheduledReports.processReports'           => 'processReports',
            'ScheduledReports.allowMultipleReports'     => 'allowMultipleReports',
            'ScheduledReports.sendReport'               => 'sendReport',
            'Template.reportParametersScheduledReports' => 'template_reportParametersScheduledReports',
            'UsersManager.deleteUser'                   => 'deleteUserReport',
            'UsersManager.removeSiteAccess'             => 'deleteUserReportForSites',
            'SitesManager.deleteSite.end'               => 'deleteSiteReport',
            'SegmentEditor.deactivate'                  => 'segmentDeactivation',
            'SegmentEditor.update'                      => 'segmentUpdated',
            'Translate.getClientSideTranslationKeys'    => 'getClientSideTranslationKeys',
            'Request.getRenamedModuleAndAction'         => 'renameDeprecatedModuleAndAction',
            'Db.getTablesInstalled'                     => 'getTablesInstalled'
        );
    }

    /**
     * Register the new tables, so Matomo knows about them.
     *
     * @param array $allTablesInstalled
     */
    public function getTablesInstalled(&$allTablesInstalled)
    {
        $allTablesInstalled[] = Common::prefixTable('report');
        $allTablesInstalled[] = Common::prefixTable('report_subscriptions');
    }

    public function renameDeprecatedModuleAndAction(&$module, &$action)
    {
        if($module == 'PDFReports') {
            $module = 'ScheduledReports';
        }
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "ScheduledReports_ReportSent";
        $translationKeys[] = "ScheduledReports_ReportUpdated";
        $translationKeys[] = "ScheduledReports_ReportHourWithUTC";
        $translationKeys[] = "ScheduledReports_EvolutionGraphsShowForEachInPeriod";
        $translationKeys[] = "ScheduledReports_EvolutionGraphsShowForPreviousN";
        $translationKeys[] = 'ScheduledReports_EmailSchedule';
        $translationKeys[] = 'ScheduledReports_ReportFormat';
        $translationKeys[] = 'ScheduledReports_SendReportTo';
        $translationKeys[] = 'ScheduledReports_MustBeLoggedIn';
        $translationKeys[] = 'Login_LogIn';
        $translationKeys[] = 'ScheduledReports_ThereIsNoReportToManage';
        $translationKeys[] = 'ScheduledReports_SegmentDeleted';
        $translationKeys[] = 'ScheduledReports_NoRecipients';
        $translationKeys[] = 'ScheduledReports_SendReportNow';
        $translationKeys[] = 'ScheduledReports_CreateAndScheduleReport';
        $translationKeys[] = 'ScheduledReports_DescriptionOnFirstPage';
        $translationKeys[] = 'SegmentEditor_ChooseASegment';
        $translationKeys[] = 'ScheduledReports_WeeklyScheduleHelp';
        $translationKeys[] = 'ScheduledReports_MonthlyScheduleHelp';
        $translationKeys[] = 'ScheduledReports_ReportPeriod';
        $translationKeys[] = 'ScheduledReports_ReportPeriodHelp';
        $translationKeys[] = 'ScheduledReports_ReportPeriodHelp2';
        $translationKeys[] = 'ScheduledReports_ReportHour';
        $translationKeys[] = 'ScheduledReports_ReportType';
        $translationKeys[] = 'ScheduledReports_AggregateReportsFormat';
        $translationKeys[] = 'ScheduledReports_EvolutionGraph';
        $translationKeys[] = 'ScheduledReports_EvolutionGraphsShowForPreviousN';
        $translationKeys[] = 'ScheduledReports_ReportsIncluded';
        $translationKeys[] = 'ScheduledReports_ReportIncludeNWebsites';
        $translationKeys[] = 'SegmentEditor_LoadingSegmentedDataMayTakeSomeTime';
        $translationKeys[] = 'General_Download';
        $translationKeys[] = 'ScheduledReports_Segment_Help';
        $translationKeys[] = 'SegmentEditor_AddNewSegment';
        $translationKeys[] = 'ScheduledReports_SentToMe';
        $translationKeys[] = 'ScheduledReports_AlsoSendReportToTheseEmails';
    }

    /**
     * Delete reports for the website
     */
    public function deleteSiteReport($idSite)
    {
        $idReports = API::getInstance()->getReports($idSite);

        foreach ($idReports as $report) {
            $idReport = $report['idreport'];
            API::getInstance()->deleteReport($idReport);
        }
    }

    public function getJsFiles(&$jsFiles)
    {
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/ScheduledReports/stylesheets/scheduledreports.less';
    }

    public function validateReportParameters(&$parameters, $reportType)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        $reportFormat = $parameters[self::DISPLAY_FORMAT_PARAMETER];
        $availableDisplayFormats = array_keys(self::getDisplayFormats());
        if (!in_array($reportFormat, $availableDisplayFormats)) {
            throw new Exception(
                Piwik::translate(
                // General_ExceptionInvalidAggregateReportsFormat should be named General_ExceptionInvalidDisplayFormat
                    'General_ExceptionInvalidAggregateReportsFormat',
                    array($reportFormat, implode(', ', $availableDisplayFormats))
                )
            );
        }

        // emailMe is an optional parameter
        if (!isset($parameters[self::EMAIL_ME_PARAMETER])) {
            $parameters[self::EMAIL_ME_PARAMETER] = self::EMAIL_ME_PARAMETER_DEFAULT_VALUE;
        } else {
            $parameters[self::EMAIL_ME_PARAMETER] = self::valueIsTrue($parameters[self::EMAIL_ME_PARAMETER]);
        }

        // evolutionGraph is an optional parameter
        if (!isset($parameters[self::EVOLUTION_GRAPH_PARAMETER])) {
            $parameters[self::EVOLUTION_GRAPH_PARAMETER] = self::EVOLUTION_GRAPH_PARAMETER_DEFAULT_VALUE;
        } else {
            $parameters[self::EVOLUTION_GRAPH_PARAMETER] = self::valueIsTrue($parameters[self::EVOLUTION_GRAPH_PARAMETER]);
        }

        // additionalEmails is an optional parameter
        if (isset($parameters[self::ADDITIONAL_EMAILS_PARAMETER])) {
            $parameters[self::ADDITIONAL_EMAILS_PARAMETER] = self::checkAdditionalEmails($parameters[self::ADDITIONAL_EMAILS_PARAMETER]);
        }
    }

    // based on http://www.php.net/manual/en/filter.filters.validate.php -> FILTER_VALIDATE_BOOLEAN
    private static function valueIsTrue($value)
    {
        return $value == 'true' || $value == 1 || $value == '1' || $value === true;
    }

    public function getReportMetadata(&$reportMetadata, $reportType, $idSite)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        $availableReportMetadata = \Piwik\Plugins\API\API::getInstance()->getReportMetadata($idSite);

        $filteredReportMetadata = array();
        foreach ($availableReportMetadata as $reportMetadata) {
            // removing reports from the API category and MultiSites.getOne
            if (
                $reportMetadata['category'] == 'API' ||
                $reportMetadata['category'] == Piwik::translate('General_MultiSitesSummary') && $reportMetadata['name'] == Piwik::translate('General_SingleWebsitesDashboard')
            ) {
                continue;
            }

            $filteredReportMetadata[] = $reportMetadata;
        }

        $reportMetadata = $filteredReportMetadata;
    }

    public function getReportTypes(&$reportTypes)
    {
        $reportTypes = array_merge($reportTypes, self::$managedReportTypes);
    }

    public function getReportFormats(&$reportFormats, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $reportFormats = self::$managedReportFormats;
        }
    }

    public function getReportParameters(&$availableParameters, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $availableParameters = self::$availableParameters;
        }
    }

    public function processReports(&$processedReports, $reportType, $outputType, $report)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        $displayFormat = $report['parameters'][self::DISPLAY_FORMAT_PARAMETER];
        $evolutionGraph = $report['parameters'][self::EVOLUTION_GRAPH_PARAMETER];

        foreach ($processedReports as &$processedReport) {
            $metadata = $processedReport['metadata'];

            $isAggregateReport = !empty($metadata['dimension']);

            $processedReport['displayTable'] = $displayFormat != self::DISPLAY_FORMAT_GRAPHS_ONLY;

            $processedReport['displayGraph'] =
                ($isAggregateReport ?
                    $displayFormat == self::DISPLAY_FORMAT_GRAPHS_ONLY || $displayFormat == self::DISPLAY_FORMAT_TABLES_AND_GRAPHS
                    :
                    $displayFormat != self::DISPLAY_FORMAT_TABLES_ONLY)
                && \Piwik\SettingsServer::isGdExtensionEnabled()
                && \Piwik\Plugin\Manager::getInstance()->isPluginActivated('ImageGraph')
                && !empty($metadata['imageGraphUrl']);

            $processedReport['evolutionGraph'] = $evolutionGraph;

            // remove evolution metrics from MultiSites.getAll
            if ($metadata['module'] == 'MultiSites') {
                $columns = $processedReport['columns'];

                foreach (\Piwik\Plugins\MultiSites\API::getApiMetrics($enhanced = true) as $metricSettings) {
                    unset($columns[$metricSettings[\Piwik\Plugins\MultiSites\API::METRIC_EVOLUTION_COL_NAME_KEY]]);
                }

                $processedReport['metadata'] = $metadata;
                $processedReport['columns'] = $columns;
            }
        }
    }

    public function getRendererInstance(&$reportRenderer, $reportType, $outputType, $report)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        $reportFormat = $report['format'];

        $reportRenderer = ReportRenderer::factory($reportFormat);

        if ($reportFormat == ReportRenderer::HTML_FORMAT) {
            $reportRenderer->setRenderImageInline($outputType != API::OUTPUT_SAVE_ON_DISK);
        }
    }

    public function allowMultipleReports(&$allowMultipleReports, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $allowMultipleReports = true;
        }
    }

    /**
     * @param $reportType
     * @param $report
     * @param $contents
     * @param $filename
     * @param $prettyDate
     * @param $reportSubject
     * @param $reportTitle
     * @param $additionalFiles
     * @param Period|null $period
     * @param $force
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function sendReport($reportType, $report, $contents, $filename, $prettyDate, $reportSubject, $reportTitle,
                               $additionalFiles, $period, $force)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        // Safeguard against sending the same report twice to the same email (unless $force is true)
        if (!$force && $this->reportAlreadySent($report, $period)) {
            Log::warning(
                'Preventing the same scheduled report from being sent again (report #%s for period "%s")',
                $report['idreport'],
                $prettyDate
            );
            return;
        }

        $generatedReport = new GeneratedReport($report, $reportTitle, $prettyDate, $contents, $additionalFiles);

        $reportFormat = $generatedReport->getReportFormat();

        $customReplyTo = null;
        if (Config::getInstance()->General['scheduled_reports_replyto_is_user_email_and_alias']
            || !isset($reportDetails['login'])
        ) {
            $userModel = new UserModel();
            $customReplyTo = $userModel->getUser($report['login']);
        }

        /** @var ReportEmailGenerator $reportGenerator */
        $reportGenerator = StaticContainer::get(ReportEmailGenerator::class . '.' . $reportFormat);
        $mail = $reportGenerator->makeEmail($generatedReport, $customReplyTo);

        // Get user emails and languages
        $reportParameters = $report['parameters'];
        $emails = array();

        if (isset($reportParameters[self::ADDITIONAL_EMAILS_PARAMETER])) {
            $emails = $reportParameters[self::ADDITIONAL_EMAILS_PARAMETER];
        }

        if ($reportParameters[self::EMAIL_ME_PARAMETER] == 1) {
            if (Piwik::getCurrentUserLogin() == $report['login']) {
                $emails[] = Piwik::getCurrentUserEmail();
            } else {
                try {
                    $user = APIUsersManager::getInstance()->getUser($report['login']);
                } catch (Exception $e) {
                    return;
                }
                $emails[] = $user['email'];
            }
        }
        $emails = array_unique($emails);

        if (! $force) {
            $this->markReportAsSent($report, $period);
        }

        $subscriptionModel = new SubscriptionModel();
        $subscriptionModel->updateReportSubscriptions($report['idreport'], $emails);
        $subscriptions = $subscriptionModel->getReportSubscriptions($report['idreport']);

        $tokens = array_column($subscriptions, 'token', 'email');

        $textContent = $mail->getBodyText();
        $htmlContent = $mail->getBodyHtml();

        foreach ($emails as $email) {
            if (empty($email)) {
                continue;
            }
            $mail->addTo($email);

            // add unsubscribe links to content
            if ($htmlContent) {
                $link = SettingsPiwik::getPiwikUrl() . 'index.php?module=ScheduledReports&action=unsubscribe&token=' . $tokens[$email];
                $bodyContent = str_replace(ReportRenderer\Html::UNSUBSCRIBE_LINK_PLACEHOLDER, Common::sanitizeInputValue($link), $htmlContent);

                $mail->setBodyHtml($bodyContent);
            }

            if ($textContent) {
                $link = SettingsPiwik::getPiwikUrl() . 'index.php?module=ScheduledReports&action=unsubscribe&token=' . $tokens[$email];
                $mail->setBodyText($textContent . "\n\n".Piwik::translate('ScheduledReports_UnsubscribeFooter', [$link]));
            }

            try {
                $mail->send();
            } catch (Exception $e) {

                // If running from piwik.php with debug, we ignore the 'email not sent' error
                $tracker = new Tracker();
                if (!$tracker->isDebugModeEnabled()) {
                    throw new Exception("An error occurred while sending '$filename' " .
                        " to " . implode(', ', $mail->getRecipients()) .
                        ". Error was '" . $e->getMessage() . "'");
                }
            }
            $mail->clearAllRecipients();
        }
    }

    public function deletePhoneNumber($phoneNumber)
    {
        $api = API::getInstance();

        $reports = $api->getReports(
            $idSite = false,
            $period = false,
            $idReport = false,
            $ifSuperUserReturnOnlySuperUserReports = false
        );

        foreach ($reports as $report) {
            if ($report['type'] == MobileMessaging::MOBILE_TYPE) {
                $reportParameters = $report['parameters'];
                $reportPhoneNumbers = $reportParameters[MobileMessaging::PHONE_NUMBERS_PARAMETER];
                $updatedPhoneNumbers = array();
                foreach ($reportPhoneNumbers as $reportPhoneNumber) {
                    if ($reportPhoneNumber != $phoneNumber) {
                        $updatedPhoneNumbers[] = $reportPhoneNumber;
                    }
                }

                if (count($updatedPhoneNumbers) != count($reportPhoneNumbers)) {
                    $reportParameters[MobileMessaging::PHONE_NUMBERS_PARAMETER] = $updatedPhoneNumbers;

                    // note: reports can end up without any recipients
                    $api->updateReport(
                        $report['idreport'],
                        $report['idsite'],
                        $report['description'],
                        $report['period'],
                        $report['hour'],
                        $report['type'],
                        $report['format'],
                        $report['reports'],
                        $reportParameters
                    );
                }
            }
        }
    }

    public function getReportRecipients(&$recipients, $reportType, $report)
    {
        if (! self::manageEvent($reportType)) {
            return;
        }

        $parameters = $report['parameters'];
        $eMailMe = $parameters[self::EMAIL_ME_PARAMETER];

        if ($eMailMe) {
            $recipients[] = Piwik::getCurrentUserEmail();
        }

        if (isset($parameters[self::ADDITIONAL_EMAILS_PARAMETER])) {
            $additionalEMails = $parameters[self::ADDITIONAL_EMAILS_PARAMETER];
            $recipients = array_merge($recipients, $additionalEMails);
        }
        $recipients = array_values(array_filter($recipients));
    }

    public static function template_reportParametersScheduledReports(&$out)
    {
        $view = new View('@ScheduledReports/reportParametersScheduledReports');
        $view->currentUserEmail = Piwik::getCurrentUserEmail();
        $view->reportType = self::EMAIL_TYPE;
        $view->defaultDisplayFormat = self::DEFAULT_DISPLAY_FORMAT;
        $view->defaultEmailMe = self::EMAIL_ME_PARAMETER_DEFAULT_VALUE;
        $view->defaultEvolutionGraph = self::EVOLUTION_GRAPH_PARAMETER_DEFAULT_VALUE;
        $out .= $view->render();
    }

    private static function manageEvent($reportType)
    {
        return in_array($reportType, array_keys(self::$managedReportTypes));
    }

    public function segmentUpdated($idSegment, $updatedSegment)
    {
        $reportsUsingSegment = API::getInstance()->getReports(false, false, false, false, $idSegment);

        $reportsNeedSegment = array();

        if (!$updatedSegment['enable_all_users']) {
            // which reports would become invisible to other users?
            foreach($reportsUsingSegment as $report) {
                if ($report['login'] == Piwik::getCurrentUserLogin()) {
                    continue;
                }
                $reportsNeedSegment[] = $report;
            }
        }

        if ($updatedSegment['enable_only_idsite']) {
            // which reports from other websites are set to use this segment restricted to one website?
            foreach($reportsUsingSegment as $report) {
                if ($report['idsite'] == $updatedSegment['enable_only_idsite']) {
                    continue;
                }
                $reportsNeedSegment[] = $report;
            }
        }

        if (empty($reportsNeedSegment)) {
            return;
        }

        $this->throwExceptionReportsAreUsingSegment($reportsNeedSegment);
    }

    public function segmentDeactivation($idSegment)
    {
        $reportsUsingSegment = API::getInstance()->getReports(false, false, false, false, $idSegment);
        if (empty($reportsUsingSegment)) {
            return;
        }

        $this->throwExceptionReportsAreUsingSegment($reportsUsingSegment);
    }

    /**
     * @param $reportsUsingSegment
     * @throws \Exception
     */
    protected function throwExceptionReportsAreUsingSegment($reportsUsingSegment)
    {
        $reportList = '';
        $reportNameJoinText = ' ' . Piwik::translate('General_And') . ' ';
        foreach ($reportsUsingSegment as $report) {
            $reportList .= '\'' . $report['description'] . '\'' . $reportNameJoinText;
        }
        $reportList = rtrim($reportList, $reportNameJoinText);

        $errorMessage = Piwik::translate('ScheduledReports_Segment_Deletion_Error', $reportList);
        throw new Exception($errorMessage);
    }

    public function deleteUserReport($userLogin)
    {
        $this->getModel()->deleteAllReportForUser($userLogin);
    }

    public function deleteUserReportForSites($userLogin, $idSites)
    {
        if (empty($idSites) || empty($userLogin)) {
            return;
        }

        $model = $this->getModel();

        foreach ($idSites as $idSite) {
            $model->deleteUserReportForSite($userLogin, $idSite);
        }
    }

    private function getModel()
    {
        return new Model();
    }

    public function install()
    {
        Model::install();
        SubscriptionModel::install();
    }

    private static function checkAdditionalEmails($additionalEmails)
    {
        foreach ($additionalEmails as &$email) {
            $email = trim($email);
            if (empty($email)) {
                $email = false;
            } elseif (!Piwik::isValidEmailString($email)) {
                throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidEmail') . ' (' . $email . ')');
            }
        }
        $additionalEmails = array_values(array_unique(array_filter($additionalEmails)));
        return $additionalEmails;
    }

    public static function getDisplayFormats()
    {
        $displayFormats = array(
            // ScheduledReports_AggregateReportsFormat_TablesOnly should be named ScheduledReports_DisplayFormat_GraphsOnlyForKeyMetrics
            self::DISPLAY_FORMAT_GRAPHS_ONLY_FOR_KEY_METRICS => Piwik::translate('ScheduledReports_AggregateReportsFormat_TablesOnly'),
            // ScheduledReports_AggregateReportsFormat_GraphsOnly should be named ScheduledReports_DisplayFormat_GraphsOnly
            self::DISPLAY_FORMAT_GRAPHS_ONLY                 => Piwik::translate('ScheduledReports_AggregateReportsFormat_GraphsOnly'),
            // ScheduledReports_AggregateReportsFormat_TablesAndGraphs should be named ScheduledReports_DisplayFormat_TablesAndGraphs
            self::DISPLAY_FORMAT_TABLES_AND_GRAPHS           => Piwik::translate('ScheduledReports_AggregateReportsFormat_TablesAndGraphs'),
            self::DISPLAY_FORMAT_TABLES_ONLY                 => Piwik::translate('ScheduledReports_DisplayFormat_TablesOnly'),
        );
        return $displayFormats;
    }

    /**
     * Used in the Report Listing
     * @ignore
     */
    public static function getPeriodToFrequency()
    {
        return array(
            Schedule::PERIOD_NEVER => Piwik::translate('General_Never'),
            Schedule::PERIOD_DAY   => Piwik::translate('General_Daily'),
            Schedule::PERIOD_WEEK  => Piwik::translate('General_Weekly'),
            Schedule::PERIOD_MONTH => Piwik::translate('General_Monthly'),
        );
    }

    public static function getPeriodFrequencyTranslations()
    {
        return [
            Schedule::PERIOD_DAY   => [
                'single' => Piwik::translate('Intl_PeriodDay'),
                'plural' => Piwik::translate('Intl_PeriodDays'),
            ],
            Schedule::PERIOD_WEEK  => [
                'single' => Piwik::translate('Intl_PeriodWeek'),
                'plural' => Piwik::translate('Intl_PeriodWeeks'),
            ],
            Schedule::PERIOD_MONTH => [
                'single' => Piwik::translate('Intl_PeriodMonth'),
                'plural' => Piwik::translate('Intl_PeriodMonths'),
            ],
        ];
    }

    private function reportAlreadySent($report, Period $period)
    {
        $key = self::OPTION_KEY_LAST_SENT_DATERANGE . $report['idreport'];

        $previousDate = Option::get($key);

        return $previousDate === $period->getRangeString();
    }

    private function markReportAsSent($report, Period $period)
    {
        $key = self::OPTION_KEY_LAST_SENT_DATERANGE . $report['idreport'];

        Option::set($key, $period->getRangeString());
    }
}
