<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 *
 */
class UserSettings extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
            'Live.getAllVisitorDetails'            => 'extendVisitorDetails',
            'Request.dispatch'                     => 'mapDeprecatedActions'
        );
    }

    /**
     * Maps the deprecated actions that were 'moved' to DevicesDetection plugin
     *
     * @deprecated since 2.10.0 and will be removed from May 1st 2015
     * @param $module
     * @param $action
     * @param $parameters
     */
    public function mapDeprecatedActions(&$module, &$action, &$parameters)
    {
        $movedMethods = array(
            'getBrowser' => 'getBrowsers',
            'getBrowserVersion' => 'getBrowserVersions',
            'getMobileVsDesktop' => 'getType',
            'getOS' => 'getOsVersions',
            'getOSFamily' => 'getOsFamilies',
            'getBrowserType' => 'getBrowserEngines'
        );

        if ($module == 'UserSettings' && array_key_exists($action, $movedMethods)) {
            $module = 'DevicesDetection';
            $action = $movedMethods[$action];
        }
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

        $visitor['screenType']               = $instance->getScreenType();
        $visitor['resolution']               = $instance->getResolution();
        $visitor['screenTypeIcon']           = $instance->getScreenTypeIcon();
        $visitor['plugins']                  = $instance->getPlugins();
        $visitor['pluginsIcons']             = $instance->getPluginIcons();
    }

    public function addMetricTranslations(&$translations)
    {
        $metrics = array(
            'nb_visits_percentage' => Piwik::translate('General_ColumnPercentageVisits')
        );

        $translations = array_merge($translations, $metrics);
    }

}
