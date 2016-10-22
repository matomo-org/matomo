<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Plugin;

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
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Marketplace/stylesheets/marketplace.less";
        $stylesheets[] = "plugins/Marketplace/stylesheets/plugin-details.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/bower_components/iframe-resizer/js/iframeResizer.min.js";

        $jsFiles[] = "plugins/Marketplace/javascripts/licensekey.js";
        $jsFiles[] = "plugins/Marketplace/javascripts/marketplace.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Marketplace_LicenseKeyActivatedSuccess';
        $translationKeys[] = 'Marketplace_LicenseKeyDeletedSuccess';
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
