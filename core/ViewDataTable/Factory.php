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
namespace Piwik\ViewDataTable;

use Piwik\API\Proxy;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 * TODO
 *
 * @package Piwik
 * @subpackage ViewDataTable
 *
 * @api
 */
class Factory
{

    /**
     * Cache for getDefaultTypeViewDataTable result.
     *
     * @var array
     */
    private static $defaultViewTypes = null;

    /**
     * Returns a Piwik_ViewDataTable_* object.
     * By default it will return a ViewDataTable_Html
     * If there is a viewDataTable parameter in the URL, a ViewDataTable of this 'viewDataTable' type will be returned.
     * If defaultType is specified and if there is no 'viewDataTable' in the URL, a ViewDataTable of this $defaultType will be returned.
     * If force is set to true, a ViewDataTable of the $defaultType will be returned in all cases.
     *
     * @param string $defaultType Any of these: table, cloud, graphPie, graphVerticalBar, graphEvolution, sparkline, generateDataChart*
     * @param string|bool $apiAction
     * @param string|bool $controllerAction
     * @param bool $forceDefault
     *
     * @throws \Exception
     * @return \Piwik\Plugin\ViewDataTable|\Piwik\Plugin\Visualization|\Piwik\Plugins\CoreVisualizations\Visualizations\Sparkline;
     */
    public static function build($defaultType = null, $apiAction = false, $controllerAction = false, $forceDefault = false)
    {
        if (false === $controllerAction) {
            $controllerAction = $apiAction;
        }

        $defaultViewType = self::getDefaultViewTypeForReport($apiAction);

        if (!$forceDefault && !empty($defaultViewType)) {
            $defaultType = $defaultViewType;
        }

        $type = Common::getRequestVar('viewDataTable', false, 'string');
        // Common::getRequestVar removes backslashes from the defaultValue in case magic quotes are enabled.
        // therefore do not pass this as a default value to getRequestVar()
        if ('' === $type) {
            $type = $defaultType ? : HtmlTable::ID;
        }

        $visualizations = Manager::getAvailableViewDataTables();

        if (array_key_exists($type, $visualizations)) {
            return new $visualizations[$type]($controllerAction, $apiAction);
        }

        if (class_exists($type)) {
            return new $type($controllerAction, $apiAction);
        }

        if (array_key_exists($defaultType, $visualizations)) {
            return new $visualizations[$defaultType]($controllerAction, $apiAction);
        }

        if (array_key_exists(HtmlTable::ID, $visualizations)) {
            return new $visualizations[HtmlTable::ID]($controllerAction, $apiAction);
        }

        throw new \Exception('No visualization found to render ViewDataTable');
    }

    /**
     * Returns the default viewDataTable ID to use when determining which visualization to use.
     */
    private static function getDefaultViewTypeForReport($apiAction)
    {
        $defaultViewTypes = self::getDefaultTypeViewDataTable();
        return isset($defaultViewTypes[$apiAction]) ? $defaultViewTypes[$apiAction] : false;
    }

    /**
     * Returns a list of default viewDataTables ID to use when determining which visualization to use for multiple
     * reports.
     */
    private static function getDefaultTypeViewDataTable()
    {
        if (null === self::$defaultViewTypes) {
            self::$defaultViewTypes = array();
            /**
             * Triggered when gathering the default view types for all available reports. By default the HtmlTable
             * visualization is used. If you define your own report, you may want to subscribe to this event to
             * make sure another Visualization is used (for example, a pie graph, bar graph, or something else).
             *
             * **Example**
             * ```
             * public function getDefaultTypeViewDataTable(&$defaultViewTypes)
             * {
             *     $defaultViewTypes['Referrers.getSocials']       = HtmlTable::ID;
             *     $defaultViewTypes['Referrers.getUrlsForSocial'] = Pie::ID;
             * }
             * ```
             * 
             * @param array &$defaultViewTypes The array mapping report IDs with visualization IDs.
             */
            Piwik::postEvent('ViewDataTable.getDefaultType', array(&self::$defaultViewTypes));
        }

        return self::$defaultViewTypes;
    }
}