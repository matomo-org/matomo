<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\Plugins\InvalidLicenses;
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
            'Installation.defaultSettingsForm.submit' => 'warmCacheAfterInstallation',
            'CoreUpdater.update.end' => 'warmCacheAfterUpdate',
            'Controller.CoreHome.markNotificationAsRead' => 'dismissPluginTrialNotification',
            'Request.dispatch' => 'createPluginTrialNotification',
            'PluginManager.pluginInstalled' => 'removePluginTrialRequest',
            'PluginManager.pluginActivated' => 'removePluginTrialRequest',
            'Widget.filterWidgets' => 'filterWidgets',
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

    /**
     * Keeps the overview lists through the flush and refreshes them in the background, so that a
     * click on "check for updates" leaves the next Marketplace page no emptier than it was.
     */
    public function checkForUpdates()
    {
        $client = StaticContainer::get(Api\Client::class);
        $client->clearCacheEntriesExceptOverviewLists();
        StaticContainer::get(InvalidLicenses::class)->clearCache();

        // not refreshed in the request: the flush also took the hold that stops repeated clicks each
        // fetching the lists. The age is checked here because it took the spawn hold too, so every
        // click would otherwise start a process. Where nothing can be spawned the kept lists stay
        // until a visit or the scheduler refreshes them
        $this->warmCacheSafely('the update check', function (BackgroundWarmer $warmer) use ($client) {
            $age = $client->getOverviewListsAge();

            if (null === $age || $age >= Api\Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS) {
                $warmer->refreshNow(Api\Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS);
            }
        }, StaticContainer::get(LoggerInterface::class));
    }

    /**
     * Warms the lists as the administrator leaves the installer. The hourly check would get there
     * much later: the scheduler only books a task it has never seen for its next full hour.
     */
    public function warmCacheAfterInstallation(): void
    {
        // anything thrown here would stop the installation from being marked as completed
        $this->warmCacheSafely('the installation', function (BackgroundWarmer $warmer) {
            // records this request's PHP version, which the child puts in its cache keys in place
            // of its own; no page has asked the Marketplace for anything yet to record it
            StaticContainer::get(Environment::class)->getWebPhpVersion();
            $warmer->refreshAfterInstallation();
        }, StaticContainer::get(LoggerInterface::class));
    }

    /**
     * Refills the lists soon after an update, which clears every cache and changes the cache keys
     * of a new Matomo version.
     */
    public function warmCacheAfterUpdate(): void
    {
        // the installer runs the updater too, while Matomo still keeps every cache in memory, and
        // warmCacheAfterInstallation() covers that case
        if (!SettingsPiwik::isMatomoInstalled()) {
            return;
        }

        // the logger is resolved now: by the time a shutdown function runs the container can be
        // gone, and looking it up from the catch would then throw out of the shutdown function
        $logger = StaticContainer::get(LoggerInterface::class);

        // the event fires while the updater is still in flight, and building a warmer - the
        // scheduler among it - from there has been seen to change what other plugins report: it
        // moved PrivacyManager's anonymisation settings in NoVisitTest
        $this->deferToEndOfRequest(function () use ($logger) {
            $this->warmCacheSafely('the update', function (BackgroundWarmer $warmer) {
                $warmer->refreshAfterUpdate(Api\Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS);
            }, $logger);
        });
    }

    /**
     * Separated so a test can run what was deferred, which a shutdown function gives it no way to.
     */
    protected function deferToEndOfRequest(callable $callback): void
    {
        register_shutdown_function($callback);
    }

    private function warmCacheSafely(string $occasion, callable $refresh, LoggerInterface $logger): void
    {
        try {
            $refresh(StaticContainer::get(BackgroundWarmer::class));
        } catch (\Throwable $e) {
            $logger->warning(
                'Could not warm the Marketplace cache after {occasion}: {message}',
                ['occasion' => $occasion, 'message' => $e->getMessage(), 'ignoreInScreenWriter' => true]
            );
        }
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Marketplace/stylesheets/marketplace.less";
        $stylesheets[] = "plugins/Marketplace/stylesheets/plugin-details.less";
        $stylesheets[] = "plugins/Marketplace/stylesheets/marketplace-widget.less";
        $stylesheets[] = "plugins/Marketplace/stylesheets/rich-menu-button.less";
        $stylesheets[] = "plugins/Marketplace/vue/src/PluginDetailsModal/ShopPricing.less";
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
        $translationKeys[] = 'General_ErrorRequest';
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
        $translationKeys[] = 'Marketplace_PayAnnually';
        $translationKeys[] = 'Marketplace_PayMonthly';
        $translationKeys[] = 'Marketplace_XMonthsFree';
        $translationKeys[] = 'Marketplace_OneMonthFree';
        $translationKeys[] = 'Marketplace_BillingPeriod';
        $translationKeys[] = 'Marketplace_PerMonthWithCurrency';
        $translationKeys[] = 'Marketplace_PerYearWithCurrency';
        $translationKeys[] = 'Marketplace_NumberOfUsers';
        $translationKeys[] = 'Marketplace_BilledAnnually';
        $translationKeys[] = 'Marketplace_BilledAnnuallyWithSavings';
        $translationKeys[] = 'SitesManager_Currency';
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
