<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Report;

use Piwik\Plugin\Report;
use Piwik\Widget\WidgetContainerConfig;

/**
 * Report widget factory. This factory allows you to create widgets for a given report without having to re-specify
 * redundant information like module, action, category, subcategory, order, ... When creating a widget from a report
 * these values will be automatically specified so that ideally `$factory->createWidget()` is all one has to do in
 * order to create a new widget.
 *
 * @api since Piwik 3.0.0
 */
class ReportWidgetFactory
{
    /**
     * @var Report
     */
    private $report = null;

    /**
     * Generates a new report widget factory.
     * @param Report $report  A report instance, widgets will be created based on the data provided by this report.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Creates a widget based on the specified report in {@link construct()}.
     *
     * It will automatically use the report's name, categoryId, subcategoryId (if specified),
     * defaultViewDataTable, module, action, order and parameters in order to create the widget.
     *
     * @return ReportWidgetConfig
     */
    public function createWidget()
    {
        $widget = new ReportWidgetConfig();
        $widget->setName($this->report->getName());
        $widget->setCategoryId($this->report->getCategoryId());

        if ($this->report->getDefaultTypeViewDataTable()) {
            $widget->setDefaultViewDataTable($this->report->getDefaultTypeViewDataTable());
        }

        if ($this->report->getSubcategoryId()) {
            $widget->setSubcategoryId($this->report->getSubcategoryId());
        }

        $widget->setModule($this->report->getModule());
        $widget->setAction($this->report->getAction());

        $orderThatListsReportsAtTheEndOfEachCategory = 100 + $this->report->getOrder();
        $widget->setOrder($orderThatListsReportsAtTheEndOfEachCategory);

        $parameters = $this->report->getParameters();
        if (!empty($parameters)) {
            $widget->setParameters($parameters);
        }

        return $widget;
    }

    /**
     * Creates a new container widget based on the specified report in {@link construct()}.
     *
     * It will automatically use the report's categoryId, subcategoryId (if specified) and order in order to
     * create the container.
     *
     * @param string $containerId eg 'Products' or 'Contents' see {Piwik\Widget\WidgetContainerConfig::setId()}.
     *                            Other reports or widgets will be able to add more widgets to this container.
     *                            This is useful when you want to show for example multiple related widgets
     *                            together.
     * @return WidgetContainerConfig
     */
    public function createContainerWidget($containerId)
    {
        $widget = new WidgetContainerConfig();
        $widget->setCategoryId($this->report->getCategoryId());
        $widget->setId($containerId);

        if ($this->report->getSubcategoryId()) {
            $widget->setSubcategoryId($this->report->getSubcategoryId());
        }

        $orderThatListsReportsAtTheEndOfEachCategory = 100 + $this->report->getOrder();
        $widget->setOrder($orderThatListsReportsAtTheEndOfEachCategory);

        return $widget;
    }

    /**
     * Creates a custom widget that doesn't use a viewDataTable to render the report but instead a custom
     * controller action. Make sure the specified `$action` exists in the plugin's controller. Otherwise
     * behaves as {@link createWidget()}.
     *
     * @param string $action  eg 'conversionReports' (requires a method `public function conversionReports()` in
     *                        the plugin's controller).
     * @return ReportWidgetConfig
     */
    public function createCustomWidget($action)
    {
        $widget = $this->createWidget();
        $widget->setDefaultViewDataTable(null);
        $widget->setAction($action);

        return $widget;
    }
}