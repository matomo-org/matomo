<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ViewDataTable;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 * Provides a means of creating {@link Piwik\Plugin\ViewDataTable} instances by ID.
 *
 * ### Examples
 *
 * **Creating a ViewDataTable for a report**
 *
 *     // method in MyPlugin\Controller
 *     public function myReport()
 *     {
 *         $view = Factory::build('table', 'MyPlugin.myReport');
 *         $view->config->show_limit_control = true;
 *         $view->config->translations['myFancyMetric'] = "My Fancy Metric";
 *         return $view->render();
 *     }
 *
 * **Displaying a report in another way**
 *
 *     // method in MyPlugin\Controller
 *     // use the same data that's used in myReport() above, but transform it in some way before
 *     // displaying.
 *     public function myReportShownDifferently()
 *     {
 *         $view = Factory::build('table', 'MyPlugin.myReport', 'MyPlugin.myReportShownDifferently');
 *         $view->config->filters[] = array('MyMagicFilter', array('an arg', 'another arg'));
 *         return $view->render();
 *     }
 *
 * **Force a report to be shown as a bar graph**
 *
 *     // method in MyPlugin\Controller
 *     // force the myReport report to show as a bar graph if there is no viewDataTable query param,
 *     // even though it is configured to show as a table.
 *     public function myReportShownAsABarGraph()
 *     {
 *         $view = Factory::build('graphVerticalBar', 'MyPlugin.myReport', 'MyPlugin.myReportShownAsABarGraph',
 *                                $forceDefault = true);
 *         return $view->render();
 *     }
 *
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
     * Creates a {@link Piwik\Plugin\ViewDataTable} instance by ID. If the **viewDataTable** query parameter is set,
     * this parameter's value is used as the ID.
     *
     * See {@link Piwik\Plugin\ViewDataTable} to read about the visualizations that are packaged with Piwik.
     *
     * @param string|null $defaultType A ViewDataTable ID representing the default ViewDataTable type to use. If
     *                                 the **viewDataTable** query parameter is not found, this value is used as
     *                                 the ID of the ViewDataTable to create.
     *
     *                                 If a visualization type is configured for the report being displayed, it
     *                                 is used instead of the default type. (See {@hook ViewDataTable.getDefaultType}).
     *                                 If nothing is configured for the report and `null` is supplied for this
     *                                 argument, **table** is used.
     * @param bool|false|string $apiAction The API method for the report that will be displayed, eg,
     *                               `'UserSettings.getBrowser'`.
     * @param bool|false|string $controllerAction The controller name and action dedicated to displaying the report. This
     *                                       action is used when reloading reports or changing the report visualization.
     *                                       Defaulted to `$apiAction` if `false` is supplied.
     * @param bool $forceDefault If true, then the visualization type that was configured for the report will be
     *                           ignored and `$defaultType` will be used as the default.
     * @throws \Exception
     * @return \Piwik\Plugin\ViewDataTable
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

        $isWidget = Common::getRequestVar('widget', '0', 'string');

        if (!empty($isWidget)) {
            $params = array();
        } else {
            $login  = Piwik::getCurrentUserLogin();
            $params = Manager::getViewDataTableParameters($login, $controllerAction);
        }

        $savedViewDataTable = false;
        if (!empty($params['viewDataTable'])) {
            $savedViewDataTable = $params['viewDataTable'];
        }

        $type = Common::getRequestVar('viewDataTable', $savedViewDataTable, 'string');

        // Common::getRequestVar removes backslashes from the defaultValue in case magic quotes are enabled.
        // therefore do not pass this as a default value to getRequestVar()
        if ('' === $type) {
            $type = $defaultType ? : HtmlTable::ID;
        }

        $visualizations = Manager::getAvailableViewDataTables();

        if (array_key_exists($type, $visualizations)) {
            return self::createViewDataTableInstance($visualizations[$type], $controllerAction, $apiAction, $params);
        }

        if (class_exists($type)) {
            return self::createViewDataTableInstance($type, $controllerAction, $apiAction, $params);
        }

        if (array_key_exists($defaultType, $visualizations)) {
            return self::createViewDataTableInstance($visualizations[$defaultType], $controllerAction, $apiAction, $params);
        }

        if (array_key_exists(HtmlTable::ID, $visualizations)) {
            return self::createViewDataTableInstance($visualizations[HtmlTable::ID], $controllerAction, $apiAction, $params);
        }

        throw new \Exception('No visualization found to render ViewDataTable');
    }

    /**
     * Returns the default viewDataTable ID to use when determining which visualization to use.
     */
    private static function getDefaultViewTypeForReport($apiAction)
    {
        list($module, $action) = explode('.', $apiAction);
        $report = Report::factory($module, $action);

        if (!empty($report) && $report->isEnabled()) {
            return $report->getDefaultTypeViewDataTable();
        }

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
             * @ignore
             */
            Piwik::postEvent('ViewDataTable.getDefaultType', array(&self::$defaultViewTypes));
        }

        return self::$defaultViewTypes;
    }

    /**
     * @param string $klass
     * @param string $controllerAction
     * @param string $apiAction
     * @param array $params
     *
     * @internal param string $viewDataTableId
     * @return \Piwik\Plugin\ViewDataTable
     */
    private static function createViewDataTableInstance($klass, $controllerAction, $apiAction, $params)
    {
        if (empty($params)) {
            $params = array();
        }

        if (!is_subclass_of($klass, 'Piwik\Plugin\Visualization')) {
            // for now we ignore those params in case it is not a visualization. We do not want to apply
            // any of those saved parameters to sparklines etc. Need to find a better solution here
            $params = array();
        }

        return new $klass($controllerAction, $apiAction, $params);
    }
}
