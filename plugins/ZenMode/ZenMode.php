<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ZenMode;

/**
 */
class ZenMode extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'SitesManager_Sites';
        $translations[] = 'General_Reports';
        $translations[] = 'MultiSites_LoadingWebsites';
        $translations[] = 'ZenMode_SearchForAnything';
        $translations[] = 'ZenMode_QuickAccessTitle';
        $translations[] = 'ZenMode_HowToSearch';
        $translations[] = 'ZenMode_HowToToggleZenMode';
        $translations[] = 'ZenMode_Activated';
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/ZenMode/javascripts/zen-mode.js";
        $jsFiles[] = "plugins/ZenMode/angularjs/quick-access/quick-access.directive.js";
        $jsFiles[] = "plugins/ZenMode/angularjs/zen-mode/zen-mode-switcher.directive.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/ZenMode/angularjs/quick-access/quick-access.directive.less";
        $stylesheets[] = "plugins/ZenMode/angularjs/zen-mode/zen-mode.less";
    }
}
