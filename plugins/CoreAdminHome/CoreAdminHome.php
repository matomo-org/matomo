<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\ProxyHttp;
use Piwik\Plugins\CoreHome\SystemSummary;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;

/**
 *
 */
class CoreAdminHome extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'UsersManager.deleteUser'         => 'cleanupUser',
            'API.DocumentationGenerator.@hideExceptForSuperUser' => 'displayOnlyForSuperUser',
            'Template.jsGlobalVariables' => 'addJsGlobalVariables',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'System.addSystemSummaryItems' => 'addSystemSummaryItems',
        );
    }

    public function addSystemSummaryItems(&$systemSummary)
    {
        if (Piwik::isUserHasSomeAdminAccess()) {
            $failures = Request::processRequest('CoreAdminHome.getTrackingFailures', [], []);
            $numFailures = count($failures);
            $icon = 'icon-error';
            if ($numFailures === 0) {
                $icon = 'icon-ok';
            }
            $systemSummary[] = new SystemSummary\Item($key = 'trackingfailures', Piwik::translate('CoreAdminHome_NTrackingFailures', $numFailures), $value = null, array('module' => 'CoreAdminHome', 'action' => 'trackingFailures'), $icon, $order = 9);
        }
    }

    public function cleanupUser($userLogin)
    {
        PluginSettingsTable::removeAllUserSettingsForUser($userLogin);
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "node_modules/jquery-ui-dist/jquery-ui.min.css";
        $stylesheets[] = "plugins/Morpheus/stylesheets/base.less";
        $stylesheets[] = "plugins/Morpheus/stylesheets/main.less";
        $stylesheets[] = "plugins/CoreAdminHome/stylesheets/generalSettings.less";
        $stylesheets[] = "plugins/CoreAdminHome/vue/src/TrackingFailures/TrackingFailures.less";
        $stylesheets[] = "plugins/CoreAdminHome/stylesheets/whatIsNew.less";
        $stylesheets[] = "plugins/CoreAdminHome/stylesheets/trackingCodeGenerator.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/jquery/dist/jquery.min.js";
        $jsFiles[] = "node_modules/jquery-ui-dist/jquery-ui.min.js";
        $jsFiles[] = "node_modules/jquery.browser/dist/jquery.browser.min.js";
        $jsFiles[] = "node_modules/sprintf-js/dist/sprintf.min.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/piwikHelper.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/broadcast.js";
        $jsFiles[] = "plugins/CoreAdminHome/javascripts/protocolCheck.js";
    }

    public function displayOnlyForSuperUser(&$hide)
    {
        $hide = !Piwik::hasUserSuperUserAccess();
    }

    public function addJsGlobalVariables(&$out)
    {
        if (ProxyHttp::isHttps()) {
            $isHttps = 'true';
        } else {
            $isHttps = 'false';
        }

        $out .= "piwik.hasServerDetectedHttps = $isHttps;\n";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'CoreAdminHome_ProtocolNotDetectedCorrectly';
        $translationKeys[] = 'CoreAdminHome_ProtocolNotDetectedCorrectlySolution';
        $translationKeys[] = 'CoreAdminHome_SettingsSaveSuccess';
        $translationKeys[] = 'UserCountryMap_None';
        $translationKeys[] = 'Actions_ColumnPageURL';
        $translationKeys[] = 'General_Date';
        $translationKeys[] = 'General_Measurable';
        $translationKeys[] = 'General_Action';
        $translationKeys[] = 'General_Delete';
        $translationKeys[] = 'General_Id';
        $translationKeys[] = 'CoreHome_ClickToSeeFullInformation';
        $translationKeys[] = 'CoreAdminHome_LearnMore';
        $translationKeys[] = 'CoreAdminHome_ConfirmDeleteAllTrackingFailures';
        $translationKeys[] = 'CoreAdminHome_ConfirmDeleteThisTrackingFailure';
        $translationKeys[] = 'CoreAdminHome_DeleteAllFailures';
        $translationKeys[] = 'CoreAdminHome_NTrackingFailures';
        $translationKeys[] = 'CoreAdminHome_Problem';
        $translationKeys[] = 'CoreAdminHome_Solution';
        $translationKeys[] = 'CoreAdminHome_TrackingFailures';
        $translationKeys[] = 'CoreAdminHome_TrackingFailuresIntroduction';
        $translationKeys[] = 'CoreAdminHome_TrackingURL';
        $translationKeys[] = 'CoreAdminHome_NoKnownFailures';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CodeNoteBeforeClosingHead';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CodeNoteBeforeClosingHeadEmail';
        $translationKeys[] = 'SitesManager_InstallationGuides';
        $translationKeys[] = 'CoreAdminHome_ArchivingSettings';
        $translationKeys[] = 'General_AllowPiwikArchivingToTriggerBrowser';
        $translationKeys[] = 'General_ReportsContainingTodayWillBeProcessedAtMostEvery';
        $translationKeys[] = 'General_SmallTrafficYouCanLeaveDefault';
        $translationKeys[] = 'General_ArchivingTriggerDescription';
        $translationKeys[] = 'General_ArchivingTriggerSegment';
        $translationKeys[] = 'General_SeeTheOfficialDocumentationForMoreInformation';
        $translationKeys[] = 'General_SmallTrafficYouCanLeaveDefault';
        $translationKeys[] = 'General_MediumToHighTrafficItIsRecommendedTo';
        $translationKeys[] = 'General_RearchiveTimeIntervalOnlyForTodayReports';
        $translationKeys[] = 'General_ArchivingInlineHelp';
        $translationKeys[] = 'General_NewReportsWillBeProcessedByCron';
        $translationKeys[] = 'General_ReportsWillBeProcessedAtMostEveryHour';
        $translationKeys[] = 'General_IfArchivingIsFastYouCanSetupCronRunMoreOften';
        $translationKeys[] = 'CoreAdminHome_BrandingSettings';
        $translationKeys[] = 'CoreAdminHome_CustomLogoHelpText';
        $translationKeys[] = 'CoreAdminHome_UseCustomLogo';
        $translationKeys[] = 'CoreAdminHome_LogoUpload';
        $translationKeys[] = 'CoreAdminHome_FaviconUpload';
        $translationKeys[] = 'CoreAdminHome_LogoUploadHelp';
        $translationKeys[] = 'CoreAdminHome_LogoUploadFailed';
        $translationKeys[] = 'CoreAdminHome_FileUploadDisabled';
        $translationKeys[] = 'CoreAdminHome_LogoNotWriteableInstruction';
        $translationKeys[] = 'General_GiveUsYourFeedback';
        $translationKeys[] = 'CoreAdminHome_CustomLogoFeedbackInfo';
        $translationKeys[] = 'CoreAdminHome_EmailServerSettings';
        $translationKeys[] = 'General_UseSMTPServerForEmail';
        $translationKeys[] = 'General_SelectYesIfYouWantToSendEmailsViaServer';
        $translationKeys[] = 'General_SmtpServerAddress';
        $translationKeys[] = 'General_SmtpPort';
        $translationKeys[] = 'General_OptionalSmtpPort';
        $translationKeys[] = 'General_AuthenticationMethodSmtp';
        $translationKeys[] = 'General_OnlyUsedIfUserPwdIsSet';
        $translationKeys[] = 'General_SmtpUsername';
        $translationKeys[] = 'General_OnlyEnterIfRequired';
        $translationKeys[] = 'General_SmtpPassword';
        $translationKeys[] = 'General_SmtpFromAddress';
        $translationKeys[] = 'General_SmtpFromEmailHelp';
        $translationKeys[] = 'General_SmtpFromName';
        $translationKeys[] = 'General_NameShownInTheSenderColumn';
        $translationKeys[] = 'General_SmtpEncryption';
        $translationKeys[] = 'General_EncryptedSmtpTransport';
        $translationKeys[] = 'General_OnlyEnterIfRequiredPassword';
        $translationKeys[] = 'General_WarningPasswordStored';
        $translationKeys[] = 'CoreAdminHome_ImageTracking';
        $translationKeys[] = 'CoreAdminHome_TrackAGoal';
        $translationKeys[] = 'CoreAdminHome_WithOptionalRevenue';
        $translationKeys[] = 'CoreAdminHome_ImageTrackingLink';
        $translationKeys[] = 'CoreAdminHome_ImageTrackingIntro1';
        $translationKeys[] = 'CoreAdminHome_ImageTrackingIntro2';
        $translationKeys[] = 'CoreAdminHome_ImageTrackingIntro3';
        $translationKeys[] = 'CoreAdminHome_JavaScriptTracking';
        $translationKeys[] = 'CoreAdminHome_JSTrackingIntro1';
        $translationKeys[] = 'CoreAdminHome_JSTrackingIntro2';
        $translationKeys[] = 'CoreAdminHome_JSTrackingIntro3b';
        $translationKeys[] = 'General_JsTrackingTag';
        $translationKeys[] = 'SitesManager_EmailInstructionsButton';
        $translationKeys[] = 'CoreAdminHome_JSTracking_MergeSubdomains';
        $translationKeys[] = 'CoreAdminHome_JSTracking_GroupPageTitlesByDomainDesc1';
        $translationKeys[] = 'CoreAdminHome_JSTracking_GroupPageTitlesByDomain';
        $translationKeys[] = 'CoreAdminHome_JSTracking_MergeAliasesDesc';
        $translationKeys[] = 'CoreAdminHome_JSTracking_MergeAliases';
        $translationKeys[] = 'CoreAdminHome_JSTracking_TrackNoScript';
        $translationKeys[] = 'Mobile_Advanced';
        $translationKeys[] = 'CoreAdminHome_JSTracking_VisitorCustomVars';
        $translationKeys[] = 'CoreAdminHome_JSTracking_VisitorCustomVarsDesc';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CrossDomain';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CrossDomain_NeedsMultipleDomains';
        $translationKeys[] = 'CoreAdminHome_JSTracking_EnableCrossDomainLinking';
        $translationKeys[] = 'CoreAdminHome_JSTracking_EnableDoNotTrackDesc';
        $translationKeys[] = 'CoreAdminHome_JSTracking_EnableDoNotTrack_AlreadyEnabled';
        $translationKeys[] = 'CoreAdminHome_JSTracking_EnableDoNotTrack';
        $translationKeys[] = 'CoreAdminHome_JSTracking_DisableCookies';
        $translationKeys[] = 'CoreAdminHome_JSTracking_DisableCookiesDesc';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CustomCampaignQueryParam';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CampaignNameParam';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CampaignKwdParam';
        $translationKeys[] = 'SitesManager_EmailInstructionsSubject';
        $translationKeys[] = 'SitesManager_JsTrackingTagHelp';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CodeNoteBeforeClosingHeadEmail';
        $translationKeys[] = 'CoreAdminHome_JSTrackingIntro3a';
        $translationKeys[] = 'CoreAdminHome_JSTrackingIntro4';
        $translationKeys[] = 'CoreAdminHome_JSTrackingIntro5';
        $translationKeys[] = 'General_Options';
        $translationKeys[] = 'General_Value';
        $translationKeys[] = 'Actions_ColumnPageName';
        $translationKeys[] = 'CoreAdminHome_JSTracking_MergeSubdomainsDesc';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CustomCampaignQueryParamDesc';
        $translationKeys[] = 'CoreAdminHome_SinglePageApplicationDescription';
        $translationKeys[] = 'CoreAdminHome_CloudflareDescription';
        $translationKeys[] = 'CoreAdminHome_SecurityNotificationUserAcceptInviteBody';
        $translationKeys[] = 'CoreAdminHome_SecurityNotificationUserDeclinedInviteBody';
        $translationKeys[] = 'CoreAdminHome_JSTracking_ConsentManagerDetected';
        $translationKeys[] = 'CoreAdminHome_JSTracking_ConsentManagerConnected';
        $translationKeys[] = 'CoreAdminHome_GoogleTagManagerDescription';
        $translationKeys[] = 'CoreAdminHome_WordpressDescription';
        $translationKeys[] = 'CoreAdminHome_VueDescription';
        $translationKeys[] = 'CoreAdminHome_ShowAdvancedOptions';
        $translationKeys[] = 'CoreAdminHome_HideAdvancedOptions';
        $translationKeys[] = 'CoreAdminHome_JSTrackingDocumentationHelp';
        $translationKeys[] = 'CoreAdminHome_ReactDescription';
    }
}
