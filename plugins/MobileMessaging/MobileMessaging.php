<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_MobileMessaging
 */

/**
 *
 * @package Piwik_MobileMessaging
 */
class Piwik_MobileMessaging extends Piwik_Plugin
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
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'name'             => 'Mobile Messaging Plugin',
            'description'      => Piwik_Translate('MobileMessaging_PluginDescription'),
            'homepage'         => 'http://piwik.org/',
            'author'           => 'Piwik',
            'author_homepage'  => 'http://piwik.org/',
            'license'          => 'GPL v3 or later',
            'license_homepage' => 'http://www.gnu.org/licenses/gpl.html',
            'version'          => Piwik_Version::VERSION,
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AdminMenu.add'                       => 'addMenu',
            'AssetManager.getJsFiles'             => 'getJsFiles',
            'AssetManager.getCssFiles'            => 'getCssFiles',
            'PDFReports.getReportParameters'      => 'getReportParameters',
            'PDFReports.validateReportParameters' => 'validateReportParameters',
            'PDFReports.getReportMetadata'        => 'getReportMetadata',
            'PDFReports.getReportTypes'           => 'getReportTypes',
            'PDFReports.getReportFormats'         => 'getReportFormats',
            'PDFReports.getRendererInstance'      => 'getRendererInstance',
            'PDFReports.getReportRecipients'      => 'getReportRecipients',
            'PDFReports.allowMultipleReports'     => 'allowMultipleReports',
            'PDFReports.sendReport'               => 'sendReport',
            'template_reportParametersPDFReports' => 'template_reportParametersPDFReports',
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

    public function getCssFiles($cssFiles)
    {
        $cssFiles[] = "plugins/MobileMessaging/stylesheets/MobileMessagingSettings.css";
    }
    
    public function validateReportParameters(&$parameters, $info)
    {
        if (self::manageEvent($info)) {
            // phone number validation
            $availablePhoneNumbers = Piwik_MobileMessaging_API::getInstance()->getActivatedPhoneNumbers();

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
            $idSite = $notificationInfo[Piwik_PDFReports_API::ID_SITE_INFO_KEY];

            foreach (self::$availableReports as $availableReport) {
                $reportMetadata = Piwik_API_API::getInstance()->getMetadata(
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
            if (Piwik_PluginsManager::getInstance()->isPluginActivated('MultiSites')) {
                $reportRenderer = new Piwik_MobileMessaging_ReportRenderer_Sms();
            } else {
                $reportRenderer = new Piwik_MobileMessaging_ReportRenderer_Exception(
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
            $report = $notificationInfo[Piwik_PDFReports_API::REPORT_KEY];
            $recipients = $report['parameters'][self::PHONE_NUMBERS_PARAMETER];
        }
    }

    public function sendReport($notificationInfo)
    {
        if (self::manageEvent($notificationInfo)) {
            $report = $notificationInfo[Piwik_PDFReports_API::REPORT_KEY];
            $contents = $notificationInfo[Piwik_PDFReports_API::REPORT_CONTENT_KEY];
            $reportSubject = $notificationInfo[Piwik_PDFReports_API::REPORT_SUBJECT_KEY];

            $parameters = $report['parameters'];
            $phoneNumbers = $parameters[self::PHONE_NUMBERS_PARAMETER];

            // 'All Websites' is one character above the limit, use 'Reports' instead
            if ($reportSubject == Piwik_Translate('General_MultiSitesSummary')) {
                $reportSubject = Piwik_Translate('General_Reports');
            }

            $mobileMessagingAPI = Piwik_MobileMessaging_API::getInstance();
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

        $view = new Piwik_View('@MobileMessaging/reportParametersPDFReports');
        $view->reportType = self::MOBILE_TYPE;
        $view->phoneNumbers = Piwik_MobileMessaging_API::getInstance()->getActivatedPhoneNumbers();
        $out .= $view->render();
    }

    private static function manageEvent($notificationInfo)
    {
        return in_array($notificationInfo[Piwik_PDFReports_API::REPORT_TYPE_INFO_KEY], array_keys(self::$managedReportTypes));
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
        $pdfReportsAPIInstance = Piwik_PDFReports_API::getInstance();
        $reports = $pdfReportsAPIInstance->getReports();

        foreach ($reports as $report) {
            if ($report['type'] == Piwik_MobileMessaging::MOBILE_TYPE) {
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
