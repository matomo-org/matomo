<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\ProxyHttp;
use Piwik\Settings\Plugin\UserSetting;
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
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    public function cleanupUser($userLogin)
    {
        PluginSettingsTable::removeAllUserSettingsForUser($userLogin);
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "libs/jquery/themes/base/jquery-ui.min.css";
        $stylesheets[] = "plugins/Morpheus/stylesheets/base.less";
        $stylesheets[] = "plugins/Morpheus/stylesheets/main.less";
        $stylesheets[] = "plugins/CoreAdminHome/stylesheets/generalSettings.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/bower_components/jquery/dist/jquery.min.js";
        $jsFiles[] = "libs/bower_components/jquery-ui/ui/minified/jquery-ui.min.js";
        $jsFiles[] = "libs/jquery/jquery.browser.js";
        $jsFiles[] = "libs/bower_components/sprintf/dist/sprintf.min.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/piwikHelper.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/ajaxHelper.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/broadcast.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/donate.js";
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
    }
}
