<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals\Reports;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;
use Piwik\Metrics;
use Piwik\Metrics\Formatter as MetricFormatter;
use Piwik\NumberFormatter;
use Piwik\Period\Month;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugin;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreHome\Columns\Metrics\ConversionRate;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
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
        $this->processedMetrics = ['conversion_rate'];
        $this->documentation = Piwik::translate('Goals_OverviewReportDocumentation');
        $this->order = 1;
        $this->orderGoal = 50;
        $this->metrics = ['nb_conversions', 'nb_visits_converted', 'revenue'];
        $this->parameters = null;
    }

    private function getGoals()
    {
        $idSite = $this->getIdSite();
        $goals = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);
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
                    $view->config->title_attributes = ['goal-page-link' => $idGoal];
                }

                // in Goals overview summary we show proper title for a goal
                $goal = $this->getGoal($idGoal);
                if (!empty($goal['name'])) {
                    $view->config->title = Piwik::translate('Goals_GoalX', "'" . $goal['name'] . "'");
                }
            } else {
                $view->config->title = '';
            }

            $view->config->addTranslations([
                'nb_visits' => Piwik::translate('VisitsSummary_NbVisitsDescription'),
                'nb_conversions' => Piwik::translate('Goals_ConversionsDescription'),
                'nb_visits_converted' => Piwik::translate('General_NVisits'),
                'conversion_rate' => Piwik::translate('Goals_OverallConversionRate'),
                'revenue' => Piwik::translate('Goals_OverallRevenue'),
            ]);

            // Adding conversion rate as extra processed metrics ensures it will be formatted
            // This is not done when comparing, as comparison does its own formatting
            if (!$view->isComparing()) {
                $view->config->filters[] = function (DataTable $t) {
                    $extraProcessedMetrics = $t->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);

                    if (empty($extraProcessedMetrics)) {
                        $extraProcessedMetrics = [];
                    }
                    $extraProcessedMetrics[] = new ConversionRate();
                    $t->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
                };
            }

            $allowMultiple = Common::getRequestVar('allow_multiple', 0, 'int');

            if ($allowMultiple) {
                $view->config->addSparklineMetric(['nb_conversions', 'nb_visits_converted'], $order = 10);
            } else {
                $view->config->addSparklineMetric(['nb_conversions'], $order = 10);
            }

            $view->config->addSparklineMetric(['conversion_rate'], $order = 20);

            if (empty($idGoal)) {
                // goals overview sparklines below evolution graph

                if ($isEcommerceEnabled) {
                    // this would be ideally done in Ecommerce plugin but then it is hard to keep same order
                    $view->config->addSparklineMetric(['revenue'], $order = 30);
                }
            } else {
                if ($onlySummary) {
                    // in Goals Overview we list an overview for each goal....
                    $view->config->addTranslation('conversion_rate', Piwik::translate('Goals_ConversionRate'));
                } elseif ($isEcommerceEnabled) {
                    // in Goals detail page...
                    $view->config->addSparklineMetric(['revenue'], $order = 30);
                }
            }

            // Add evolution values to sparklines
            [$lastPeriodDate, $ignore] = Range::getLastDate();
            if ($lastPeriodDate !== false) {

                // Using a filter here ensures the additional request is only performed when the view is rendered
                $view->config->filters[] = function($datatable) use ($view, $lastPeriodDate, $idSite) {
                    /** @var DataTable $previousData */
                    $previousData    = Request::processRequest(
                        'Goals.get',
                        ['date' => $lastPeriodDate, 'format_metrics' => '0']
                    );
                    $previousDataRow = $previousData->getFirstRow();

                    $currentPeriod     = PeriodFactory::build(Piwik::getPeriod(), Common::getRequestVar('date'));
                    $currentPrettyDate = ($currentPeriod instanceof Month ? $currentPeriod->getLocalizedLongString(
                    ) : $currentPeriod->getPrettyString());
                    $lastPeriod        = PeriodFactory::build(Piwik::getPeriod(), $lastPeriodDate);
                    $lastPrettyDate    = ($currentPeriod instanceof Month ? $lastPeriod->getLocalizedLongString(
                    ) : $lastPeriod->getPrettyString());

                    $view->config->compute_evolution = function ($columns, $metrics) use (
                        $currentPrettyDate,
                        $lastPrettyDate,
                        $previousDataRow,
                        $idSite
                    ) {
                        $value      = reset($columns);
                        $columnName = key($columns);
                        $pastValue  = $previousDataRow ? $previousDataRow->getColumn($columnName) : 0;

                        if (!is_numeric($value)) {
                            return;
                        }

                        // Format
                        $formatter             = new MetricFormatter();
                        $currentValueFormatted = $value;
                        $pastValueFormatted    = $pastValue;
                        foreach ($metrics as $metric) {
                            if ($metric->getName() === $columnName) {
                                $pastValueFormatted    = $metric->format($pastValue, $formatter);
                                $currentValueFormatted = $metric->format($value, $formatter);
                                break;
                            }
                        }

                        if (strpos($columnName, 'revenue') !== false) {
                            $currencySymbol        = Site::getCurrencySymbolFor($idSite);
                            $pastValueFormatted    = NumberFormatter::getInstance()->formatCurrency(
                                $pastValue,
                                $currencySymbol,
                                GoalManager::REVENUE_PRECISION
                            );
                            $currentValueFormatted = NumberFormatter::getInstance()->formatCurrency(
                                $value,
                                $currencySymbol,
                                GoalManager::REVENUE_PRECISION
                            );
                        }

                        $columnTranslations = Metrics::getDefaultMetricTranslations();
                        $columnTranslation  = '';
                        if (array_key_exists($columnName, $columnTranslations)) {
                            $columnTranslation = $columnTranslations[$columnName];
                        }

                        return [
                            'currentValue' => $value,
                            'pastValue'    => $pastValue,
                            'tooltip'      => Piwik::translate('General_EvolutionSummaryGeneric', [
                                $currentValueFormatted . ' ' . $columnTranslation,
                                $currentPrettyDate,
                                $pastValueFormatted . ' ' . $columnTranslation,
                                $lastPrettyDate,
                                CalculateEvolutionFilter::calculate($value, $pastValue, $precision = 1)
                            ]),
                        ];
                    };
                };
            }
        } elseif ($view->isViewDataTableId(Evolution::ID)) {
            if (!empty($idSite) && Piwik::isUserHasWriteAccess($idSite)) {
                $view->config->title_edit_entity_url = 'index.php' . Url::getCurrentQueryStringWithParametersModified([
                    'module' => 'Goals',
                    'action' => 'manage',
                    'forceView' => null,
                    'viewDataTable' => null,
                    'showtitle' => null,
                    'random' => null,
                    'format_metrics' => 0
                ]);
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
                $view->config->columns_to_display = ['nb_conversions'];
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
        }, $isSummary = true);
    }
}
