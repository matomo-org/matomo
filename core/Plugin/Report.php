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
    protected $menuTitle;
    protected $processedMetrics = false;
    protected $metrics = array();

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
            $widget->add($this->category, $this->widgetTitle, 'CoreHome', 'renderWidget', array('reportModule' => $this->module, 'reportAction' => $this->action));
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
        // TODO not all will be defined there... later in Columns directory
        $translations = Metrics::getDefaultMetricTranslations();
        $metrics = array();

        foreach ($this->metrics as $metric) {
            if (!empty( $translations[$metric])) {
                $metric[$metric] = $translations[$metric];
            } else {
                $metric[$metric] = 'To be defined';
            }
        }

        return $metrics;
    }

    protected function getMetricsDocumentation()
    {
        // TODO not all will be defined there... later in Columns directory
        $translations  = Metrics::getDefaultMetricsDocumentation();
        $documentation = array();

        foreach ($this->metrics as $metric) {
            if (!empty( $translations[$metric])) {
                $metric[$metric] = $translations[$metric];
            } else {
                $metric[$metric] = 'To be defined see todo';
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
            'documentation'        => $this->documentation,
            'processedMetrics'     => $this->processedMetrics,
            'order'                => $this->order
        );

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

    public static function factory($moduleOnlyOrModuleAndAction, $action = '')
    {
        if (empty($action) && strpos($moduleOnlyOrModuleAndAction, '.') > 0) {
            $parts  = explode('.', $moduleOnlyOrModuleAndAction);
            $module = $parts[0];
            $action = $parts[1];
        } else {
            $module = $moduleOnlyOrModuleAndAction;
        }

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
