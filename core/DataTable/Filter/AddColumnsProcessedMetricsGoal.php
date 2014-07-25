<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Exception;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Tracker\GoalManager;

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

    private $expectedColumns = array();

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
    public function __construct($table, $enable = true, $processOnlyIdGoal)
    {
        $this->processOnlyIdGoal = $processOnlyIdGoal;
        $this->isEcommerce = $this->processOnlyIdGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER || $this->processOnlyIdGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART;
        parent::__construct($table);
        // Ensure that all rows with no visit but conversions will be displayed
        $this->deleteRowsWithNoVisit = false;
    }

    private function addColumn(Row $row, $columnName, $callback)
    {
        $this->expectedColumns[$columnName] = true;
        $row->addColumn($columnName, $callback);
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

        $this->expectedColumns = array();

        $metrics = new Metrics\ProcessedGoals();

        foreach ($table->getRows() as $row) {
            $goals = $metrics->getColumn($row, Metrics::INDEX_GOALS);

            if (!$goals) {
                continue;
            }

            $this->addColumn($row, 'revenue_per_visit', function (Row $row) use ($metrics) {
                return $metrics->getRevenuePerVisit($row);
            });

            if ($this->processOnlyIdGoal == self::GOALS_MINIMAL_REPORT) {
                continue;
            }

            foreach ($goals as $goalId => $goalMetrics) {
                $goalId = str_replace("idgoal=", "", $goalId);

                if (($this->processOnlyIdGoal > self::GOALS_FULL_TABLE
                        || $this->isEcommerce)
                    && $this->processOnlyIdGoal != $goalId
                ) {
                    continue;
                }

                $columnPrefix = 'goal_' . $goalId;

                $this->addColumn($row, $columnPrefix . '_conversion_rate', function (Row $row) use ($metrics, $goalMetrics) {
                    return $metrics->getConversionRate($row, $goalMetrics);
                });

                // When the table is displayed by clicking on the flag icon, we only display the columns
                // Visits, Conversions, Per goal conversion rate, Revenue
                if ($this->processOnlyIdGoal == self::GOALS_OVERVIEW) {
                    continue;
                }

                // Goal Conversions
                $this->addColumn($row, $columnPrefix . '_nb_conversions', function () use ($metrics, $goalMetrics) {
                    return $metrics->getNbConversions($goalMetrics);
                });

                // Goal Revenue per visit
                $this->addColumn($row, $columnPrefix . '_revenue_per_visit', function (Row $row) use ($metrics, $goalMetrics) {
                    return $metrics->getRevenuePerVisitForGoal($row, $goalMetrics);
                });

                // Total revenue
                $this->addColumn($row, $columnPrefix . '_revenue', function () use ($metrics, $goalMetrics) {
                    return $metrics->getRevenue($goalMetrics);
                });

                if ($this->isEcommerce) {

                    // AOV Average Order Value
                    $this->addColumn($row, $columnPrefix . '_avg_order_revenue', function () use ($metrics, $goalMetrics) {
                        return $metrics->getAvgOrderRevenue($goalMetrics);
                    });

                    // Items qty
                    $this->addColumn($row, $columnPrefix . '_items', function () use ($metrics, $goalMetrics) {
                        return $metrics->getItems($goalMetrics);
                    });

                }
            }
        }

        $expectedColumns = array_keys($this->expectedColumns);
        $rows = $table->getRows();
        foreach ($rows as $row) {
            foreach ($expectedColumns as $name) {
                if (!$row->hasColumn($name)) {
                    if (strpos($name, 'conversion_rate') !== false) {
                        $row->addColumn($name, function () {
                            return '0%';
                        });
                    } else {
                        $row->addColumn($name, 0);
                    }
                }
            }
        }
    }
}
