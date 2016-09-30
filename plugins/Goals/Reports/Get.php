<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\Goals\Goals;
use Piwik\Plugins\Goals\Pages;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Site;
use Piwik\Tracker\GoalManager;
use Piwik\Url;
use Piwik\Widget\WidgetsList;

class Get extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('Goals_Goals');
        $this->processedMetrics = array('conversion_rate');
        $this->documentation = ''; // TODO
        $this->order = 1;
        $this->orderGoal = 50;
        $this->metrics = array('nb_conversions', 'nb_visits_converted', 'revenue');
        $this->parameters = null;
    }

    private function getGoals()
    {
        $idSite = $this->getIdSite();
        $goals = API::getInstance()->getGoals($idSite);
        return $goals;
    }

    private function getGoal($goalId)
    {
        $goals = $this->getGoals();

        if (!empty($goals[$goalId])) {

            return $goals[$goalId];
        }
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $idSite  = Common::getRequestVar('idSite', 0, 'int');

        if (empty($idSite)) {
            return;
        }
        
        $goals   = $this->getGoals();
        $reports = Goals::getReportsWithGoalMetrics();

        $page = new Pages($factory, $reports);

        $widgetsList->addWidgetConfigs($page->createGoalsOverviewPage($goals));

        if ($this->isEcommerceEnabled($idSite)) {
            $widgetsList->addWidgetConfigs($page->createEcommerceOverviewPage());
            $widgetsList->addWidgetConfigs($page->createEcommerceSalesPage());
        }

        foreach ($goals as $goal) {
            $widgetsList->addWidgetConfigs($page->createGoalDetailPage($goal));
        }
    }

    private function getIdSite()
    {
        return Common::getRequestVar('idSite', null, 'int');
    }

    private function isEcommerceEnabled($idSite)
    {
        if (!Plugin\Manager::getInstance()->isPluginActivated('Ecommerce')) {
            return false;
        }

        $site = new Site($idSite);
        return $site->isEcommerceEnabled();
    }

    public function configureView(ViewDataTable $view)
    {
        $idGoal = Common::getRequestVar('idGoal', 0, 'string');

        $idSite = $this->getIdSite();

        if ($view->isViewDataTableId(Sparklines::ID)) {
            /** @var Sparklines $view */
            $isEcommerceEnabled = $this->isEcommerceEnabled($idSite);

            $onlySummary = Common::getRequestVar('only_summary', 0, 'int');

            if ($onlySummary && !empty($idGoal)) {
                if (is_numeric($idGoal)) {
                    $view->config->title_attributes = array('piwik-goal-page-link' => $idGoal);
                }

                // in Goals overview summary we show proper title for a goal
                $goal = $this->getGoal($idGoal);
                if (!empty($goal['name'])) {
                    $view->config->title = Piwik::translate('Goals_GoalX', "'" . $goal['name'] . "'");
                }
            } else {
                $view->config->title = '';
            }

            $numberFormatter = NumberFormatter::getInstance();
            $view->config->filters[] = function (DataTable $table) use ($numberFormatter, $idSite) {
                $firstRow = $table->getFirstRow();
                if ($firstRow) {

                    $revenue = $firstRow->getColumn('revenue');
                    $currencySymbol = Site::getCurrencySymbolFor($idSite);
                    $revenue = $numberFormatter->formatCurrency($revenue, $currencySymbol, GoalManager::REVENUE_PRECISION);
                    $firstRow->setColumn('revenue', $revenue);

                    $conversionRate = $firstRow->getColumn('conversion_rate');
                    if (false !== $conversionRate) {
                        $firstRow->setColumn('conversion_rate', $numberFormatter->formatPercent($conversionRate, $precision = 1));
                    }

                    $conversions = $firstRow->getColumn('nb_conversions');
                    if (false !== $conversions) {
                        $firstRow->setColumn('nb_conversions', $numberFormatter->formatNumber($conversions));
                    }

                    $visitsConverted = $firstRow->getColumn('nb_visits_converted');
                    if (false !== $visitsConverted) {
                        $firstRow->setColumn('nb_visits_converted', $numberFormatter->formatNumber($visitsConverted));
                    }
                }
            };

            $view->config->addTranslations(array(
                'nb_visits' => Piwik::translate('VisitsSummary_NbVisitsDescription'),
                'nb_conversions' => Piwik::translate('Goals_ConversionsDescription'),
                'nb_visits_converted' => Piwik::translate('General_NVisits'),
                'conversion_rate' => Piwik::translate('Goals_OverallConversionRate'),
                'revenue' => Piwik::translate('Goals_OverallRevenue'),
            ));

            $allowMultiple = Common::getRequestVar('allow_multiple', 0, 'int');

            if ($allowMultiple) {
                $view->config->addSparklineMetric(array('nb_conversions', 'nb_visits_converted'), $order = 10);
            } else {
                $view->config->addSparklineMetric(array('nb_conversions'), $order = 10);
            }

            $view->config->addSparklineMetric(array('conversion_rate'), $order = 20);

            if (empty($idGoal)) {
                // goals overview sparklines below evolution graph

                if ($isEcommerceEnabled) {
                    // this would be ideally done in Ecommerce plugin but then it is hard to keep same order
                    $view->config->addSparklineMetric(array('revenue'), $order = 30);
                }

            } else {
                if ($onlySummary) {
                    // in Goals Overview we list an overview for each goal....
                    $view->config->addTranslation('conversion_rate', Piwik::translate('Goals_ConversionRate'));

                } elseif ($isEcommerceEnabled) {
                    // in Goals detail page...
                    $view->config->addSparklineMetric(array('revenue'), $order = 30);
                }
            }
        } else if ($view->isViewDataTableId(Evolution::ID)) {
            if (!empty($idSite) && Piwik::isUserHasAdminAccess($idSite)) {
                $view->config->title_edit_entity_url = 'index.php' . Url::getCurrentQueryStringWithParametersModified(array(
                    'module' => 'Goals',
                    'action' => 'manage',
                    'forceView' => null,
                    'viewDataTable' => null,
                    'showtitle' => null,
                    'random' => null
                ));
            }

            $goal = $this->getGoal($idGoal);
            if (!empty($goal['name'])) {
                $view->config->title = Piwik::translate('Goals_GoalX', "'" . $goal['name'] . "'");
                if (!empty($goal['description'])) {
                    $view->config->description = $goal['description'];
                }
            } else {
                $view->config->title = Piwik::translate('General_EvolutionOverPeriod');
            }
            
            if (empty($view->config->columns_to_display)) {
                $view->config->columns_to_display = array('nb_conversions');
            }
        }
    }

    public function configureReportMetadata(&$availableReports, $infos)
    {
        if (!$this->isEnabled()) {
            return;
        }

        parent::configureReportMetadata($availableReports, $infos);

        $this->addReportMetadataForEachGoal($availableReports, $infos, function ($goal) {
            return Piwik::translate('Goals_GoalX', $goal['name']);
        });
    }
}
