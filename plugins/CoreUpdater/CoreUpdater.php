<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreUpdater
 */

/**
 *
 * @package Piwik_CoreUpdater
 */
class Piwik_CoreUpdater extends Piwik_Plugin
{
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('CoreUpdater_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    function getListHooksRegistered()
    {
        $hooks = array(
            'FrontController.dispatchCoreAndPluginUpdatesScreen' => 'dispatch',
            'FrontController.checkForUpdates'                    => 'updateCheck',
        );
        return $hooks;
    }

    public static function getComponentUpdates($updater)
    {
        $updater->addComponentToCheck('core', Piwik_Version::VERSION);
        $plugins = Piwik_PluginsManager::getInstance()->getLoadedPlugins();
        foreach ($plugins as $pluginName => $plugin) {
            $updater->addComponentToCheck($pluginName, $plugin->getVersion());
        }

        $componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();
        if (count($componentsWithUpdateFile) == 0 && !$updater->hasNewVersion('core')) {
            return null;
        }

        return $componentsWithUpdateFile;
    }

    function dispatch()
    {
        $module = Piwik_Common::getRequestVar('module', '', 'string');
        $action = Piwik_Common::getRequestVar('action', '', 'string');

        $updater = new Piwik_Updater();
        $updater->addComponentToCheck('core', Piwik_Version::VERSION);
        $updates = $updater->getComponentsWithNewVersion();
        if (!empty($updates)) {
            Piwik::deleteAllCacheOnUpdate();
        }
        if (self::getComponentUpdates($updater) !== null
            && $module != 'CoreUpdater'
            // Proxy module is used to redirect users to piwik.org, should still work when Piwik must be updated
            && $module != 'Proxy'
            && !($module == 'LanguagesManager'
                && $action == 'saveLanguage')
        ) {
            if (Piwik_FrontController::shouldRethrowException()) {
                throw new Exception("Piwik and/or some plugins have been upgraded to a new version. \n".
                    "--> Please run the update process first. See documentation: http://piwik.org/docs/update/ \n");
            } else {
                Piwik::redirectToModule('CoreUpdater');
            }
        }
    }

    function updateCheck()
    {
        Piwik_UpdateCheck::check();
    }
}
