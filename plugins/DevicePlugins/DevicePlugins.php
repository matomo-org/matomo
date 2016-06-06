<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicePlugins;

use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 *
 */
class DevicePlugins extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
            'Live.getAllVisitorDetails'            => 'extendVisitorDetails',
            'Request.getRenamedModuleAndAction' => 'renameUserSettingsModuleAndAction',
        );
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

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

    public function renameUserSettingsModuleAndAction(&$module, &$action)
    {
        if ($module == 'UserSettings' && $action == 'getPlugin') {
            $module = 'DevicePlugins';
        }
    }
}
