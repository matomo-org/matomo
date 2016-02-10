<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging;

use Piwik\Option;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\API\API as APIPlugins;
use Piwik\Plugins\MobileMessaging\API as APIMobileMessaging;
use Piwik\Plugins\MobileMessaging\ReportRenderer\ReportRendererException;
use Piwik\Plugins\MobileMessaging\ReportRenderer\Sms;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\View;

/**
 *
 */
class MobileMessaging extends \Piwik\Plugin
{
    const DELEGATED_MANAGEMENT_OPTION = 'MobileMessaging_DelegatedManagement';
    const PROVIDER_OPTION = 'Provider';
    const API_KEY_OPTION = 'APIKey';
    const PHONE_NUMBERS_OPTION = 'PhoneNumbers';
    const PHONE_NUMBER_VALIDATION_REQUEST_COUNT_OPTION = 'PhoneNumberValidationRequestCount';
    const SMS_SENT_COUNT_OPTION = 'SMSSentCount';
    const DELEGATED_MANAGEMENT_OPTION_DEFAULT = 'false';
    const USER_SETTINGS_POSTFIX_OPTION = '_MobileMessagingSettings';

    const PHONE_NUMBERS_PARAMETER = 'phoneNumbers';

    const MOBILE_TYPE = 'mobile';
    const SMS_FORMAT = 'sms';

    private static $availableParameters = array(
        self::PHONE_NUMBERS_PARAMETER => true,
    );

    private static $managedReportTypes = array(
        self::MOBILE_TYPE => 'plugins/MobileMessaging/images/phone.png'
    );

    private static $managedReportFormats = array(
        self::SMS_FORMAT => 'plugins/MobileMessaging/images/phone.png'
    );

