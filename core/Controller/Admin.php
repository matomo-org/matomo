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

/**
 * Parent class of all plugins Controllers with admin functions
 *
 * @package Piwik
 */
abstract class Piwik_Controller_Admin extends Piwik_Controller
{
    /**
     * Set the minimal variables in the view object
     * Extended by some admin view specific variables
     *
     * @param Piwik_View $view
     */
    protected function setBasicVariablesView($view)
    {
        parent::setBasicVariablesView($view);

        self::setBasicVariablesAdminView($view);
    }

    static public function setBasicVariablesAdminView($view)
    {
        $statsEnabled = Piwik_Config::getInstance()->Tracker['record_statistics'];
        if ($statsEnabled == "0") {
            $view->statisticsNotRecorded = true;
        }

        $view->topMenu = Piwik_GetTopMenu();
        $view->currentAdminMenuName = Piwik_GetCurrentAdminMenuName();

        $view->enableFrames = Piwik_Config::getInstance()->General['enable_framed_settings'];
        if (!$view->enableFrames) {
            $view->setXFrameOptions('sameorigin');
        }

        $view->isSuperUser = Piwik::isUserIsSuperUser();

        // for old geoip plugin warning
        $view->usingOldGeoIPPlugin = Piwik_PluginsManager::getInstance()->isPluginActivated('GeoIP');

        // for cannot find installed plugin warning
        $missingPlugins = Piwik_PluginsManager::getInstance()->getMissingPlugins();
        if (!empty($missingPlugins)) {
            $pluginsLink = Piwik_Url::getCurrentQueryStringWithParametersModified(array(
                                                                                       'module' => 'CorePluginsAdmin', 'action' => 'index'
                                                                                  ));
            $view->missingPluginsWarning = Piwik_Translate('CoreAdminHome_MissingPluginsWarning', array(
                                                                                                       '<strong>' . implode('</strong>,&nbsp;<strong>', $missingPlugins) . '</strong>',
                                                                                                       '<a href="' . $pluginsLink . '"/>',
                                                                                                       '</a>'
                                                                                                  ));
        }
        
        self::checkPhpVersion($view);
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
