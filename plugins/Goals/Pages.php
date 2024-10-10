<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals;

use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugin\ReportsProvider;
use Piwik\Widget\WidgetContainerConfig;
use Piwik\Widget\WidgetConfig;
use Piwik\Report\ReportWidgetFactory;

class Pages
{
    private $orderId = 0;
    private $allReports = array();
    private $factory = array();
    private $conversions;

    public function __construct(ReportWidgetFactory $reportFactory, $reportsWithGoalMetrics)
    {
        $this->factory = $reportFactory;
        $this->allReports = $reportsWithGoalMetrics;
        $this->conversions = new Conversions();
    }

    /**
     * @param array $goals
     * @return WidgetConfig[]
     */
    public function createGoalsOverviewPage($goals)
    {
        $subcategory = 'General_Overview';

        $widgets = array();

        $config = $this->factory->createWidget();
        $config->forceViewDataTable(Evolution::ID);
        $config->setSubcategoryId($subcategory);
        $config->setAction('getEvolutionGraph');
        $config->setOrder(5);
        $config->setIsNotWidgetizable();
        $widgets[] = $config;

        $config = $this->factory->createWidget();
        $config->forceViewDataTable(Sparklines::ID);
        $config->setSubcategoryId($subcategory);
        $config->setName('');
        $config->setOrder(15);
        $config->setModule('Goals');
        $config->setAction('getMetrics');
        $config->setIsNotWidgetizable();
        $widgets[] = $config;

        // load sparkline
        $config = $this->factory->createCustomWidget('getSparklines');
        $config->setSubcategoryId($subcategory);
        $config->setName('');
        $config->setOrder(25);
        $config->setIsNotWidgetizable();
        $widgets[] = $config;

        $container = $this->createWidgetizableWidgetContainer('GoalsOverview', $subcategory, $widgets);

        $config = $this->factory->createContainerWidget('Goals');
        $config->setSubcategoryId($subcategory);
        $config->setName('Goals_ConversionsOverviewBy');
        $config->setOrder(35);
        $config->setIsNotWidgetizable();
        $this->buildGoalByDimensionView('', $config);
        $config->setMiddlewareParameters(array(
            'module' => 'Goals',
            'action' => 'hasConversions'
        ));

        return array($container, $config);
    }

    /**
     * @return WidgetConfig[]
     */
    public function createEcommerceOverviewPage()
    {
        $category    = 'Goals_Ecommerce';
        $subcategory = 'General_Overview';
        $idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;

        $widgets = array();
        $config  = $this->factory->createWidget();
        $config->forceViewDataTable(Evolution::ID);
        $config->setCategoryId($category);
        $config->setSubcategoryId($subcategory);
        $config->setAction('getEvolutionGraph');
        $config->setOrder(5);
        $config->setIsNotWidgetizable();
        $config->setParameters(array('idGoal' => $idGoal));
        $widgets[] = $config;

        $config = $this->factory->createWidget();
        $config->setCategoryId($category);
        $config->forceViewDataTable(Sparklines::ID);
        $config->setSubcategoryId($subcategory);
        $config->setName('');
        $config->setModule('Ecommerce');
        $config->setAction('getSparklines');
        $config->setParameters(array('idGoal' => $idGoal));
        $config->setOrder(15);
        $config->setIsNotWidgetizable();
        $widgets[] = $config;

        $config = $this->factory->createWidget();
        $config->setModule('Ecommerce');
        $config->setAction('getConversionsOverview');
        $config->setSubcategoryId($idGoal);
        $config->setName('Goals_ConversionsOverview');
        $config->setParameters(array('idGoal' => $idGoal));
        $config->setOrder(25);
        $config->setIsNotWidgetizable();
        $config->setMiddlewareParameters(array(
            'module' => 'Goals',
            'action' => 'hasConversions',
            'idGoal' => $idGoal
        ));

        $widgets[] = $config;

        $container = $this->createWidgetizableWidgetContainer('EcommerceOverview', $subcategory, $widgets);
        $container->setName(Piwik::translate('Goals_EcommerceOverview'));
        return array($container);
    }

