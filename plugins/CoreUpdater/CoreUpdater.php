<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater;

use Exception;
use Piwik\Access;
use Piwik\Common;
use Piwik\Filesystem;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Columns\Updater as ColumnsUpdater;
use Piwik\ScheduledTime;
use Piwik\UpdateCheck;
use Piwik\Updater;
use Piwik\UpdaterErrorException;
use Piwik\Version;

/**
 *
 */
class CoreUpdater extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Request.dispatchCoreAndPluginUpdatesScreen' => 'dispatch',
            'Platform.initialized'                       => 'updateCheck',
        );
    }

    public static function updateComponents(Updater $updater, $componentsWithUpdateFile)
    {
        $warnings = array();
        $errors   = array();
        $deactivatedPlugins = array();
        $coreError = false;

        if (!empty($componentsWithUpdateFile)) {
            $currentAccess      = Access::getInstance();
            $hasSuperUserAccess = $currentAccess->hasSuperUserAccess();

            if (!$hasSuperUserAccess) {
                $currentAccess->setSuperUserAccess(true);
            }

            // if error in any core update, show message + help message + EXIT
            // if errors in any plugins updates, show them on screen, disable plugins that errored + CONTINUE
            // if warning in any core update or in any plugins update, show message + CONTINUE
            // if no error or warning, success message + CONTINUE
            foreach ($componentsWithUpdateFile as $name => $filenames) {
                try {
                    $warnings = array_merge($warnings, $updater->update($name));
                } catch (UpdaterErrorException $e) {
                    $errors[] = $e->getMessage();
                    if ($name == 'core') {
                        $coreError = true;
                        break;
                    } elseif (\Piwik\Plugin\Manager::getInstance()->isPluginActivated($name)) {
                        \Piwik\Plugin\Manager::getInstance()->deactivatePlugin($name);
                        $deactivatedPlugins[] = $name;
                    }
                }
            }

            if (!$hasSuperUserAccess) {
                $currentAccess->setSuperUserAccess(false);
            }
        }

        Filesystem::deleteAllCacheOnUpdate();

        $result = array(
            'warnings'  => $warnings,
            'errors'    => $errors,
            'coreError' => $coreError,
            'deactivatedPlugins' => $deactivatedPlugins
        );

        return $result;
    }

    public static function getComponentUpdates(Updater $updater)
    {
        $updater->addComponentToCheck('core', Version::VERSION);
        $manager = \Piwik\Plugin\Manager::getInstance();
        $plugins = $manager->getLoadedPlugins();
        foreach ($plugins as $pluginName => $plugin) {
            if ($manager->isPluginInstalled($pluginName)) {
                $updater->addComponentToCheck($pluginName, $plugin->getVersion());
            }
        }

        $columnsVersions = ColumnsUpdater::getAllVersions();
        foreach ($columnsVersions as $component => $version) {
            $updater->addComponentToCheck($component, $version);
        }

        $componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();

        if (count($componentsWithUpdateFile) == 0) {
            ColumnsUpdater::onNoUpdateAvailable($columnsVersions);

            if (!$updater->hasNewVersion('core')) {
                return null;
            }
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
            // Do not show update page during installation.
            && $module != 'Installation'
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
