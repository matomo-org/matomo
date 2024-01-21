<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Container\StaticContainer;
use Piwik\Plugin;
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
        $translationKeys[] = 'Marketplace_LicenseKeyActivatedSuccess';
        $translationKeys[] = 'Marketplace_LicenseKeyDeletedSuccess';
        $translationKeys[] = 'Marketplace_Show';
        $translationKeys[] = 'Marketplace_Sort';
        $translationKeys[] = 'General_Plugins';
        $translationKeys[] = 'Marketplace_PaidPluginsNoLicenseKeyIntro';
        $translationKeys[] = 'Marketplace_PaidPluginsWithLicenseKeyIntro';
        $translationKeys[] = 'Marketplace_RemoveLicenseKey';
        $translationKeys[] = 'Marketplace_ViewSubscriptions';
        $translationKeys[] = 'Marketplace_InstallPurchasedPlugins';
        $translationKeys[] = 'Marketplace_InstallAllPurchasedPlugins';
        $translationKeys[] = 'Marketplace_InstallThesePlugins';
        $translationKeys[] = 'Marketplace_InstallAllPurchasedPluginsAction';
        $translationKeys[] = 'Marketplace_ConfirmRemoveLicense';
        $translationKeys[] = 'Marketplace_PaidPluginsNoLicenseKeyIntroNoSuperUserAccess';
        $translationKeys[] = 'Marketplace_LicenseKeyIsValidShort';
        $translationKeys[] = 'Marketplace_LicenseKey';
        $translationKeys[] = 'CoreUpdater_UpdateTitle';
        $translationKeys[] = 'Marketplace_ActivateLicenseKey';
        $translationKeys[] = 'Marketplace_TeaserExtendPiwikByUpload';
        $translationKeys[] = 'Marketplace_AllowedUploadFormats';
        $translationKeys[] = 'Login_ConfirmPasswordToContinue';
        $translationKeys[] = 'Marketplace_UploadZipFile';
        $translationKeys[] = 'Marketplace_PluginUploadDisabled';
        $translationKeys[] = 'General_Ok';
        $translationKeys[] = 'CorePluginsAdmin_Marketplace';
        $translationKeys[] = 'Marketplace_NotAllowedToBrowseMarketplaceThemes';
        $translationKeys[] = 'Marketplace_NotAllowedToBrowseMarketplacePlugins';
        $translationKeys[] = 'CorePluginsAdmin_ThemesDescription';
        $translationKeys[] = 'Marketplace_InstallingNewThemesViaMarketplaceOrUpload';
        $translationKeys[] = 'Marketplace_InstallingNewPluginsViaMarketplaceOrUpload';
        $translationKeys[] = 'CorePluginsAdmin_Themes';
        $translationKeys[] = 'CorePluginsAdmin_Theme';
        $translationKeys[] = 'CorePluginsAdmin_PluginsExtendPiwik';
        $translationKeys[] = 'General_Plugin';
        $translationKeys[] = 'Marketplace_NoticeRemoveMarketplaceFromReportingMenu';
        $translationKeys[] = 'CorePluginsAdmin_ViewAllMarketplacePlugins';
        $translationKeys[] = 'Marketplace_TrialHints';
        $translationKeys[] = 'Marketplace_SupportMatomoThankYou';
        $translationKeys[] = 'Marketplace_SpecialOffer';
        $translationKeys[] = 'Marketplace_OverviewPluginSubscriptions';
        $translationKeys[] = 'Marketplace_PluginSubscriptionsList';
        $translationKeys[] = 'Marketplace_OverviewPluginSubscriptionsAllDetails';
        $translationKeys[] = 'Marketplace_OverviewPluginSubscriptionsMissingInfo';
        $translationKeys[] = 'Marketplace_NoValidSubscriptionNoUpdates';
        $translationKeys[] = 'Marketplace_CurrentNumPiwikUsers';
        $translationKeys[] = 'Marketplace_SubscriptionType';
        $translationKeys[] = 'CorePluginsAdmin_Status';
        $translationKeys[] = 'Marketplace_SubscriptionStartDate';
        $translationKeys[] = 'Marketplace_SubscriptionEndDate';
        $translationKeys[] = 'Marketplace_SubscriptionNextPaymentDate';
        $translationKeys[] = 'Marketplace_SubscriptionInvalid';
        $translationKeys[] = 'Marketplace_SubscriptionExpiresSoon';
        $translationKeys[] = 'Marketplace_LicenseExceededPossibleCause';
        $translationKeys[] = 'Marketplace_Exceeded';
        $translationKeys[] = 'Marketplace_LicenseRenewsNextPaymentDate';
        $translationKeys[] = 'Marketplace_NoSubscriptionsFound';
        $translationKeys[] = 'Marketplace_BrowseMarketplace';
        $translationKeys[] = 'Marketplace_OverviewPluginSubscriptionsMissingLicense';
        $translationKeys[] = 'CorePluginsAdmin_MissingRequirementsNotice';
        $translationKeys[] = 'Marketplace_Marketplace';
        $translationKeys[] = 'Marketplace_RichMenuIntro';
        $translationKeys[] = 'Marketplace_ManageLicenseKeyIntro';
        $translationKeys[] = 'Marketplace_Free';
        $translationKeys[] = 'Marketplace_StartFreeTrial';
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
