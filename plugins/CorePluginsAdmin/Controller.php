<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Piwik;
use Piwik\Common;
use Piwik\Config;
use Piwik\View;
use Piwik\Url;

/**
 *
 * @package CorePluginsAdmin
 */
class Controller extends \Piwik\Controller\Admin
{
    function extend()
    {
        $view = $this->configureView('@CorePluginsAdmin/extend');
        echo $view->render();
    }

    function plugins()
    {
        $view = $this->configureView('@CorePluginsAdmin/plugins');
        $view->pluginsInfo = $this->getPluginsInfo();
        echo $view->render();
    }

    function themes()
    {
        $view = $this->configureView('@CorePluginsAdmin/themes');
        $view->pluginsInfo = $this->getPluginsInfo($themesOnly = true);
        echo $view->render();
    }

    protected function configureView($template)
    {
        Piwik::checkUserIsSuperUser();
        $view = new View($template);
        $this->setBasicVariablesView($view);
        $this->displayWarningIfConfigFileNotWritable($view);
        return $view;
    }

    protected function getPluginsInfo($themesOnly = false)
    {
        $plugins = array();

        $listPlugins = array_merge(
            \Piwik\PluginsManager::getInstance()->readPluginsDirectory(),
            Config::getInstance()->Plugins['Plugins']
        );
        $listPlugins = array_unique($listPlugins);
        foreach ($listPlugins as $pluginName) {
            \Piwik\PluginsManager::getInstance()->loadPlugin($pluginName);
            $plugins[$pluginName] = array(
                'activated'       => \Piwik\PluginsManager::getInstance()->isPluginActivated($pluginName),
                'alwaysActivated' => \Piwik\PluginsManager::getInstance()->isPluginAlwaysActivated($pluginName),
                'uninstallable'   => \Piwik\PluginsManager::getInstance()->isPluginUninstallable($pluginName),
            );
        }
        \Piwik\PluginsManager::getInstance()->loadPluginTranslations();

        $loadedPlugins = \Piwik\PluginsManager::getInstance()->getLoadedPlugins();
        foreach ($loadedPlugins as $oPlugin) {
            $pluginName = $oPlugin->getPluginName();
            $plugins[$pluginName]['info'] = $oPlugin->getInformation();
        }

        foreach ($plugins as $pluginName => &$plugin) {
            if (!isset($plugin['info'])) {
                $plugin['info'] = array(
                    'description' => '<strong><em>' . Piwik_Translate('CorePluginsAdmin_PluginCannotBeFound')
                        . '</strong></em>',
                    'version'     => Piwik_Translate('General_Unknown'),
                    'theme'       => false,
                );
            }
        }

        $pluginsFiltered = $this->keepPluginsOrThemes($themesOnly, $plugins);
        return $pluginsFiltered;
    }

    protected function keepPluginsOrThemes($themesOnly, $plugins)
    {
        $pluginsFiltered = array();
        foreach ($plugins as $name => $thisPlugin) {

            $isTheme = false;
            if (!empty($thisPlugin['info']['theme'])) {
                $isTheme = (bool)$thisPlugin['info']['theme'];
            }
            if (($themesOnly && $isTheme)
                || (!$themesOnly && !$isTheme)
            ) {
                $pluginsFiltered[$name] = $thisPlugin;
            }
        }
        return $pluginsFiltered;
    }

    public function deactivate($redirectAfter = true)
    {
        $pluginName = $this->initPluginModification();
        \Piwik\PluginsManager::getInstance()->deactivatePlugin($pluginName);
        $this->redirectAfterModification($redirectAfter);
    }

    protected function redirectAfterModification($redirectAfter)
    {
        if ($redirectAfter) {
            Url::redirectToReferer();
        }
    }

    protected function initPluginModification()
    {
        Piwik::checkUserIsSuperUser();
        $this->checkTokenInUrl();
        $pluginName = Common::getRequestVar('pluginName', null, 'string');
        return $pluginName;
    }

    public function activate($redirectAfter = true)
    {
        $pluginName = $this->initPluginModification();
        \Piwik\PluginsManager::getInstance()->activatePlugin($pluginName);
        $this->redirectAfterModification($redirectAfter);
    }

    public function uninstall($redirectAfter = true)
    {
        $pluginName = $this->initPluginModification();
        $uninstalled = \Piwik\PluginsManager::getInstance()->uninstallPlugin($pluginName);
        if (!$uninstalled) {
            $path = Common::getPathToPiwikRoot() . '/plugins/' . $pluginName . '/';
            $messagePermissions = Piwik::getErrorMessageMissingPermissions($path);

            $messageIntro = Piwik_Translate("Warning: \"%s\" could not be uninstalled. Piwik did not have enough permission to delete the files in $path. ",
                $pluginName);
            $exitMessage = $messageIntro . "<br/><br/>" . $messagePermissions;
            Piwik_ExitWithMessage($exitMessage, $optionalTrace = false, $optionalLinks = false, $optionalLinkBack = true);
        }
        $this->redirectAfterModification($redirectAfter);
    }
}
