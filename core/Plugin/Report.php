<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\API\Proxy;
use Piwik\Menu\MenuReporting;
use Piwik\Metrics;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\WidgetsList;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

class Report
{
    protected $module;
    protected $action;
    protected $name;
    protected $title;
    protected $category;
    protected $widgetTitle;
    protected $widgetParams = array();
    protected $menuTitle;
    protected $processedMetrics = array();
    protected $metrics = array();
    protected $constantRowsCount = null;
    protected $isSubtableReport = null;

    /**
     * @var \Piwik\Plugin\VisitDimension
     */
    protected $dimension;
    protected $documentation;

    /**
     * @var null|Report
     */
    protected $actionToLoadSubTables;
    protected $order = 1;

    public function __construct()
    {
        $classname    = get_class($this);
        $parts        = explode('\\', $classname);
        $this->module = $parts[2];
        $this->action = lcfirst($parts[4]);
        $this->processedMetrics = Metrics::getDefaultProcessedMetrics();
        $this->metrics          = array_keys(Metrics::getDefaultMetrics());

        $this->init();
    }

    protected function init()
    {
    }

    public function isEnabled()
    {
        return true;
    }

    public function getDefaultTypeViewDataTable()
    {
        return HtmlTable::ID;
    }

    public function configureView(ViewDataTable $view)
    {

    }

    public function render()
    {
        $apiProxy = Proxy::getInstance();

        if (!$apiProxy->isExistingApiAction($this->module, $this->action)) {
            throw new \Exception("Invalid action name '$this->action' for '$this->module' plugin.");
        }

        $apiAction = $apiProxy->buildApiActionName($this->module, $this->action);

        $view      = ViewDataTableFactory::build(null, $apiAction, 'CoreHome.renderWidget');
        $rendered  = $view->render();

        return $rendered;
    }

    public function configureWidget(WidgetsList $widget)
    {
        if ($this->widgetTitle) {
            $params = array('reportModule' => $this->module, 'reportAction' => $this->action);
            if (!empty($this->widgetParams) && is_array($this->widgetParams)) {
                foreach ($this->widgetParams as $key => $value) {
                    $params[$key] = $value;
                }
            }
            $widget->add($this->category, $this->widgetTitle, 'CoreHome', 'renderWidget', $params);
        }
    }

    public function configureReportingMenu(MenuReporting $menu)
    {
        if ($this->menuTitle) {
            $menu->add($this->category,
                       $this->menuTitle,
                       array('module' => 'CoreHome', 'action' => 'renderMenuReport', 'reportModule' => $this->module, 'reportAction' => $this->action),
                       $this->isEnabled(),
                       $this->order);
        }
    }

    protected function getMetrics()
    {
        $translations = Metrics::getDefaultMetricTranslations();
        $metrics = array();

        foreach ($this->metrics as $metric) {
            if (!empty($translations[$metric])) {
                $metrics[$metric] = $translations[$metric];
            } else {
                $metrics[] = $metric;
            }
        }

        return $metrics;
    }

    protected function getMetricsDocumentation()
    {
        $translations  = Metrics::getDefaultMetricsDocumentation();
        $documentation = array();

        foreach ($this->metrics as $metric) {
            if (!empty( $translations[$metric])) {
                $documentation[$metric] = $translations[$metric];
            }
        }

        return $documentation;
    }

    public function toArray()
    {
        $report = array(
            'category'             => $this->category,
            'name'                 => $this->name,
            'module'               => $this->module,
            'action'               => $this->action,
            'metrics'              => $this->getMetrics(),
            'metricsDocumentation' => $this->getMetricsDocumentation(),
            'processedMetrics'     => $this->processedMetrics,
            'order'                => $this->order
        );

        if (!empty($this->documentation)) {
            $report['documentation'] = $this->documentation;
        }

        if (null !== $this->constantRowsCount) {
            $report['constantRowsCount'] = $this->constantRowsCount;
        }

        if (null !== $this->isSubtableReport) {
            $report['isSubtableReport'] = $this->isSubtableReport;
        }

        if (!empty($this->dimension)) {
            $report['dimension'] = $this->dimension->getName();
        }

        if (!empty($this->actionToLoadSubTables)) {
            $report['actionToLoadSubTables'] = $this->actionToLoadSubTables;
        }

        return $report;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAction()
    {
        return $this->action;
    }

    public static function factory($module, $action = '')
    {
        foreach (self::getAllReports() as $report) {
            if ($report->module === $module && $report->action === $action) {
                return $report;
            }
        }
    }

    /** @return \Piwik\Plugin\Report[] */
    public static function getAllReports()
    {
        $reports   = PluginManager::getInstance()->findMultipleComponents('Reports', '\\Piwik\\Plugin\\Report');
        $instances = array();

        foreach ($reports as $report) {
            $instances[] = new $report();
        }

        return $instances;
    }

}
