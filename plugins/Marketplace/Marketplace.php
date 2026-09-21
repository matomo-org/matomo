<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugin;
use Piwik\Plugins\Marketplace\Plugins\InvalidLicenses;
use Piwik\Plugins\Marketplace\PluginTrial\Service as PluginTrialService;
use Piwik\Request;
use Piwik\Scheduler\Scheduler;
use Piwik\SettingsPiwik;
use Piwik\Widget\WidgetsList;
use Throwable;

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
            'Widget.filterWidgets' => 'filterWidgets',
            'CoreUpdater.update.end' => 'warmCacheAfterUpdate',
            'Installation.defaultSettingsForm.submit' => 'warmCacheAfterInstallation',
        );
    }

    public function warmCacheAfterUpdate(): void
    {
        // an installation runs the updater too, at a point where no site and no user exist yet.
        // Both counts are part of every Marketplace cache key, so warming from there would fill
        // entries the finished installation never reads back. warmCacheAfterInstallation() covers
        // that case, from a step late enough for the counts to have settled.
        if (!SettingsPiwik::isMatomoInstalled()) {
            return;
        }

        try {
            // deliberately not the CacheWarmer. This fires from inside Updater::updateComponents(),
            // and building the Marketplace container graph there changes what other plugins see -
            // it moved PrivacyManager's anonymisation settings in NoVisitTest. Naming the task
            // constructs nothing, so marking it due is safe from here, and it is what warms every
            // update that does not reach the spawn below.
            StaticContainer::get(Scheduler::class)
                ->rescheduleTaskAndRunNow(Tasks::getWarmCacheEntriesTask());
        } catch (Throwable $e) {
            // an update must not fail over a warm that is only an optimisation, but without a line
            // here there would be nothing to triage from if the timetable write kept failing
            self::logWarmFailure('Could not mark the Marketplace cache warming task due: {message}', $e);
        }

        // an update run from the command line is an unattended deployment as a rule: nobody is
        // waiting on a page, and spawning from here would have a fleet updating together reach the
        // Marketplace as each server finished. Each warms from its own scheduler instead, on a
        // cadence that is its own - a cron minute, or whatever traffic the instance happens to get.
        if (Common::isPhpCliMode()) {
            return;
        }

        // that run can be an hour away, and longer on an instance whose scheduler only runs from
        // tracking requests, so an administrator opening the Marketplace straight after an update
        // would still pay for a cold cache. The browser update therefore spawns the warm itself -
        // but only once this request has stopped updating, since it is the updater being in flight
        // that makes building the warmer unsafe above.
        $this->deferToEndOfRequest([self::class, 'warmCacheInBackground']);
    }

    /**
     * Runs $callback once this request has finished with it. Separated so a test can read what was
     * deferred, which a registered shutdown function gives it no way to see.
     */
    protected function deferToEndOfRequest(callable $callback): void
    {
        register_shutdown_function($callback);
    }

    /**
     * Spawns the background warm that {@link warmCacheAfterUpdate()} defers to the end of the
     * request. Public only because that is how it is registered.
     *
     * @internal
     */
    public static function warmCacheInBackground(): void
    {
        try {
            StaticContainer::get(CacheWarmer::class)->warmSoon();
        } catch (Throwable $e) {
            // only a container failure reaches here - a bad override, or a partial deploy - since
            // warmSoon() reports everything that goes wrong once it is running
            self::logWarmFailure('Could not build the Marketplace cache warmer: {message}', $e);
        }
    }

    public function warmCacheAfterInstallation(): void
    {
        try {
            // the full warmer straight away, unlike the update path above: this fires from the
            // installer's last step rather than from inside the updater, so building it here is
            // safe and the administrator about to land in Matomo gets the spawned warm rather than
            // waiting for a scheduler run that a brand new instance may not have for some time.
            StaticContainer::get(CacheWarmer::class)->warmSoon();
        } catch (Throwable $e) {
            // only a container failure reaches here - a bad override, or a partial deploy - since
            // warmSoon() reports everything that goes wrong once it is running. That would leave
            // the feature dead with nothing to show for it, so it is logged, but it must not fail
            // the installation that triggered it.
            self::logWarmFailure('Could not build the Marketplace cache warmer: {message}', $e);
        }
    }

    private static function logWarmFailure(string $message, Throwable $e): void
    {
        try {
            StaticContainer::get(LoggerInterface::class)->debug($message, ['message' => $e->getMessage()]);
        } catch (Throwable $ignored) {
            // the container is what failed, so it cannot be relied on to report it either
        }
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

        StaticContainer::get(InvalidLicenses::class)->clearCache();
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
