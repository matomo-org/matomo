<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;

/**
 * The ranking query class wraps an arbitrary SQL query with more SQL that limits
 * the number of results while aggregating the rest in an a new "Others" row. It also
 * allows for some more fancy things that can be configured via method calls of this
 * class. The advanced use cases are explained in the doc comments of the methods.
 *
 * The general use case looks like this:
 *
 *     // limit to 500 rows + "Others"
 *     $rankingQuery = new RankingQuery();
 *     $rankingQuery->setLimit(500);
 *
 *     // idaction_url will be "Others" in the row that contains the aggregated rest
 *     $rankingQuery->addLabelColumn('idaction_url');
 *
 *     // the actual query. it's important to sort it before the limit is applied
 *     $sql = 'SELECT idaction_url, COUNT(*) AS nb_hits
 *             FROM log_link_visit_action
 *             GROUP BY idaction_url
 *             ORDER BY nb_hits DESC';
 *
 *     // execute the query
 *     $rankingQuery->execute($sql);
 *
 * For more examples, see RankingQueryTest.php
 *
 * @api
 */
class RankingQuery
{
    // a special label used to mark the 'Others' row in a ranking query result set. this is mapped to the
    // datatable summary row during archiving.
    public const LABEL_SUMMARY_ROW = '__mtm_ranking_query_others__';

    /**
     * Contains the labels of the inner query.
     * Format: "label" => true (to make sure labels don't appear twice)
     */
    private array $labelColumns = array();

    /**
     * The columns of the inner query that are not labels
     * Format: "label" => "aggregation function" or false for no aggregation
     */
    private array $additionalColumns = array();

    /**
     * The limit for each group
     * @var int
     */
    private $limit = 5;

    /**
     * The name of the columns that marks rows to be excluded from the limit
     * @var string|false
     */
    private $columnToMarkExcludedRows = false;

    /**
     * The column that is used to partition the result
     * @var string|false
     */
    private $partitionColumn = false;

    /**
     * The possible values for the column $this->partitionColumn
     * @var array
     */
    private $partitionColumnValues = array();

    /**
     * The value to use in the label of the 'Others' row.
     * @var string
     */
    private $othersLabelValue = self::LABEL_SUMMARY_ROW;

    /**
     * @param int|false $limit The result row limit. See {@link setLimit()}.
     */
    public function __construct($limit = false)
    {
        if ($limit !== false) {
            $this->setLimit($limit);
        }
    }

    /**
     * Set the limit after which everything is grouped to "Others".
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Set the value to use for the label in the 'Others' row.
     *
     * @param string $value
     */
    public function setOthersLabel($value)
    {
        $this->othersLabelValue = $value;
    }

    /**
     * Add a label column.
     * Labels are the columns that are replaced with "Others" after the limit.
     *
     * @param string|array $labelColumn
     */
    public function addLabelColumn($labelColumn)
    {
        if (is_array($labelColumn)) {
            foreach ($labelColumn as $label) {
                $this->addLabelColumn($label);
            }
            return;
        }
        $this->labelColumns[$labelColumn] = true;
    }

    /**
     * @return array
     */
    public function getLabelColumns()
    {
        return $this->labelColumns;
    }

    /**
     * Add a column that has be added to the outer queries.
     *
     * @param $column
     * @param string|bool $aggregationFunction If set, this function is used to aggregate the values of "Others",
     *                                         eg, `'min'`, `'max'` or `'sum'`.
     */
    public function addColumn($column, $aggregationFunction = false)
    {
        if (is_array($column)) {
            foreach ($column as $c) {
                $this->addColumn($c, $aggregationFunction);
            }
            return;
        }
        $this->additionalColumns[$column] = $aggregationFunction;
    }

