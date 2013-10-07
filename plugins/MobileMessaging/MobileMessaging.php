<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package MobileMessaging
 */
namespace Piwik\Plugins\MobileMessaging;

use Piwik\Piwik;
use Piwik\Plugins\MobileMessaging\API as APIMobileMessaging;
use Piwik\View;
use Piwik\Plugins\API\API as APIPlugins;
use Piwik\Plugins\MobileMessaging\ReportRenderer\ReportRendererException;
use Piwik\Plugins\MobileMessaging\ReportRenderer\Sms;
use Piwik\Plugins\PDFReports\API as APIPDFReports;

/**
 *
 * @package MobileMessaging
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

    static private $availableParameters = array(
        self::PHONE_NUMBERS_PARAMETER => true,
    );

    static private $managedReportTypes = array(
        self::MOBILE_TYPE => 'plugins/MobileMessaging/images/phone.png'
    );

    static private $managedReportFormats = array(
        self::SMS_FORMAT => 'plugins/MobileMessaging/images/phone.png'
    );

    static private $availableReports = array(
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
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Menu.Admin.addItems'                 => 'addMenu',
            'AssetManager.getJavaScriptFiles'     => 'getJsFiles',
            'AssetManager.getStylesheetFiles'     => 'getStylesheetFiles',
            'PDFReports.getReportParameters'      => 'getReportParameters',
            'PDFReports.validateReportParameters' => 'validateReportParameters',
            'PDFReports.getReportMetadata'        => 'getReportMetadata',
            'PDFReports.getReportTypes'           => 'getReportTypes',
            'PDFReports.getReportFormats'         => 'getReportFormats',
            'PDFReports.getRendererInstance'      => 'getRendererInstance',
            'PDFReports.getReportRecipients'      => 'getReportRecipients',
            'PDFReports.allowMultipleReports'     => 'allowMultipleReports',
            'PDFReports.sendReport'               => 'sendReport',
            'Template.reportParametersPDFReports' => 'template_reportParametersPDFReports',
        );
    }

    function addMenu()
    {
        Piwik_AddAdminMenu(
            'MobileMessaging_SettingsMenu',
            array('module' => 'MobileMessaging', 'action' => 'index'),
            true
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

    public function validateReportParameters(&$parameters, $info)
    {
        if (self::manageEvent($info)) {
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

    public function getReportMetadata(&$availableReportMetadata, $notificationInfo)
    {
        if (self::manageEvent($notificationInfo)) {
            $idSite = $notificationInfo[APIPDFReports::ID_SITE_INFO_KEY];

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

    public function getReportFormats(&$reportFormats, $info)
    {
        if (self::manageEvent($info)) {
            $reportFormats = array_merge($reportFormats, self::$managedReportFormats);
        }
    }

    public function getReportParameters(&$availableParameters, $info)
    {
        if (self::manageEvent($info)) {
            $availableParameters = self::$availableParameters;
        }
    }

    public function getRendererInstance(&$reportRenderer, $info)
    {
        if (self::manageEvent($info)) {
            if (\Piwik\PluginsManager::getInstance()->isPluginActivated('MultiSites')) {
                $reportRenderer = new Sms();
            } else {
                $reportRenderer = new ReportRendererException(
                    Piwik_Translate('MobileMessaging_MultiSites_Must_Be_Activated')
                );
            }
        }
    }

    public function allowMultipleReports(&$allowMultipleReports, $info)
    {
        if (self::manageEvent($info)) {
            $allowMultipleReports = false;
        }
    }

    public function getReportRecipients(&$recipients, $notificationInfo)
    {
        if (self::manageEvent($notificationInfo)) {
            $report = $notificationInfo[APIPDFReports::REPORT_KEY];
            $recipients = $report['parameters'][self::PHONE_NUMBERS_PARAMETER];
        }
    }

    public function sendReport($notificationInfo)
    {
        if (self::manageEvent($notificationInfo)) {
            $report = $notificationInfo[APIPDFReports::REPORT_KEY];
            $contents = $notificationInfo[APIPDFReports::REPORT_CONTENT_KEY];
            $reportSubject = $notificationInfo[APIPDFReports::REPORT_SUBJECT_KEY];

            $parameters = $report['parameters'];
            $phoneNumbers = $parameters[self::PHONE_NUMBERS_PARAMETER];

            // 'All Websites' is one character above the limit, use 'Reports' instead
            if ($reportSubject == Piwik_Translate('General_MultiSitesSummary')) {
                $reportSubject = Piwik_Translate('General_Reports');
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

    static public function template_reportParametersPDFReports(&$out)
    {
        if (Piwik::isUserIsAnonymous()) {
            return;
        }

        $view = new View('@MobileMessaging/reportParametersPDFReports');
        $view->reportType = self::MOBILE_TYPE;
        $view->phoneNumbers = APIMobileMessaging::getInstance()->getActivatedPhoneNumbers();
        $out .= $view->render();
    }

    private static function manageEvent($notificationInfo)
    {
        return in_array($notificationInfo[APIPDFReports::REPORT_TYPE_INFO_KEY], array_keys(self::$managedReportTypes));
    }

    function install()
    {
        $delegatedManagement = Piwik_GetOption(self::DELEGATED_MANAGEMENT_OPTION);
        if (empty($delegatedManagement)) {
            Piwik_SetOption(self::DELEGATED_MANAGEMENT_OPTION, self::DELEGATED_MANAGEMENT_OPTION_DEFAULT);
        }
    }

    function deactivate()
    {
        // delete all mobile reports
        $pdfReportsAPIInstance = APIPDFReports::getInstance();
        $reports = $pdfReportsAPIInstance->getReports();

        foreach ($reports as $report) {
            if ($report['type'] == MobileMessaging::MOBILE_TYPE) {
                $pdfReportsAPIInstance->deleteReport($report['idreport']);
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
