<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MobileMessaging;

use Piwik\Option;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\API\API as APIPlugins;
use Piwik\Plugins\MobileMessaging\ReportRenderer\ReportRendererException;
use Piwik\Plugins\MobileMessaging\ReportRenderer\Sms;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\View;

/**
 *
 */
class MobileMessaging extends \Piwik\Plugin
{
    public const DELEGATED_MANAGEMENT_OPTION = 'MobileMessaging_DelegatedManagement';
    public const PROVIDER_OPTION = 'Provider';
    public const API_KEY_OPTION = 'APIKey';
    public const PHONE_NUMBERS_OPTION = 'PhoneNumbers';
    public const SMS_SENT_COUNT_OPTION = 'SMSSentCount';
    public const DELEGATED_MANAGEMENT_OPTION_DEFAULT = 'false';
    public const USER_SETTINGS_POSTFIX_OPTION = '_MobileMessagingSettings';

    public const PHONE_NUMBERS_PARAMETER = 'phoneNumbers';

    public const MOBILE_TYPE = 'mobile';
    public const SMS_FORMAT = 'sms';

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
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
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
            'Template.reportParametersScheduledReports' => 'templateReportParametersScheduledReports',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    public function requiresInternetConnection()
    {
        return true;
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/MobileMessaging/stylesheets/MobileMessagingSettings.less";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'CoreAdminHome_SettingsSaveSuccess';
        $translationKeys[] = 'MobileMessaging_Settings_InvalidActivationCode';
        $translationKeys[] = 'MobileMessaging_Settings_PhoneActivated';
        $translationKeys[] = 'MobileMessaging_Settings_SMSProvider';
        $translationKeys[] = 'MobileMessaging_Settings_PleaseSignUp';
        $translationKeys[] = 'MobileMessaging_Settings_CredentialInvalid';
        $translationKeys[] = 'MobileMessaging_Settings_CredentialProvided';
        $translationKeys[] = 'MobileMessaging_Settings_UpdateOrDeleteAccount';
        $translationKeys[] = 'MobileMessaging_UserKey';
        $translationKeys[] = 'General_Password';
        $translationKeys[] = 'MobileMessaging_Settings_APIKey';
        $translationKeys[] = 'MobileMessaging_Settings_LetUsersManageAPICredential';
        $translationKeys[] = 'MobileMessaging_Settings_SelectCountry';
        $translationKeys[] = 'MobileMessaging_Settings_CountryCode';
        $translationKeys[] = 'MobileMessaging_Settings_PhoneNumber';
        $translationKeys[] = 'MobileMessaging_Settings_EnterActivationCode';
        $translationKeys[] = 'MobileMessaging_Settings_PhoneNumbers_Add';
        $translationKeys[] = 'MobileMessaging_Settings_DelegatedPhoneNumbersOnlyUsedByYou';
        $translationKeys[] = 'MobileMessaging_Settings_PhoneNumbers_Help';
        $translationKeys[] = 'MobileMessaging_Settings_PhoneNumbers_CountryCode_Help';
        $translationKeys[] = 'MobileMessaging_Settings_ManagePhoneNumbers';
        $translationKeys[] = 'MobileMessaging_Settings_VerificationCodeJustSent';
        $translationKeys[] = 'MobileMessaging_Settings_ValidatePhoneNumber';
        $translationKeys[] = 'MobileMessaging_MobileReport_NoPhoneNumbers';
        $translationKeys[] = 'MobileMessaging_MobileReport_AdditionalPhoneNumbers';
        $translationKeys[] = 'MobileMessaging_MobileReport_MobileMessagingSettingsLink';
        $translationKeys[] = 'ScheduledReports_SendReportTo';
        $translationKeys[] = 'MobileMessaging_PhoneNumbers';
        $translationKeys[] = 'MobileMessaging_Settings_DelegatedSmsProviderOnlyAppliesToYou';
        $translationKeys[] = 'MobileMessaging_Settings_CredentialNotProvided';
        $translationKeys[] = 'MobileMessaging_Settings_CredentialNotProvidedByAdmin';
        $translationKeys[] = 'MobileMessaging_Settings_DeleteAccountConfirm';
        $translationKeys[] = 'MobileMessaging_Settings_SuspiciousPhoneNumber';
        $translationKeys[] = 'MobileMessaging_SettingsMenu';
        $translationKeys[] = 'MobileMessaging_ConfirmRemovePhoneNumber';
        $translationKeys[] = 'MobileMessaging_Settings_ResendVerification';
        $translationKeys[] = 'MobileMessaging_Settings_NewVerificationCodeSent';
        $translationKeys[] = 'General_Yes';
        $translationKeys[] = 'General_No';
    }

    public function validateReportParameters(&$parameters, $reportType)
    {
        if (self::manageEvent($reportType)) {
            // phone number validation
            $availablePhoneNumbers = $this->getModel()->getActivatedPhoneNumbers(Piwik::getCurrentUserLogin());

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
     */
    public function sendReport(
        $reportType,
        $report,
        $contents,
        $filename,
        $prettyDate,
        $reportSubject,
        $reportTitle,
        $additionalFiles,
        $period,
        $force
    ) {
        if (self::manageEvent($reportType)) {
            $parameters = $report['parameters'];
            $phoneNumbers = $parameters[self::PHONE_NUMBERS_PARAMETER];

            // 'All Websites' is one character above the limit, use 'Reports' instead
            if ($reportSubject == Piwik::translate('General_MultiSitesSummary')) {
                $reportSubject = Piwik::translate('General_Reports');
            }

            $model = $this->getModel();
            foreach ($phoneNumbers as $phoneNumber) {
                $model->sendSMS(
                    $contents,
                    $phoneNumber,
                    $reportSubject
                );
            }
        }
    }

    public function templateReportParametersScheduledReports(&$out, $context = '')
    {
        if (Piwik::isUserIsAnonymous()) {
            return;
        }

        $view = new View('@MobileMessaging/reportParametersScheduledReports');
        $view->reportType = self::MOBILE_TYPE;
        $view->context = $context;
        $numbers = $this->getModel()->getActivatedPhoneNumbers(Piwik::getCurrentUserLogin());

        $phoneNumbers = array();
        if (!empty($numbers)) {
            foreach ($numbers as $number) {
                $phoneNumbers[$number] = $number;
            }
        }

        $view->phoneNumbers = $phoneNumbers;
        $out .= $view->render();
    }

    private static function manageEvent($reportType)
    {
        return in_array($reportType, array_keys(self::$managedReportTypes));
    }

    public function install()
    {
        $delegatedManagement = Option::get(self::DELEGATED_MANAGEMENT_OPTION);
        if (empty($delegatedManagement)) {
            Option::set(self::DELEGATED_MANAGEMENT_OPTION, self::DELEGATED_MANAGEMENT_OPTION_DEFAULT);
        }
    }

    public function deactivate()
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

    protected function getModel()
    {
        return new Model();
    }
}
