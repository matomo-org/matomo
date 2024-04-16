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
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CoreHome\SystemSummary;
use Piwik\Plugins\CorePluginsAdmin\Model\TagManagerTeaser;
use Piwik\Changes\Model as ChangesModel;

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
            'Updater.componentUpdated'               => 'addPluginChanges',
            'PluginManager.pluginActivated'          => 'onPluginActivated',
            'PluginManager.pluginDeactivated'        => 'removePluginChanges'
        );
    }

    /**
     * Add any changes from newly activated or updated plugins to the changes table
     *
     * @param string $pluginName The name of the plugin that was updated or activated
     */
    public function addPluginChanges(string $pluginName)
    {
        $this->getChangesModel()->addChanges($pluginName);
    }

    /**
     * Remove any changes from a plugin that has been uninstalled
     *
     * @param string $pluginName The name of the plugin that was uninstalled
     */
    public function removePluginChanges(string $pluginName)
    {
        $this->getChangesModel()->removeChanges($pluginName);
    }

    /**
     * Retrieve an instantiated ChangesModel object
     *
     * @return ChangesModel
     */
    private function getChangesModel(): ChangesModel
    {
        return StaticContainer::get(\Piwik\Changes\Model::class);
    }

    public function onPluginActivated($pluginName)
    {
        if ($pluginName === 'TagManager') {
            // make sure once activated once, it won't appear when disabling Tag Manager later
            $tagManagerTeaser = new TagManagerTeaser(Piwik::getCurrentUserLogin());
            $tagManagerTeaser->disableGlobally();
        }

        $this->addPluginChanges($pluginName);
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
        $translations[] = 'CorePluginsAdmin_Activate';
        $translations[] = 'CorePluginsAdmin_Deactivate';
        $translations[] = 'CorePluginsAdmin_PluginsExtendPiwik';
        $translations[] = 'CorePluginsAdmin_OncePluginIsInstalledYouMayActivateHere';
        $translations[] = 'CorePluginsAdmin_TeaserExtendPiwikByPlugin';
        $translations[] = 'CorePluginsAdmin_DoMoreContactPiwikAdmins';
        $translations[] = 'CorePluginsAdmin_ChangeLookByManageThemes';
        $translations[] = 'CorePluginsAdmin_InfoPluginUpdateIsRecommended';
        $translations[] = 'CorePluginsAdmin_UpdateSelected';
        $translations[] = 'General_Plugin';
        $translations[] = 'CorePluginsAdmin_Version';
        $translations[] = 'General_Description';
        $translations[] = 'CorePluginsAdmin_Status';
        $translations[] = 'CorePluginsAdmin_Changelog';
        $translations[] = 'CorePluginsAdmin_Active';
        $translations[] = 'CorePluginsAdmin_Inactive';
        $translations[] = 'CorePluginsAdmin_PluginNotDownloadable';
        $translations[] = 'CorePluginsAdmin_PluginNotDownloadablePaidReason';
        $translations[] = 'CorePluginsAdmin_NotDownloadable';
        $translations[] = 'General_Download';
        $translations[] = 'CoreUpdater_UpdateTitle';
        $translations[] = 'CorePluginsAdmin_InstalledPlugins';
        $translations[] = 'CorePluginsAdmin_Origin';
        $translations[] = 'CorePluginsAdmin_OriginCore';
        $translations[] = 'CorePluginsAdmin_OriginOfficial';
        $translations[] = 'CorePluginsAdmin_OriginThirdParty';
        $translations[] = 'CorePluginsAdmin_UninstallConfirm';
        $translations[] = 'CorePluginsAdmin_Theme';
        $translations[] = 'CorePluginsAdmin_CorePluginTooltip';
        $translations[] = 'General_Settings';
        $translations[] = 'CorePluginsAdmin_PluginHomepage';
        $translations[] = 'CorePluginsAdmin_LikeThisPlugin';
        $translations[] = 'CorePluginsAdmin_ConsiderDonating';
        $translations[] = 'CorePluginsAdmin_CommunityContributedPlugin';
        $translations[] = 'CorePluginsAdmin_ConsiderDonatingCreatorOf';
        $translations[] = 'General_Close';
        $translations[] = 'CorePluginsAdmin_LicenseHomepage';
        $translations[] = 'CorePluginsAdmin_AuthorHomepage';
        $translations[] = 'CorePluginsAdmin_ActionUninstall';
        $translations[] = 'CorePluginsAdmin_InstallNewThemes';
        $translations[] = 'CorePluginsAdmin_InstallNewPlugins';
        $translations[] = 'CorePluginsAdmin_AlwaysActivatedPluginsList';
        $translations[] = 'CorePluginsAdmin_PluginsManagement';
        $translations[] = 'CorePluginsAdmin_ThemesDescription';
        $translations[] = 'CorePluginsAdmin_TeaserExtendPiwikByTheme';
        $translations[] = 'CorePluginsAdmin_InfoThemeIsUsedByOtherUsersAsWell';
        $translations[] = 'CorePluginsAdmin_ThemesManagement';
        $translations[] = 'CorePluginsAdmin_NUpdatesAvailable';
        $translations[] = 'CorePluginsAdmin_PluginFreeTrialStarted';
        $translations[] = 'CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedMessage';
        $translations[] = 'CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedTitle';
    }
}