    /**
     * Sets a column that will be used to filter the result into two categories.
     * Rows where this column has a value > 0 will be removed from the result and put
     * into another array. Both the result and the array of excluded rows are returned
     * by {@link execute()}.
     *
     * @param string $column Name of the column.
     * @throws Exception if method is used more than once.
     */
    public function setColumnToMarkExcludedRows($column)
    {
        if ($this->columnToMarkExcludedRows !== false) {
            throw new Exception("setColumnToMarkExcludedRows can only be used once");
        }

        $this->columnToMarkExcludedRows = $column;
        $this->addColumn($this->columnToMarkExcludedRows);
    }

    /**
     * This method can be used to partition the result based on the possible values of one
     * table column. This means the query will split the result set into other sets of rows
     * for each possible value you provide (where the rows of each set have a column value
     * that equals a possible value). Each of these new sets of rows will be individually
     * limited resulting in several limited result sets.
     *
     * For example, you can run a query aggregating some data on the log_action table and
     * partition by log_action.type with the possible values of {@link Piwik\Tracker\Action::TYPE_PAGE_URL},
     * {@link Piwik\Tracker\Action::TYPE_OUTLINK}, {@link Piwik\Tracker\Action::TYPE_DOWNLOAD}.
     * The result will be three separate result sets that are aggregated the same ways, but for rows
     * where `log_action.type = TYPE_OUTLINK`, for rows where `log_action.type = TYPE_ACTION_URL` and for
     * rows `log_action.type = TYPE_DOWNLOAD`.
     *
     * @param string $partitionColumn The column name to partition by.
     * @param array $possibleValues Array of possible column values.
     * @throws Exception if method is used more than once.
     */
    public function partitionResultIntoMultipleGroups($partitionColumn, $possibleValues)
    {
        if ($this->partitionColumn !== false) {
            throw new Exception("partitionResultIntoMultipleGroups can only be used once");
        }

        $this->partitionColumn = $partitionColumn;
        $this->partitionColumnValues = $possibleValues;
        $this->addColumn($partitionColumn);
    }

    /**
     * Executes the query.
     * The object has to be configured first using the other methods.
     *
     * @param string $innerQuery The "payload" query that does the actual data aggregation. The ordering
     *                            has to be specified in this query. {@link RankingQuery} cannot apply ordering
     *                            itself.
     * @param array $bind Bindings for the inner query.
     * @param int $timeLimit      Adds a MAX_EXECUTION_TIME query hint to the query if $timeLimit > 0
     *                            for more details see {@link DbHelper::addMaxExecutionTimeHintToQuery}
     * @return array              The format depends on which methods have been used
     *                            to configure the ranking query.
     */
    public function execute($innerQuery, $bind = array(), $timeLimit = 0)
    {
        $query = $this->generateRankingQuery($innerQuery);
        $query = DbHelper::addMaxExecutionTimeHintToQuery($query, $timeLimit);

        $data  = Db::getReader()->fetchAll($query, $bind);

        if ($this->columnToMarkExcludedRows !== false) {
            // split the result into the regular result and the rows with special treatment
            $excludedFromLimit = array();
            $result = array();
            foreach ($data as &$row) {
                if ($row[$this->columnToMarkExcludedRows] != 0) {
                    $excludedFromLimit[] = $row;
                } else {
                    $result[] = $row;
                }
            }
            $data = array(
                'result'            => &$result,
                'excludedFromLimit' => &$excludedFromLimit,
            );
        }

        if ($this->partitionColumn !== false) {
            if ($this->columnToMarkExcludedRows !== false) {
                $data['result'] = $this->splitPartitions($data['result']);
            } else {
                $data = $this->splitPartitions($data);
            }
        }

        return $data;
    }

    private function splitPartitions(&$data)
    {
        $result = array();
        foreach ($data as &$row) {
            $partition = $row[$this->partitionColumn];
            if (!isset($result[$partition])) {
                $result[$partition] = array();
            }
            $result[$partition][] = & $row;
        }
        return $result;
    }

