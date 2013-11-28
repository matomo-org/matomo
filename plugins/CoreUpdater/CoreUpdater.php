<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreUpdater
 */
namespace Piwik\Plugins\CoreUpdater;

use Exception;
use Piwik\Common;
use Piwik\Filesystem;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\UpdateCheck;
use Piwik\Updater;
use Piwik\Version;

/**
 *
 * @package CoreUpdater
 */
class CoreUpdater extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Request.dispatchCoreAndPluginUpdatesScreen' => 'dispatch',
            'Updater.checkForUpdates'                    => 'updateCheck',
        );
        return $hooks;
    }

    public static function getComponentUpdates(Updater $updater)
    {
        $updater->addComponentToCheck('core', Version::VERSION);
        $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPlugins();
        foreach ($plugins as $pluginName => $plugin) {
            $updater->addComponentToCheck($pluginName, $plugin->getVersion());
        }

        $componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();
        if (count($componentsWithUpdateFile) == 0 && !$updater->hasNewVersion('core')) {
            return null;
        }

        return $componentsWithUpdateFile;
    }

    public function dispatch()
    {
        $module = Common::getRequestVar('module', '', 'string');
        $action = Common::getRequestVar('action', '', 'string');

        $updater = new Updater();
        $updater->addComponentToCheck('core', Version::VERSION);
        $updates = $updater->getComponentsWithNewVersion();
        if (!empty($updates)) {
            Filesystem::deleteAllCacheOnUpdate();
        }
        if (self::getComponentUpdates($updater) !== null
            && $module != 'CoreUpdater'
            // Proxy module is used to redirect users to piwik.org, should still work when Piwik must be updated
            && $module != 'Proxy'
            && !($module == 'LanguagesManager'
                && $action == 'saveLanguage')
        ) {
            if (FrontController::shouldRethrowException()) {
                throw new Exception("Piwik and/or some plugins have been upgraded to a new version. \n" .
                    "--> Please run the update process first. See documentation: http://piwik.org/docs/update/ \n");
            } else {
                Piwik::redirectToModule('CoreUpdater');
            }
        }
    }

    public function updateCheck()
    {
        UpdateCheck::check();
    }
}
