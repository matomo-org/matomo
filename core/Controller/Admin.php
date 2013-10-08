<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Controller;

use Piwik\Config;
use Piwik\Controller;
use Piwik\Piwik;
use Piwik\PluginsManager;
use Piwik\Url;
use Piwik\Version;
use Piwik\View;

/**
 * Parent class of all plugins Controllers with admin functions
 *
 * @package Piwik
 *
 * @api
 */
abstract class Admin extends Controller
{
    /**
     * Set the minimal variables in the view object
     * Extended by some admin view specific variables
     *
     * @param View $view
     */
    protected function setBasicVariablesView($view)
    {
        parent::setBasicVariablesView($view);

        self::setBasicVariablesAdminView($view);
    }

    static public function displayWarningIfConfigFileNotWritable(View $view)
    {
        $view->configFileNotWritable = !Config::getInstance()->isFileWritable();
    }

    static public function setBasicVariablesAdminView(View $view)
    {
        $statsEnabled = Config::getInstance()->Tracker['record_statistics'];
        if ($statsEnabled == "0") {
            $view->statisticsNotRecorded = true;
        }

        $view->topMenu = Piwik_GetTopMenu();
        $view->currentAdminMenuName = \Piwik\Menu\Admin::getInstance()->getCurrentAdminMenuName();

        $view->enableFrames = Config::getInstance()->General['enable_framed_settings'];
        if (!$view->enableFrames) {
            $view->setXFrameOptions('sameorigin');
        }

        $view->isSuperUser = Piwik::isUserIsSuperUser();

        // for old geoip plugin warning
        $view->usingOldGeoIPPlugin = PluginsManager::getInstance()->isPluginActivated('GeoIP');

        // for cannot find installed plugin warning
        $missingPlugins = PluginsManager::getInstance()->getMissingPlugins();
        if (!empty($missingPlugins)) {
            $pluginsLink = Url::getCurrentQueryStringWithParametersModified(array(
                                             'module' => 'CorePluginsAdmin', 'action' => 'plugins'
                                        ));
            $view->invalidPluginsWarning = Piwik_Translate('CoreAdminHome_InvalidPluginsWarning', array(
                                               self::getPiwikVersion(),
                                               '<strong>' . implode('</strong>,&nbsp;<strong>', $missingPlugins) . '</strong>'))
                                        . '<br/>'
                                        . Piwik_Translate('CoreAdminHome_InvalidPluginsYouCanUninstall', array(
                                               '<a href="' . $pluginsLink . '"/>',
                                               '</a>'
                                          ));
        }

        self::checkPhpVersion($view);

        $view->menu = Piwik_GetAdminMenu();
    }

    static protected function getPiwikVersion()
    {
        return "Piwik " . Version::VERSION;
    }


    /**
     * Check if the current PHP version is >= 5.3. If not, a warning is displayed
     * to the user.
     */
    private static function checkPhpVersion($view)
    {
        $view->phpVersion = PHP_VERSION;
        $view->phpIsNewEnough = version_compare($view->phpVersion, '5.3.0', '>=');
    }
}
