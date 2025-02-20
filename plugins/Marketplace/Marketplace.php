<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Container\StaticContainer;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\PluginTrial\Service as PluginTrialService;
use Piwik\Request;
use Piwik\SettingsPiwik;
use Piwik\Widget\WidgetsList;

class Marketplace extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Controller.CoreHome.checkForUpdates' => 'checkForUpdates',
            'Controller.CoreHome.markNotificationAsRead' => 'dismissPluginTrialNotification',
            'Request.dispatch' => 'createPluginTrialNotification',
            'PluginManager.pluginInstalled' => 'removePluginTrialRequest',
            'PluginManager.pluginActivated' => 'removePluginTrialRequest',
            'Widget.filterWidgets' => 'filterWidgets'
        );
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function requiresInternetConnection()
    {
        return true;
    }

    public function checkForUpdates()
    {
        $marketplace = StaticContainer::get('Piwik\Plugins\Marketplace\Api\Client');
        $marketplace->clearAllCacheEntries();
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Marketplace/stylesheets/marketplace.less";
        $stylesheets[] = "plugins/Marketplace/stylesheets/plugin-details.less";
        $stylesheets[] = "plugins/Marketplace/stylesheets/marketplace-widget.less";
        $stylesheets[] = "plugins/Marketplace/stylesheets/rich-menu-button.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/iframe-resizer/js/iframeResizer.min.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'CorePluginsAdmin_Activate';
        $translationKeys[] = 'CorePluginsAdmin_Deactivate';
        $translationKeys[] = 'CorePluginsAdmin_Marketplace';
        $translationKeys[] = 'CorePluginsAdmin_MissingRequirementsNotice';
        $translationKeys[] = 'CorePluginsAdmin_PluginsExtendPiwik';
        $translationKeys[] = 'CorePluginsAdmin_Status';
        $translationKeys[] = 'CorePluginsAdmin_Theme';
        $translationKeys[] = 'CorePluginsAdmin_Themes';
        $translationKeys[] = 'CorePluginsAdmin_ThemesDescription';
        $translationKeys[] = 'CorePluginsAdmin_ViewAllMarketplacePlugins';
        $translationKeys[] = 'CoreUpdater_UpdateTitle';
        $translationKeys[] = 'General_Documentation';
        $translationKeys[] = 'General_Download';
        $translationKeys[] = 'General_Downloads';
        $translationKeys[] = 'General_Help';
        $translationKeys[] = 'General_Installed';
        $translationKeys[] = 'General_MoreDetails';
        $translationKeys[] = 'General_Ok';
        $translationKeys[] = 'General_Or';
        $translationKeys[] = 'General_Plugin';
        $translationKeys[] = 'General_Plugins';
        $translationKeys[] = 'Login_ConfirmPasswordToContinue';
        $translationKeys[] = 'Marketplace_ActionInstall';
        $translationKeys[] = 'Marketplace_ActivateLicenseKey';
        $translationKeys[] = 'Marketplace_AllowedUploadFormats';
        $translationKeys[] = 'Marketplace_BrowseMarketplace';
        $translationKeys[] = 'Marketplace_CannotUpdate';
        $translationKeys[] = 'Marketplace_CannotInstall';
        $translationKeys[] = 'Marketplace_CreateAccountErrorAPI';
        $translationKeys[] = 'Marketplace_CreateAccountErrorLicenseExists';
        $translationKeys[] = 'Marketplace_ConfirmRemoveLicense';
        $translationKeys[] = 'Marketplace_CurrentNumPiwikUsers';
        $translationKeys[] = 'Marketplace_Exceeded';
        $translationKeys[] = 'Marketplace_Free';
        $translationKeys[] = 'Marketplace_InstallAllPurchasedPlugins';
        $translationKeys[] = 'Marketplace_InstallAllPurchasedPluginsAction';
        $translationKeys[] = 'Marketplace_InstallPurchasedPlugins';
        $translationKeys[] = 'Marketplace_InstallThesePlugins';
        $translationKeys[] = 'Marketplace_Intro';
        $translationKeys[] = 'Marketplace_IntroSuperUser';
        $translationKeys[] = 'Marketplace_LicenseExceeded';
        $translationKeys[] = 'Marketplace_LicenseExceededPossibleCause';
        $translationKeys[] = 'Marketplace_LicenseKey';
        $translationKeys[] = 'Marketplace_LicenseKeyActivatedSuccess';
        $translationKeys[] = 'Marketplace_LicenseKeyDeletedSuccess';
        $translationKeys[] = 'Marketplace_LicenseKeyIsValidShort';
        $translationKeys[] = 'Marketplace_LicenseMissing';
        $translationKeys[] = 'Marketplace_LicenseRenewsNextPaymentDate';
        $translationKeys[] = 'Marketplace_ManageLicenseKeyIntro';
        $translationKeys[] = 'Marketplace_Marketplace';
        $translationKeys[] = 'Marketplace_NoPluginsFound';
        $translationKeys[] = 'Marketplace_NoSubscriptionsFound';
        $translationKeys[] = 'Marketplace_NoThemesFound';
        $translationKeys[] = 'Marketplace_NoValidSubscriptionNoUpdates';
        $translationKeys[] = 'Marketplace_NoticeRemoveMarketplaceFromReportingMenu';
        $translationKeys[] = 'Marketplace_OverviewPluginSubscriptions';
        $translationKeys[] = 'Marketplace_OverviewPluginSubscriptionsAllDetails';
        $translationKeys[] = 'Marketplace_OverviewPluginSubscriptionsMissingInfo';
        $translationKeys[] = 'Marketplace_OverviewPluginSubscriptionsMissingLicenseMessage';
        $translationKeys[] = 'Marketplace_PluginSubscriptionsList';
        $translationKeys[] = 'Marketplace_PluginUploadDisabled';
        $translationKeys[] = 'Marketplace_PriceFromPerPeriod';
        $translationKeys[] = 'Marketplace_RemoveLicenseKey';
        $translationKeys[] = 'Marketplace_RequestTrial';
        $translationKeys[] = 'Marketplace_RequestTrialConfirmEmailWarning';
        $translationKeys[] = 'Marketplace_RequestTrialConfirmTitle';
        $translationKeys[] = 'Marketplace_RequestTrialSubmitted';
        $translationKeys[] = 'Marketplace_RichMenuIntro';
        $translationKeys[] = 'Marketplace_Show';
        $translationKeys[] = 'Marketplace_Sort';
        $translationKeys[] = 'Marketplace_SpecialOffer';
        $translationKeys[] = 'Marketplace_StartFreeTrial';
        $translationKeys[] = 'Marketplace_SubscriptionEndDate';
        $translationKeys[] = 'Marketplace_SubscriptionExpiresSoon';
        $translationKeys[] = 'Marketplace_SubscriptionInvalid';
        $translationKeys[] = 'Marketplace_SubscriptionNextPaymentDate';
        $translationKeys[] = 'Marketplace_SubscriptionStartDate';
        $translationKeys[] = 'Marketplace_SubscriptionType';
        $translationKeys[] = 'Marketplace_SupportMatomoThankYou';
        $translationKeys[] = 'Marketplace_TeaserExtendPiwikByUpload';
        $translationKeys[] = 'Marketplace_TrialHints';
        $translationKeys[] = 'Marketplace_TrialRequested';
        $translationKeys[] = 'Marketplace_TrialStartErrorSupport';
        $translationKeys[] = 'Marketplace_TrialStartErrorTitle';
        $translationKeys[] = 'Marketplace_TrialStartInProgressText';
        $translationKeys[] = 'Marketplace_TrialStartInProgressTitle';
        $translationKeys[] = 'Marketplace_TrialStartNoLicenseAddHere';
        $translationKeys[] = 'Marketplace_TrialStartNoLicenseCreateAccount';
        $translationKeys[] = 'Marketplace_TrialStartNoLicenseLegalHint';
        $translationKeys[] = 'Marketplace_TrialStartNoLicenseText';
        $translationKeys[] = 'Marketplace_TrialStartNoLicenseTitle';
        $translationKeys[] = 'Marketplace_UpgradeSubscription';
        $translationKeys[] = 'Marketplace_UploadZipFile';
        $translationKeys[] = 'Marketplace_ViewSubscriptions';
        $translationKeys[] = 'Mobile_LoadingReport';
        $translationKeys[] = 'Marketplace_AddToCart';
        $translationKeys[] = 'Marketplace_Authors';
        $translationKeys[] = 'Marketplace_AutoUpdateDisabledWarning';
        $translationKeys[] = 'Marketplace_ByXDevelopers';
        $translationKeys[] = 'Marketplace_ClickToCompletePurchase';
        $translationKeys[] = 'Marketplace_Developer';
        $translationKeys[] = 'Marketplace_FeaturedPlugin';
        $translationKeys[] = 'Marketplace_LastCommitTime';
        $translationKeys[] = 'Marketplace_LastUpdated';
        $translationKeys[] = 'Marketplace_License';
        $translationKeys[] = 'Marketplace_MultiServerEnvironmentWarning';
        $translationKeys[] = 'Marketplace_NumDownloadsLatestVersion';
        $translationKeys[] = 'Marketplace_PluginKeywords';
        $translationKeys[] = 'Marketplace_PluginLicenseExceededDescription';
        $translationKeys[] = 'Marketplace_PluginLicenseMissingDescription';
        $translationKeys[] = 'Marketplace_PluginWebsite';
        $translationKeys[] = 'Marketplace_PriceExclTax';
        $translationKeys[] = 'Marketplace_Reviews';
        $translationKeys[] = 'Marketplace_Screenshots';
        $translationKeys[] = 'Marketplace_ShownPriceIsExclTax';
        $translationKeys[] = 'Marketplace_TryFreeTrialTitle';
        $translationKeys[] = 'CorePluginsAdmin_Activity';
        $translationKeys[] = 'CorePluginsAdmin_Version';
        $translationKeys[] = 'CorePluginsAdmin_Websites';
        $translationKeys[] = 'Marketplace_PluginLicenseStatusPending';
        $translationKeys[] = 'Marketplace_PluginLicenseStatusCancelled';
        $translationKeys[] = 'Marketplace_PluginDownloadLinkMissingPremium';
        $translationKeys[] = 'Marketplace_PluginDownloadLinkMissingFree';
        $translationKeys[] = 'Marketplace_PluginDownloadLinkMissingDescription';
        $translationKeys[] = 'Marketplace_CreatedBy';
    }

    /**
     * @param WidgetsList $list
     */
    public function filterWidgets($list)
    {
        if (!SettingsPiwik::isInternetEnabled()) {
            $list->remove('Marketplace_Marketplace');
        }
    }

    public function dismissPluginTrialNotification(): void
    {
        try {
            $notificationId = Request::fromRequest()->getStringParameter('notificationId');

            StaticContainer::get(PluginTrialService::class)->dismissNotification($notificationId);
        } catch (\Exception $e) {
            // ignore any type of error
        }
    }

    public function createPluginTrialNotification(): void
    {
        try {
            StaticContainer::get(PluginTrialService::class)->createNotificationsIfNeeded();
        } catch (\Exception $e) {
            // ignore any type of error
        }
    }

    public function removePluginTrialRequest(string $pluginName): void
    {
        try {
            StaticContainer::get(PluginTrialService::class)->cancelRequest($pluginName);
        } catch (\Exception $e) {
            // ignore any type of error
        }
    }

    public static function isMarketplaceEnabled()
    {
        return self::getPluginManager()->isPluginActivated('Marketplace');
    }

    /**
     * @return Plugin\Manager
     */
    private static function getPluginManager()
    {
        return Plugin\Manager::getInstance();
    }
}