    /**
     * Generate the SQL code that does the magic.
     * If you want to get the result, use execute() instead. If you want to run the query
     * yourself, use this method.
     *
     * @param string $innerQuery The "payload" query that does the actual data aggregation. The ordering
     *                            has to be specified in this query. {@link RankingQuery} cannot apply ordering
     *                            itself.
     * @param bool $withRollup A flag which determines whether to generate the SQL query using ROLLUP
     * @return string             The entire ranking query SQL.
     */
    public function generateRankingQuery($innerQuery, bool $withRollup = false)
    {
        // +1 to include "Others"
        $limit = $this->limit + 1;

        $labelColumnsOthersSwitches = $this->generateLabelColumnsOthersSwitches($limit, $withRollup);
        $additionalColumnsExpressions = $this->generateAdditionalColumnsExpressions();
        $windowOrderBy = $this->generateWindowOrderByExpression($innerQuery, $withRollup);

        $withCounterSelectString = implode(
            ',
            ',
            array_filter([
                $this->generateLabelColumnsString(),
                $this->generateCounterExpression($limit, $windowOrderBy, $withRollup),
                $this->generateCounterRollupExpression($limit, $windowOrderBy, $withRollup),
                $additionalColumnsExpressions['additionalColumns'],
            ])
        );

        $labelColumnsOthersSelects = [];

        foreach ($labelColumnsOthersSwitches as $column => $switch) {
            $labelColumnsOthersSelects[] = "$switch AS `$column`";
        }

        $rankingSelectString = implode(
            ',
            ',
            array_filter([
                implode(', ', $labelColumnsOthersSelects),
                $additionalColumnsExpressions['additionalColumnsAggregated'],
            ])
        );

        $groupByExpression = $this->generateGroupByExpression($withRollup, $labelColumnsOthersSwitches);

        $rankingQuery = "
            SELECT
                $rankingSelectString
            FROM (
                SELECT
                    $withCounterSelectString
                FROM ( $innerQuery ) actualQuery
            ) AS withCounter
            GROUP BY $groupByExpression
        ";

        if ($withRollup) {
            // Sort the final result if a rollup was used
            // to ensure rollup values are returned first, and "Others" last
            $rankingQuery .= " ORDER BY counter, counterRollup";
        }

        return $rankingQuery;
    }

    /**
     * @return array{additionalColumns: string, additionalColumnsAggregated: string}
     */
    private function generateAdditionalColumnsExpressions(): array
    {
        $columnsString = '';
        $columnsAggregatedString = '';

        if ([] !== $this->additionalColumns) {
            $columnsToAggregate = [];

            foreach ($this->additionalColumns as $additionalColumn => $aggregation) {
                if ($aggregation !== false) {
                    $columnsToAggregate[] = $aggregation . '(`' . $additionalColumn . '`) AS `' . $additionalColumn . '`';
                } else {
                    $columnsToAggregate[] = '`' . $additionalColumn . '`';
                }
            }

            $columnsString = '`' . implode('`, `', array_keys($this->additionalColumns)) . '`';
            $columnsAggregatedString = implode(', ', $columnsToAggregate);
        }

        return [
            'additionalColumns' => $columnsString,
            'additionalColumnsAggregated' => $columnsAggregatedString,
        ];
    }

    /**
     * @return array<string, string> The switch expression (without alias) indexed by label column
     */
    private function generateLabelColumnsOthersSwitches(int $limit, bool $withRollup): array
    {
        $isFirstLabelColumn = true;
        $switches = [];

        foreach (array_keys($this->labelColumns) as $column) {
            if ($withRollup && $isFirstLabelColumn) {
                // support "Others" row for first label column
                $whens = "
                    WHEN counterRollup = $limit THEN '" . $this->othersLabelValue . "'
                    WHEN counterRollup > 0 THEN `$column`
                    WHEN counter = $limit AND counterRollup = 0 THEN `$column`
                    WHEN counter = $limit THEN '" . $this->othersLabelValue . "'
                ";
            } elseif ($withRollup) {
                // support "Others" row for secondary label columns
                $whens = "
                    WHEN `$column` IS NULL THEN NULL
                    WHEN counter = $limit AND counterRollup = 0 THEN '" . $this->othersLabelValue . "'
                ";
            } else {
                $whens = "
                    WHEN counter = $limit THEN '" . $this->othersLabelValue . "'
                ";
            }

            $switches[$column] = "
                CASE
                    $whens
                    ELSE `$column`
                END
            ";

            $isFirstLabelColumn = false;
        }

        return $switches;
    }

