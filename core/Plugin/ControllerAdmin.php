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
namespace Piwik\Plugin;

use Piwik\Config;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
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
abstract class ControllerAdmin extends Controller
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

        $view->topMenu = MenuTop::getInstance()->getMenu();
        $view->currentAdminMenuName = MenuAdmin::getInstance()->getCurrentAdminMenuName();

        $view->enableFrames = Config::getInstance()->General['enable_framed_settings'];
        if (!$view->enableFrames) {
            $view->setXFrameOptions('sameorigin');
        }

        $view->isSuperUser = Piwik::isUserIsSuperUser();

        // for old geoip plugin warning
        $view->usingOldGeoIPPlugin = \Piwik\Plugin\Manager::getInstance()->isPluginActivated('GeoIP');

        // for cannot find installed plugin warning
        $missingPlugins = \Piwik\Plugin\Manager::getInstance()->getMissingPlugins();
        if (!empty($missingPlugins)) {
            $pluginsLink = Url::getCurrentQueryStringWithParametersModified(array(
                                                                                 'module' => 'CorePluginsAdmin', 'action' => 'plugins'
                                                                            ));
            $view->invalidPluginsWarning = Piwik::translate('CoreAdminHome_InvalidPluginsWarning', array(
                                                                                                       self::getPiwikVersion(),
                                                                                                       '<strong>' . implode('</strong>,&nbsp;<strong>', $missingPlugins) . '</strong>'))
                . '<br/>'
                . Piwik::translate('CoreAdminHome_InvalidPluginsYouCanUninstall', array(
                                                                                      '<a href="' . $pluginsLink . '"/>',
                                                                                      '</a>'
                                                                                 ));
        }

        self::checkPhpVersion($view);

        $adminMenu = MenuAdmin::getInstance()->getMenu();
        $view->adminMenu = $adminMenu;
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
