<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CoreHome\SystemSummary;
use Piwik\Plugins\CorePluginsAdmin\Model\TagManagerTeaser;
use Piwik\Changes\Model as ChangesModel;
use Piwik\Db;
use Piwik\Plugin\Manager as PluginManager;

class CorePluginsAdmin extends Plugin
{
    /**
     * @see Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'System.addSystemSummaryItems'           => 'addSystemSummaryItems',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'PluginManager.pluginActivated'          => 'onPluginActivated',
            'PluginManager.pluginInstalled'          => 'addPluginChanges',
            'Updater.componentUpdated'               => 'addPluginChanges',
            'PluginManager.pluginUninstalled'        => 'removePluginChanges'
        );
    }

    /**
     * Add any changes from newly installed or updated plugins to the changes table
     *
     * @param string $pluginName The name of the plugin that was updated or installed
     */
    public function addPluginChanges(string $pluginName)
    {
        $changes = new ChangesModel(Db::get(), PluginManager::getInstance());
        $changes->addChanges($pluginName);
    }

    /**
     * Remove any changes from a plugin that has been uninstalled
     *
     * @param string $pluginName The name of the plugin that was uninstalled
     */
    public function removePluginChanges(string $pluginName)
    {
        $changes = new ChangesModel(Db::get(), PluginManager::getInstance());
        $changes->removeChanges($pluginName);
    }

    public function onPluginActivated($pluginName)
    {
        if ($pluginName === 'TagManager') {
            // make sure once activated once, it won't appear when disabling Tag Manager later 
            $tagManagerTeaser = new TagManagerTeaser(Piwik::getCurrentUserLogin());
            $tagManagerTeaser->disableGlobally();
        }
    }

    public function addSystemSummaryItems(&$systemSummary)
    {
        $numPlugins = Plugin\Manager::getInstance()->getNumberOfActivatedPluginsExcludingAlwaysActivated();
        $systemSummary[] = new SystemSummary\Item($key = 'plugins', Piwik::translate('CoreHome_SystemSummaryNActivatedPlugins', $numPlugins), $value = null, $url = array('module' => 'CorePluginsAdmin', 'action' => 'plugins'), $icon = '', $order = 11);
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/CorePluginsAdmin/stylesheets/plugins_admin.less";
        $stylesheets[] = "plugins/CorePluginsAdmin/vue/src/PluginSettings/PluginSettings.less";
        $stylesheets[] = "plugins/CorePluginsAdmin/vue/src/FormField/FieldExpandableSelect.less";
        $stylesheets[] = "plugins/CorePluginsAdmin/vue/src/FormField/FieldMultituple.less";
        $stylesheets[] = "plugins/CorePluginsAdmin/vue/src/FormField/FieldSelect.less";
        $stylesheets[] = "plugins/CorePluginsAdmin/vue/src/PasswordConfirmation/PasswordConfirmation.less";
    }

    public static function isPluginsAdminEnabled()
    {
        return (bool) Config::getInstance()->General['enable_plugins_admin'];
    }

    public static function isPluginUploadEnabled()
    {
        return (bool) Config::getInstance()->General['enable_plugin_upload'];
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/jquery.dotdotdot/dist/jquery.dotdotdot.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/popover.js";
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'CorePluginsAdmin_NoZipFileSelected';
        $translations[] = 'CorePluginsAdmin_FileExceedsUploadLimit';
        $translations[] = 'CorePluginsAdmin_NoPluginSettings';
        $translations[] = 'CoreAdminHome_PluginSettingsIntro';
        $translations[] = 'CoreAdminHome_PluginSettingsSaveSuccess';
        $translations[] = 'General_Save';
    }

}