    /**
     * @return WidgetConfig[]
     */
    public function createEcommerceSalesPage()
    {
        $category    = 'Goals_Ecommerce';
        $subcategory = 'Ecommerce_Sales';

        $config = $this->factory->createContainerWidget('GoalsOrder');
        $config->setCategoryId($category);
        $config->setSubcategoryId($subcategory);
        $config->setName('');
        $config->setParameters(array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
        $config->setOrder(5);
        $config->setIsNotWidgetizable();

        $extraParameters = [ 'segmented_visitor_log_segment_suffix' => 'visitEcommerceStatus==ordered' ];
        $this->buildGoalByDimensionView(Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER, $config, $extraParameters);

        return array($config);
    }

    /**
     * @param array $goal
     * @return WidgetConfig[]
     */
    public function createGoalDetailPage($goal)
    {
        $widgets = array();

        $idGoal = (int) $goal['idgoal'];
        $name   = $goal['name'];
        $params = array('idGoal' => $idGoal);

        $config = $this->factory->createWidget();
        $config->setSubcategoryId($idGoal);
        $config->forceViewDataTable(Evolution::ID);
        $config->setAction('getEvolutionGraph');
        $config->setParameters($params);
        $config->setOrder(5);
        $config->setIsNotWidgetizable();
        $widgets[] = $config;

        $config = $this->factory->createWidget();
        $config->setSubcategoryId($idGoal);
        $config->setName('');
        $config->forceViewDataTable(Sparklines::ID);
        $config->setParameters($params);
        $config->addParameters(array('allow_multiple' => (int) $goal['allow_multiple']));
        $config->setOrder(15);
        $config->setIsNotWidgetizable();
        $widgets[] = $config;

        $config = $this->factory->createWidget();
        $config->setAction('goalConversionsOverview');
        $config->setSubcategoryId($idGoal);
        $config->setName('Goals_ConversionsOverview');
        $config->setParameters($params);
        $config->setOrder(25);
        $config->setIsNotWidgetizable();
        $config->setMiddlewareParameters(array(
            'module' => 'Goals',
            'action' => 'hasConversions',
            'idGoal' => $idGoal
        ));
        $widgets[] = $config;

        $container = $this->createWidgetizableWidgetContainer('Goal_' . $idGoal, $name, $widgets);

        $configs = array($container);

        $config = $this->factory->createContainerWidget('Goals' . $idGoal);
        $config->setName(Piwik::translate('Goals_GoalConversionsBy', array($name)));
        $config->setSubcategoryId($idGoal);
        $config->setParameters(array());
        $config->setOrder(35);
        $config->setIsNotWidgetizable();
        $config->setMiddlewareParameters(array(
            'module' => 'Goals',
            'action' => 'hasConversions',
            'idGoal' => $idGoal
        ));
        $this->buildGoalByDimensionView($idGoal, $config);

        $configs[] = $config;

        return $configs;
    }

    private function createWidgetizableWidgetContainer($containerId, $pageName, $widgets)
    {
        /** @var \Piwik\Widget\WidgetConfig[] $widgets */
        $firstWidget = reset($widgets);
        /** @var \Piwik\Report\ReportWidgetConfig $firstWidget */

        if (!empty($pageName)) {
            // make sure to not show two titles (one for this container and one for the first widget)
            $firstWidget->setName('');
        }

        $config = $this->factory->createContainerWidget($containerId);
        $config->setName($pageName);
        $config->setCategoryId($firstWidget->getCategoryId());
        $config->setSubcategoryId($firstWidget->getSubcategoryId());
        $config->setIsWidgetizable();
        $config->setOrder($this->orderId++);

        foreach ($widgets as $widget) {
            $config->addWidgetConfig($widget);
        }

        return $config;
    }

    private function buildGoalByDimensionView($idGoal, WidgetContainerConfig $container, $extraParameters = [])
    {
        $container->setLayout('ByDimension');
        $ecommerce = ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);

        // for non-Goals reports, we show the goals table
        $customParams = array('documentationForGoalsPage' => '1');

        if ($idGoal === '') {
            // if no idGoal, use 0 for overview. Must be string! Otherwise Piwik_View_HtmlTable_Goals fails.
            $customParams['idGoal'] = '0';
        } else {
            $customParams['idGoal'] = $idGoal;
        }

        $translationHelper = new TranslationHelper();

        foreach ($this->allReports as $category => $reports) {
            $order = ($this->getSortOrderOfCategory($category) * 100);

            if ($ecommerce) {
                $categoryText = $translationHelper->translateEcommerceMetricCategory($category);
            } else {
                $categoryText = $translationHelper->translateGoalMetricCategory($category);
            }

            foreach ($reports as $report) {
                $order++;

                if (
                    empty($report['viewDataTable'])
                    && empty($report['abandonedCarts'])
                ) {
                    $report['viewDataTable'] = 'tableGoals';
                }

                if (!empty($report['parameters'])) {
                    $params = array_merge($customParams, $report['parameters']);
                } else {
                    $params = $customParams;
                }

                $widget = $this->createWidgetForReport($report['module'], $report['action']);
                if (!$widget) {
                    continue;
                }
                if (!empty($report['name'])) {
                    $widget->setName($report['name']);
                }
                $widget->setParameters($params);
                $widget->addParameters($extraParameters);
                $widget->setCategoryId($categoryText);
                $widget->setSubcategoryId($categoryText);
                $widget->setOrder($order);
                if ($ecommerce) {
                    $widget->setIsWidgetizable();
                } else {
                    $widget->setIsNotWidgetizable();
                }

                if (!empty($report['viewDataTable'])) {
                    $widget->forceViewDataTable($report['viewDataTable']);
                }

                $container->addWidgetConfig($widget);
            }
        }
    }

    private function getSortOrderOfCategory($category)
    {
        static $order = null;

        if (is_null($order)) {
            $order = array(
                'Referrers_Referrers',
                'General_Actions',
                'General_Visit',
                'General_Visitors',
                'VisitsSummary_VisitsSummary',
            );
        }

        $value = array_search($category, $order);

        if (false === $value) {
            $value = count($order) + 1;
        }

        return $value;
    }

    private function createWidgetForReport($module, $action)
    {
        $report = ReportsProvider::factory($module, $action);
        if ($report) {
            $factory = new ReportWidgetFactory($report);
            return $factory->createWidget();
        }
    }
}