    private function generateLabelColumnsString(): string
    {
        return '`' . implode('`, `', array_keys($this->labelColumns)) . '`';
    }

    private function generateCounterExpression(int $limit, string $windowOrderBy, bool $withRollup): string
    {
        $partitionBy = '';

        if ($this->partitionColumn !== false) {
            $partitionBy = "PARTITION BY `{$this->partitionColumn}`";
        }

        $excludeWhens = [];
        $orderByWhens = [];

        if ($this->columnToMarkExcludedRows !== false) {
            // rows excluded from the limit get a separate, negative number space
            // and are ordered last so they don't take up any of the counted positions
            $excludeWhens[] = "WHEN {$this->columnToMarkExcludedRows} != 0 THEN -1 * {$this->columnToMarkExcludedRows}";
            $orderByWhens[] = "WHEN {$this->columnToMarkExcludedRows} != 0 THEN 1 * {$this->columnToMarkExcludedRows}";
        }

        if ($withRollup) {
            foreach (array_keys($this->labelColumns) as $column) {
                $excludeWhens[] = "WHEN `$column` IS NULL THEN -1";
                $orderByWhens[] = "WHEN `$column` IS NULL THEN 1";
            }
        }

        if ($this->partitionColumn !== false) {
            // rows outside the possible partition values are not ranked
            $possibleValues = implode(', ', array_map('intval', $this->partitionColumnValues));
            $excludeWhens[] = "WHEN `{$this->partitionColumn}` NOT IN ($possibleValues) THEN 0";
        }

        if ([] === $orderByWhens) {
            $orderBy = $windowOrderBy;
        } else {
            $orderBy = "
                CASE
                    " . implode("
                    ", $orderByWhens) . "
                    ELSE 0
                END,
                $windowOrderBy
            ";
        }

        // the counter stops at $limit to group all rows after the limit into "Others"
        $rowNumber = "LEAST(ROW_NUMBER() OVER ($partitionBy ORDER BY $orderBy), $limit)";

        if ([] === $excludeWhens) {
            return "$rowNumber AS counter";
        }

        return "
            CASE
                " . implode("
                ", $excludeWhens) . "
                ELSE $rowNumber
            END AS counter
        ";
    }

    private function generateCounterRollupExpression(int $limit, string $windowOrderBy, bool $withRollup): string
    {
        if (!$withRollup) {
            return '';
        }

        $rollupColumns = array_keys($this->labelColumns);

        return "
            CASE
                WHEN `" . implode('` IS NULL AND `', $rollupColumns) . "` IS NULL THEN -1
                WHEN `" . implode('` IS NOT NULL AND `', $rollupColumns) . "` IS NOT NULL THEN 0
                ELSE LEAST(
                    ROW_NUMBER() OVER (
                        ORDER BY
                            CASE
                                WHEN `" . implode('` IS NULL AND `', $rollupColumns) . "` IS NULL THEN 1
                                WHEN `" . implode('` IS NULL OR `', $rollupColumns) . "` IS NULL THEN 0
                                ELSE 1
                            END,
                            $windowOrderBy
                    ),
                    $limit
                )
            END AS counterRollup
        ";
    }

