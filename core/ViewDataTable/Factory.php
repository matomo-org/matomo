<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ViewDataTable;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugin\ReportsProvider;

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
    const DEFAULT_VIEW = HtmlTable::ID;

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
     *                               `'DevicesDetection.getBrowsers'`.
     * @param bool|false|string $controllerAction The controller name and action dedicated to displaying the report. This
     *                                       action is used when reloading reports or changing the report visualization.
     *                                       Defaulted to `$apiAction` if `false` is supplied.
     * @param bool $forceDefault If true, then the visualization type that was configured for the report will be
     *                           ignored and `$defaultType` will be used as the default.
     * @param bool $loadViewDataTableParametersForUser Whether the per-user parameters for this user, this ViewDataTable and this Api action
     *                                          should be loaded from the user preferences and override the default params values.
     * @throws \Exception
     * @return \Piwik\Plugin\ViewDataTable
     */
    public static function build($defaultType = null, $apiAction = false, $controllerAction = false, $forceDefault = false, $loadViewDataTableParametersForUser = null)
    {
        if (false === $controllerAction) {
            $controllerAction = $apiAction;
        }

        $report = self::getReport($apiAction);

        $defaultViewType = self::getDefaultViewTypeForReport($report, $apiAction);

        $params = array();

        $containerId = Common::getRequestVar('containerId', '', 'string');

        if (!isset($loadViewDataTableParametersForUser)) {
            $loadViewDataTableParametersForUser = ($containerId != '' || '0' == Common::getRequestVar('widget', '0', 'string'));
        }

        if ($loadViewDataTableParametersForUser) {
            $login  = Piwik::getCurrentUserLogin();
            $paramsKey = $controllerAction;
            if (!empty($report) && $controllerAction === $apiAction) {
                $paramsKey = $report->getId();
            }
            $params = Manager::getViewDataTableParameters($login, $paramsKey, $containerId);
        }

        if (!self::isDefaultViewTypeForReportFixed($report)) {
            $savedViewDataTable = false;
            if (!empty($params['viewDataTable'])) {
                $savedViewDataTable = $params['viewDataTable'];
            }

            // order of default viewDataTables' priority is: function specified default, saved default, configured default for report
            //   function specified default is preferred
            // -> force default == true : defaultType ?: saved ?: defaultView
            // -> force default == false : saved ?: defaultType ?: defaultView
            if ($forceDefault) {
                $defaultType = $defaultType ?: $savedViewDataTable ?: $defaultViewType;
            } else {
                $defaultType = $savedViewDataTable ?: $defaultType ?: $defaultViewType;
            }

            $type = Common::getRequestVar('viewDataTable', $defaultType, 'string');

            // Common::getRequestVar removes backslashes from the defaultValue in case magic quotes are enabled.
            // therefore do not pass this as a default value to getRequestVar()
            if ('' === $type) {
                $type = $defaultType ?: self::DEFAULT_VIEW;
            }
        } else {
            $type = $defaultType ?: $defaultViewType;
        }

        $params['viewDataTable'] = $type;

        $visualizations = Manager::getAvailableViewDataTables();

        if (array_key_exists($type, $visualizations)) {
            return self::createViewDataTableInstance($visualizations[$type], $controllerAction, $apiAction, $params);
        }

        if (array_key_exists($defaultType, $visualizations)) {
            return self::createViewDataTableInstance($visualizations[$defaultType], $controllerAction, $apiAction, $params);
        }

        if (array_key_exists(self::DEFAULT_VIEW, $visualizations)) {
            return self::createViewDataTableInstance($visualizations[self::DEFAULT_VIEW], $controllerAction, $apiAction, $params);
        }

        throw new \Exception('No visualization found to render ViewDataTable');
    }

    /**
     * Return the report object for the given apiAction
     * @param $apiAction
     * @return null|Report
     */
    private static function getReport($apiAction)
    {
        if (strpos($apiAction, '.') === false) {
            return;
        }

        list($module, $action) = explode('.', $apiAction);
        $report = ReportsProvider::factory($module, $action);
        return $report;
    }

    /**
     * Returns the default viewDataTable ID to use when determining which visualization to use.
     *
     * @param Report $report
     * @param string $apiAction
     *
     * @return bool|string
     */
    private static function getDefaultViewTypeForReport($report, $apiAction)
    {
        if (!empty($report) && $report->isEnabled()) {
            return $report->getDefaultTypeViewDataTable();
        }

        return false;
    }

    /**
     * Returns if the default viewDataTable ID to use is fixed.
     *
     * @param Report $report
     * @return bool
     */
    private static function isDefaultViewTypeForReportFixed($report)
    {
        if (!empty($report) && $report->isEnabled()) {
            return $report->alwaysUseDefaultViewDataTable();
        }

        return false;
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

        if(!is_subclass_of($klass, 'Piwik\View\ViewInterface')) {
            throw new \Exception("viewDataTable $klass must implement Piwik\View\ViewInterface interface.");
        }

        return new $klass($controllerAction, $apiAction, $params);
    }
}
