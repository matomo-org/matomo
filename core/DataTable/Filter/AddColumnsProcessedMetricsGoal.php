<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin\Metric;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\AverageOrderRevenue;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionRate;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionPageRate;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionEntryRate;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionsAttrib;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ConversionsEntry;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\Conversions;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\ItemsCount;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\Revenue;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\RevenuePerVisit as GoalSpecificRevenuePerVisit;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\RevenuePerEntry as GoalSpecificRevenuePerEntry;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\RevenueAttrib;
use Piwik\Plugins\Goals\Columns\Metrics\GoalSpecific\RevenueEntry;
use Piwik\Plugins\Goals\Columns\Metrics\RevenuePerVisit;

/**
 * Adds goal related metrics to a {@link DataTable} using metrics that already exist.
 *
 * Metrics added are:
 *
 * - **revenue_per_visit**: total goal and ecommerce revenue / nb_visits
 * - **goal_%idGoal%_conversion_rate**: the conversion rate. There will be one of
 *                                      these columns for each goal that exists
 *                                      for the site.
 * - **goal_%idGoal%_nb_conversions**: the number of conversions. There will be one of
 *                                     these columns for each goal that exists
 *                                     for the site.
 * - **goal_%idGoal%_revenue_per_visit**: goal revenue / nb_visits. There will be one of
 *                                        these columns for each goal that exists
 *                                        for the site.
 * - **goal_%idGoal%_revenue**: goal revenue. There will be one of
 *                              these columns for each goal that exists
 *                              for the site.
 * - **goal_%idGoal%_avg_order_revenue**: goal revenue / number of orders or abandoned
 *                                        carts. Only for ecommerce order and abandoned cart
 *                                        reports.
 * - **goal_%idGoal%_items**: number of items. Only for ecommerce order and abandoned cart
 *                            reports.
 *
 * Adding the **filter_update_columns_when_show_all_goals** query parameter to
 * an API request will trigger the execution of this Filter.
 *
 * _Note: This filter must be called before {@link ReplaceColumnNames} is called._
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('AddColumnsProcessedMetricsGoal',
 *         array($enable = true, $idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
 *
 * @api
 */
class AddColumnsProcessedMetricsGoal extends AddColumnsProcessedMetrics
{

    /**
     * Process metrics for entry page views, with ECommerce
     */
    const GOALS_ENTRY_PAGES_ECOMMERCE = -6;

    /**
     * Process for page views, with ECommerce
     */
    const GOALS_PAGES_ECOMMERCE = -5;

    /**
     * Process metrics for entry page views
     */
    const GOALS_ENTRY_PAGES = -4;

    /**
     * Process for page views
     */
    const GOALS_PAGES = -3;

    /**
     * Process main goal metrics: conversion rate, revenue per visit
     */
    const GOALS_MINIMAL_REPORT = -2;

    /**
     * Process main goal metrics, and conversion rate per goal
     */
    const GOALS_OVERVIEW = -1;

    /**
     * Process all goal and per-goal metrics
     */
    const GOALS_FULL_TABLE = 0;

    /**
     * @var string
     */
    private $processOnlyIdGoal;

    /**
     * @var bool
     */
    private $isEcommerce;

    /**
     * @var mixed|null
     */
    private $goalsToProcess;

    /**
     * Constructor.
     *
     * @param DataTable $table The table that will eventually filtered.
     * @param bool $enable Always set to true.
     * @param string $processOnlyIdGoal Defines what metrics to add (don't process metrics when you don't display them).
     *                                  If self::GOALS_FULL_TABLE, all Goal metrics (and per goal metrics) will be processed.
     *                                  If self::GOALS_OVERVIEW, only the main goal metrics will be added.
     *                                  If an int > 0, then will process only metrics for this specific Goal.
     */
    public function __construct($table, $enable, $processOnlyIdGoal, $goalsToProcess = null)
    {
        $this->processOnlyIdGoal = $processOnlyIdGoal;
        $this->isEcommerce = $this->processOnlyIdGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER || $this->processOnlyIdGoal === Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART;
        parent::__construct($table);
        // Ensure that all rows with no visit but conversions will be displayed
        $this->deleteRowsWithNoVisit = false;
        $this->goalsToProcess = $goalsToProcess;
    }