    /**
     * @param array<string, string> $labelColumnsOthersSwitches
     */
    private function generateGroupByExpression(bool $withRollup, array $labelColumnsOthersSwitches): string
    {
        // group by the counter - this groups "Others" because the counter stops at $limit
        $groupBy = 'counter';

        if ($withRollup) {
            // group rollups additionally by the rollup counter and the
            // full "Others" switch to ensure correct secondary level "Others" calculation
            $groupBy .= ', counterRollup, ' . implode(', ', $labelColumnsOthersSwitches);
        }

        if ($this->partitionColumn !== false) {
            $groupBy .= ', `' . $this->partitionColumn . '`';
        }

        return $groupBy;
    }

    private function generateWindowOrderByExpression(string $innerQuery, bool $withRollup): string
    {
        $selectColumns = DbHelper::extractSelectFromQuery($innerQuery);

        if ($withRollup && '*' === $selectColumns) {
            // special case for wrapped ROLLUP query
            // we find the real columns one level deeper
            $outerSelectPos = stripos($innerQuery, 'SELECT');
            $innerSelectPos = stripos($innerQuery, 'SELECT', $outerSelectPos + strlen('SELECT'));
            $realInnerQuery = substr($innerQuery, $innerSelectPos);
            $selectColumns = DbHelper::extractSelectFromQuery($realInnerQuery);
        }

        if (null === $selectColumns || '*' === $selectColumns) {
            // order by label columns if we can not find
            // the named SELECT columns to order by
            return $this->generateLabelColumnsString();
        }

        $columns = DbHelper::extractOrderByFromQuery($innerQuery, true);
        $columns = $this->prepareColumnsForWindowOrderBy($columns, $selectColumns);

        if (null === $columns) {
            $columns = DbHelper::extractGroupByFromQuery($innerQuery, true);

            if (null === $columns && $withRollup) {
                // a rollup has the GROUP BY inside the wrapper
                $outerSelectPos = stripos($innerQuery, 'SELECT');
                $innerSelectPos = stripos($innerQuery, 'SELECT', $outerSelectPos + strlen('SELECT'));
                $lastClosingParenthesis = strrpos($innerQuery, ')');

                $realInnerQuery = substr($innerQuery, $innerSelectPos, $lastClosingParenthesis - $innerSelectPos);
                $columns = DbHelper::extractGroupByFromQuery($realInnerQuery, true);
            }

            $columns = $this->prepareColumnsForWindowOrderBy($columns, $selectColumns);
        }

        if (null === $columns) {
            $columns = $this->generateLabelColumnsString();
        }

        return $columns;
    }

    private function prepareColumnsForWindowOrderBy(?string $expr, string $selectColumns): ?string
    {
        if (null === $expr) {
            return null;
        }

        $exprColumns = explode(',', $expr);

        foreach ($exprColumns as $i => &$exprColumn) {
            $columnParts = explode(' ', trim($exprColumn));

            if (count($columnParts) > 2) {
                // the column contains more than just "column ASC"
                // remove the column for safety, we don't know what we are really dealing with
                unset($exprColumns[$i]);
                continue;
            }

            $column = str_replace('`', '', trim($columnParts[0]));
            $quotedColumn = preg_quote($column, '/');

            if (preg_match('/`?' . $quotedColumn . '`? AS [`"\']?(\w+)[`"\']?(?:,|$)/is', $selectColumns, $matches)) {
                // unalias the column to allow usage in window
                $column = trim($matches[1]);
                $quotedColumn = preg_quote($column, '/');
                $columnParts[0] = '`' . $column . '`';
                $exprColumn = implode(' ', $columnParts);
            }

            if (!preg_match('/[`"\']?' . $quotedColumn . '[`"\']?(?:,|$)/is', $selectColumns)) {
                // the column was not found as "column," or "column<end of select>" in the SELECT part
                // we remove it from the window because it otherwise break the query
                unset($exprColumns[$i]);
            }
        }

        if ([] === $exprColumns) {
            return null;
        }

        return implode(', ', array_map('trim', $exprColumns));
    }
}
