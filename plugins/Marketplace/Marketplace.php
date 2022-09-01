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