    /**
     * Adds the processed metrics. See {@link AddColumnsProcessedMetrics} for
     * more information.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        // Add standard processed metrics
        parent::filter($table);

        $goals = $this->getGoalsInTable($table);
        if (!empty($this->goalsToProcess)) {
            $goals = array_unique(array_merge($goals, $this->goalsToProcess));
            sort($goals);
        }

        $idSite = DataTableFactory::getSiteIdFromMetadata($table);

        $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);

        $extraProcessedMetrics[] = new RevenuePerVisit();
        if ($this->processOnlyIdGoal != self::GOALS_MINIMAL_REPORT) {
            foreach ($goals as $idGoal) {
                if (($this->processOnlyIdGoal > self::GOALS_FULL_TABLE
                        || $this->isEcommerce)
                    && $this->processOnlyIdGoal != $idGoal
                ) {
                    continue;
                }

                $extraProcessedMetrics[] = new ConversionRate($idSite, $idGoal); // PerGoal\ConversionRate

                // When the table is displayed by clicking on the flag icon, we only display the columns
                // Visits, Conversions, Per goal conversion rate, Revenue
                if ($this->processOnlyIdGoal == self::GOALS_OVERVIEW) {
                   continue;
                }

                $extraProcessedMetrics[] = new Conversions($idSite, $idGoal); // PerGoal\Conversions or GoalSpecific\
                $extraProcessedMetrics[] = new GoalSpecificRevenuePerVisit($idSite, $idGoal); // PerGoal\Revenue
                $extraProcessedMetrics[] = new Revenue($idSite, $idGoal); // PerGoal\Revenue

                if ($this->processOnlyIdGoal == self::GOALS_PAGES || $this->processOnlyIdGoal == self::GOALS_PAGES_ECOMMERCE) {
                    $extraProcessedMetrics[] = new ConversionsAttrib($idSite, $idGoal); // PerGoal\Conversions or GoalSpecific\
                    $extraProcessedMetrics[] = new RevenueAttrib($idSite, $idGoal); // PerGoal\Revenue attrib
                    $extraProcessedMetrics[] = new ConversionPageRate($idSite, $idGoal); // PerGoal\ConversionRate for page uniq views
                }

                if ($this->processOnlyIdGoal == self::GOALS_ENTRY_PAGES || $this->processOnlyIdGoal == self::GOALS_ENTRY_PAGES_ECOMMERCE) {
                    $extraProcessedMetrics[] = new ConversionsEntry($idSite, $idGoal); // PerGoal\Conversions or GoalSpecific\
                    $extraProcessedMetrics[] = new GoalSpecificRevenuePerEntry($idSite, $idGoal); // PerGoal\Revenue entries
                    $extraProcessedMetrics[] = new RevenueEntry($idSite, $idGoal); // PerGoal\Revenue entrances
                    $extraProcessedMetrics[] = new ConversionEntryRate($idSite, $idGoal); // PerGoal\ConversionRate for entrances
                }

                if ($this->isEcommerce || $this->processOnlyIdGoal == self::GOALS_PAGES_ECOMMERCE || $this->processOnlyIdGoal == self::GOALS_ENTRY_PAGES_ECOMMERCE) {
                    $extraProcessedMetrics[] = new AverageOrderRevenue($idSite, $idGoal);
                    $extraProcessedMetrics[] = new ItemsCount($idSite, $idGoal);
                }
            }
        }

        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
    }

    private function getGoalsInTable(DataTable $table)
    {
        $result = array();
        foreach ($table->getRows() as $row) {
            $goals = Metric::getMetric($row, 'goals');
            if (!$goals) {
                continue;
            }

            foreach ($goals as $goalId => $goalMetrics) {
                $goalId = str_replace("idgoal=", "", $goalId);
                $result[] = $goalId;
            }
        }
        return array_unique($result);
    }
}