    private static $availableReports = array(
        array(
            'module' => 'MultiSites',
            'action' => 'getAll',
        ),
        array(
            'module' => 'MultiSites',
            'action' => 'getOne',
        ),
    );

    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'           => 'getJsFiles',
            'AssetManager.getStylesheetFiles'           => 'getStylesheetFiles',
            'ScheduledReports.getReportParameters'      => 'getReportParameters',
            'ScheduledReports.validateReportParameters' => 'validateReportParameters',
            'ScheduledReports.getReportMetadata'        => 'getReportMetadata',
            'ScheduledReports.getReportTypes'           => 'getReportTypes',
            'ScheduledReports.getReportFormats'         => 'getReportFormats',
            'ScheduledReports.getRendererInstance'      => 'getRendererInstance',
            'ScheduledReports.getReportRecipients'      => 'getReportRecipients',
            'ScheduledReports.allowMultipleReports'     => 'allowMultipleReports',
            'ScheduledReports.sendReport'               => 'sendReport',
            'Template.reportParametersScheduledReports' => 'template_reportParametersScheduledReports',
        );
    }

    /**
     * Get JavaScript files
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/MobileMessaging/javascripts/MobileMessagingSettings.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/MobileMessaging/stylesheets/MobileMessagingSettings.less";
    }

    public function validateReportParameters(&$parameters, $reportType)
    {
        if (self::manageEvent($reportType)) {
            // phone number validation
            $availablePhoneNumbers = APIMobileMessaging::getInstance()->getActivatedPhoneNumbers();

            $phoneNumbers = $parameters[self::PHONE_NUMBERS_PARAMETER];
            foreach ($phoneNumbers as $key => $phoneNumber) {
                //when a wrong phone number is supplied we silently discard it
                if (!in_array($phoneNumber, $availablePhoneNumbers)) {
                    unset($phoneNumbers[$key]);
                }
            }

            // 'unset' seems to transform the array to an associative array
            $parameters[self::PHONE_NUMBERS_PARAMETER] = array_values($phoneNumbers);
        }
    }

    public function getReportMetadata(&$availableReportMetadata, $reportType, $idSite)
    {
        if (self::manageEvent($reportType)) {
            foreach (self::$availableReports as $availableReport) {
                $reportMetadata = APIPlugins::getInstance()->getMetadata(
                    $idSite,
                    $availableReport['module'],
                    $availableReport['action']
                );

                if ($reportMetadata != null) {
                    $reportMetadata = reset($reportMetadata);
                    $availableReportMetadata[] = $reportMetadata;
                }
            }
        }
    }

    public function getReportTypes(&$reportTypes)
    {
        $reportTypes = array_merge($reportTypes, self::$managedReportTypes);
    }

    public function getReportFormats(&$reportFormats, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $reportFormats = array_merge($reportFormats, self::$managedReportFormats);
        }
    }

    public function getReportParameters(&$availableParameters, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $availableParameters = self::$availableParameters;
        }
    }

    public function getRendererInstance(&$reportRenderer, $reportType, $outputType, $report)
    {
        if (self::manageEvent($reportType)) {
            if (\Piwik\Plugin\Manager::getInstance()->isPluginActivated('MultiSites')) {
                $reportRenderer = new Sms();
            } else {
                $reportRenderer = new ReportRendererException(
                    Piwik::translate('MobileMessaging_MultiSites_Must_Be_Activated')
                );
            }
        }
    }

    public function allowMultipleReports(&$allowMultipleReports, $reportType)
    {
        if (self::manageEvent($reportType)) {
            $allowMultipleReports = false;
        }
    }

    public function getReportRecipients(&$recipients, $reportType, $report)
    {
        if (self::manageEvent($reportType)) {
            $recipients = $report['parameters'][self::PHONE_NUMBERS_PARAMETER];
        }
    }

    public function sendReport($reportType, $report, $contents, $filename, $prettyDate, $reportSubject, $reportTitle,
                               $additionalFiles, Period $period = null, $force)
    {
        if (self::manageEvent($reportType)) {
            $parameters = $report['parameters'];
            $phoneNumbers = $parameters[self::PHONE_NUMBERS_PARAMETER];

            // 'All Websites' is one character above the limit, use 'Reports' instead
            if ($reportSubject == Piwik::translate('General_MultiSitesSummary')) {
                $reportSubject = Piwik::translate('General_Reports');
            }

            $mobileMessagingAPI = APIMobileMessaging::getInstance();
            foreach ($phoneNumbers as $phoneNumber) {
                $mobileMessagingAPI->sendSMS(
                    $contents,
                    $phoneNumber,
                    $reportSubject
                );
            }
        }
    }

    public static function template_reportParametersScheduledReports(&$out)
    {
        if (Piwik::isUserIsAnonymous()) {
            return;
        }

        $view = new View('@MobileMessaging/reportParametersScheduledReports');
        $view->reportType = self::MOBILE_TYPE;
        $view->phoneNumbers = APIMobileMessaging::getInstance()->getActivatedPhoneNumbers();
        $out .= $view->render();
    }

    private static function manageEvent($reportType)
    {
        return in_array($reportType, array_keys(self::$managedReportTypes));
    }

    function install()
    {
        $delegatedManagement = Option::get(self::DELEGATED_MANAGEMENT_OPTION);
        if (empty($delegatedManagement)) {
            Option::set(self::DELEGATED_MANAGEMENT_OPTION, self::DELEGATED_MANAGEMENT_OPTION_DEFAULT);
        }
    }

    function deactivate()
    {
        // delete all mobile reports
        $APIScheduledReports = APIScheduledReports::getInstance();
        $reports = $APIScheduledReports->getReports();

        foreach ($reports as $report) {
            if ($report['type'] == MobileMessaging::MOBILE_TYPE) {
                $APIScheduledReports->deleteReport($report['idreport']);
            }
        }
    }

    public function uninstall()
    {
        // currently the UI does not allow to delete a plugin
        // when it becomes available, all the MobileMessaging settings (API credentials, phone numbers, etc..) should be removed from the option table
        return;
    }
}
