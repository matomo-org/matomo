<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary\Reports;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Filter\CalculateEvolutionFilter;
use Piwik\DbHelper;
use Piwik\Metrics;
use Piwik\Metrics\Formatter as MetricFormatter;
use Piwik\Period;
use Piwik\Period\Month;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreHome\Columns\Metrics\ActionsPerVisit;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\CoreHome\Columns\UserId;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Report\ReportWidgetFactory;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Widget\WidgetsList;

class Get extends \Piwik\Plugin\Report
{
    private $usersColumn = 'nb_users';

    protected function init()
    {
        parent::init();
        $this->categoryId    = 'General_Visitors';
        $this->name          = Piwik::translate('VisitsSummary_VisitsSummary');
        $this->documentation = Piwik::translate('VisitsSummary_VisitsSummaryReportDocumentation');
        $this->processedMetrics = array(
            new BounceRate(),
            new ActionsPerVisit(),
            new AverageTimeOnSite()
        );
        $this->metrics = array(
            'nb_visits',
            $this->usersColumn,
            'nb_actions',
            'max_actions'
        );

        $period = Piwik::getPeriod('day');
        if (SettingsPiwik::isUniqueVisitorsEnabled($period)) {
            $this->metrics = array_merge(['nb_uniq_visitors'], $this->metrics);
        }

        $this->subcategoryId = 'General_Overview';
        // Used to process metrics, not displayed/used directly
//								'sum_visit_length',
//								'nb_visits_converted',
        $this->order = 1;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->setName('VisitsSummary_WidgetLastVisits')
                ->forceViewDataTable(Evolution::ID)
                ->setAction('getEvolutionGraph')
                ->setOrder(5)
        );

        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->setName('VisitsSummary_WidgetVisits')
                ->forceViewDataTable(Sparklines::ID)
                ->setOrder(10)
        );
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)) {
            /** @var Sparklines $view */
            $view->requestConfig->apiMethodToRequestDataTable = 'API.get';
            $this->addSparklineColumns($view);
            $view->config->addTranslations($this->getSparklineTranslations());

            $view->config->filters[] = function (DataTable $table) use ($view) {
                $firstRow = $table->getFirstRow();

                if (!$firstRow->getColumn('avg_time_generation')) {
                    $view->config->removeSparklineMetric(array('avg_time_generation'));
                }

                if (($firstRow->getColumn('nb_pageviews')
                    + $firstRow->getColumn('nb_downloads')
                    + $firstRow->getColumn('nb_outlinks')) == 0
                    && $firstRow->getColumn('nb_actions') > 0) {
                    $view->config->removeSparklineMetric(array('nb_downloads', 'nb_uniq_downloads'));
                    $view->config->removeSparklineMetric(array('nb_outlinks', 'nb_uniq_outlinks'));
                    $view->config->removeSparklineMetric(array('nb_pageviews', 'nb_uniq_pageviews'));
                    $view->config->removeSparklineMetric(array('nb_searches', 'nb_keywords'));
                } else {
                    $view->config->removeSparklineMetric(array('nb_actions'));
                }

                $nbUsers = $firstRow->getColumn('nb_users');
                if (!is_numeric($nbUsers) || 0 >= $nbUsers) {
                    $view->config->replaceSparklineMetric(array('nb_users'), '');
                }
            };

            // Add evolution values to sparklines
            list($lastPeriodDate, $ignore) = Range::getLastDate();
            if ($lastPeriodDate !== false) {

                $currentPeriod = Period\Factory::build(Piwik::getPeriod(), Common::getRequestVar('date'));
                $currentPrettyDate = ($currentPeriod instanceof Month ? $currentPeriod->getLocalizedLongString() : $currentPeriod->getPrettyString());
                $lastPeriod = Period\Factory::build(Piwik::getPeriod(), $lastPeriodDate);
                $lastPrettyDate = ($currentPeriod instanceof Month ? $lastPeriod->getLocalizedLongString() : $lastPeriod->getPrettyString());

                /** @var DataTable $previousData */
                $previousData = Request::processRequest('API.get', ['date' => $lastPeriodDate, 'format_metrics' => '0']);
                $previousDataRow = $previousData->getFirstRow();

                $view->config->compute_evolution = function ($columns, $metrics) use ($currentPrettyDate, $lastPrettyDate, $previousDataRow) {
                    $value = reset($columns);
                    $columnName = key($columns);
                    $pastValue = $previousDataRow ? $previousDataRow->getColumn($columnName) : 0;

                    // Format
                    $formatter = new MetricFormatter();
                    $currentValueFormatted = $value;
                    $pastValueFormatted = $pastValue;
                    foreach ($metrics as $metric) {
                        if ($metric->getName() == $columnName) {
                            $pastValueFormatted = $metric->format($pastValue, $formatter);
                            $currentValueFormatted = $metric->format($value, $formatter);
                            break;
                        }
                    }

                    $columnTranslations = Metrics::getDefaultMetricTranslations();
                    $columnTranslation = '';
                    if (array_key_exists($columnName, $columnTranslations)) {
                        $columnTranslation = $columnTranslations[$columnName];
                    }

                    return [
                        'currentValue' => $value,
                        'pastValue' => $pastValue,
                        'tooltip' => Piwik::translate('General_EvolutionSummaryGeneric', [
                            $currentValueFormatted.' '.$columnTranslation,
                            $currentPrettyDate,
                            $pastValueFormatted.' '.$columnTranslation,
                            $lastPrettyDate,
                            CalculateEvolutionFilter::calculate($value, $pastValue, $precision = 1)])
                    ];
                };
            }

            // Remove metric tooltips
            $view->config->metrics_documentation['nb_actions'] = '';
            $view->config->metrics_documentation['nb_visits'] = '';
            $view->config->metrics_documentation['nb_users'] = '';
            $view->config->metrics_documentation['nb_uniq_visitors'] = '';
            $view->config->metrics_documentation['avg_time_generation'] = '';
            $view->config->metrics_documentation['avg_time_on_site'] = '';
            $view->config->metrics_documentation['max_actions'] = '';
            $view->config->metrics_documentation['nb_actions_per_visit'] = '';
            $view->config->metrics_documentation['nb_downloads'] = '';
            $view->config->metrics_documentation['nb_uniq_downloads'] = '';
            $view->config->metrics_documentation['nb_outlinks'] = '';
            $view->config->metrics_documentation['nb_uniq_outlinks'] = '';
            $view->config->metrics_documentation['nb_keywords'] = '';
            $view->config->metrics_documentation['nb_searches'] = '';
            $view->config->metrics_documentation['nb_pageviews'] = '';
            $view->config->metrics_documentation['nb_uniq_pageviews'] = '';
            $view->config->metrics_documentation['bounce_rate'] = '';
        }
    }

    private function getSparklineTranslationsKeys()
    {
        return array(
            'nb_actions' => 'NbActionsDescription',
            'nb_visits' => 'NbVisitsDescription',
            'nb_users' => 'NbUsersDescription',
            'nb_uniq_visitors' => 'NbUniqueVisitors',
            'avg_time_generation' => 'AverageGenerationTime',
            'avg_time_on_site' => 'AverageVisitDuration',
            'max_actions' => 'MaxNbActions',
            'nb_actions_per_visit' => 'NbActionsPerVisit',
            'nb_downloads' => 'NbDownloadsDescription',
            'nb_uniq_downloads' => 'NbUniqueDownloadsDescription',
            'nb_outlinks' => 'NbOutlinksDescription',
            'nb_uniq_outlinks' => 'NbUniqueOutlinksDescription',
            'nb_keywords' => 'NbKeywordsDescription',
            'nb_searches' => 'NbSearchesDescription',
            'nb_pageviews' => 'NbPageviewsDescription',
            'nb_uniq_pageviews' => 'NbUniquePageviewsDescription',
            'bounce_rate' => 'NbVisitsBounced',
        );

    }

    private function getSparklineTranslations()
    {
        $translations = $this->getSparklineTranslationsKeys();
        foreach ($translations as $metric => $key) {
            $translations[$metric] = Piwik::translate('VisitsSummary_' . $key);
        }

        return $translations;
    }

    private function addSparklineColumns(Sparklines $view)
    {
        $currentPeriod = Common::getRequestVar('period');
        $currentIdSite = Common::getRequestVar('idSite');
        $currentDate   = Common::getRequestVar('date');
        $displayUniqueVisitors = SettingsPiwik::isUniqueVisitorsEnabled($currentPeriod);

        $isActionPluginEnabled = Common::isActionsPluginEnabled();

        $view->config->addSparklineMetric($displayUniqueVisitors ? array('nb_visits', 'nb_uniq_visitors') : array('nb_visits'), 5);

        if ($isActionPluginEnabled) {
            $view->config->addSparklineMetric(array('nb_actions'), 10); // either actions or pageviews will be displayed
            $view->config->addSparklineMetric(array('nb_pageviews', 'nb_uniq_pageviews'), 20);
        } else {
            // make sure to still create a div on the right side for this, just leave it empty
            $view->config->addPlaceholder(10);
        }

        $userId = new UserId();
        if ($userId->isUsedInAtLeastOneSite(array($currentIdSite), $currentPeriod, $currentDate)) {
            $view->config->addSparklineMetric(array('nb_users'), 30);
            $view->config->addPlaceholder(31);
        }

        $view->config->addSparklineMetric(array('avg_time_on_site'), 40);

        $idSite = Common::getRequestVar('idSite');
        if ($isActionPluginEnabled && Site::isSiteSearchEnabledFor($idSite)) {
            $view->config->addSparklineMetric(array('nb_searches', 'nb_keywords'), 50);
        } else {
            // make sure to still create a div on the right side for this, just leave it empty
            $view->config->addPlaceholder(50);
        }

        $view->config->addSparklineMetric(array('bounce_rate'), 60);

        if ($isActionPluginEnabled) {
            $view->config->addSparklineMetric(array('nb_downloads', 'nb_uniq_downloads'), 70);
            $view->config->addSparklineMetric(array('nb_actions_per_visit'), 71);
            $view->config->addSparklineMetric(array('nb_outlinks', 'nb_uniq_outlinks'), 72);

            if (version_compare(DbHelper::getInstallVersion(),'4.0.0-b1', '<')) {
                $view->config->addSparklineMetric(array('avg_time_generation'), 73);
            }

            $view->config->addSparklineMetric(array('max_actions'), 74);
        }
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();

        $metrics['max_actions'] = Piwik::translate('General_ColumnMaxActions');

        return $metrics;
    }

    public function getProcessedMetrics()
    {
        $metrics = parent::getProcessedMetrics();

        $metrics['avg_time_on_site'] = Piwik::translate('General_VisitDuration');

        return $metrics;
    }

    public function removeUsersFromProcessedReport(&$response)
    {
        if (!empty($response['metadata']['metrics'][$this->usersColumn])) {
            unset($response['metadata']['metrics'][$this->usersColumn]);
        }

        if (!empty($response['metadata']['metricsDocumentation'][$this->usersColumn])) {
            unset($response['metadata']['metricsDocumentation'][$this->usersColumn]);
        }

        if (!empty($response['columns'][$this->usersColumn])) {
            unset($response['columns'][$this->usersColumn]);
        }

        if (!empty($response['reportData'])) {
            $dataTable = $response['reportData'];
            $dataTable->deleteColumn($this->usersColumn, true);
        }
    }

}
