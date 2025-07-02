<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;
use Piwik\Db\Schema;

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
     *
     * @var array<string, true>
     */
    private $labelColumns = [];

    /**
     * The columns of the inner query that are not labels
     * Format: "label" => "aggregation function" or false for no aggregation
     *
     * @var array<int|string, string|false>
     */
    private $additionalColumns = [];

    /**
     * The limit for each group
     *
     * @var int
     */
    private $limit = 5;

    /**
     * The name of the columns that marks rows to be excluded from the limit
     *
     * @var string|false
     */
    private $columnToMarkExcludedRows = false;

    /**
     * The column that is used to partition the result
     *
     * @var string|false
     */
    private $partitionColumn = false;

    /**
     * The possible values for the column $this->partitionColumn
     *
     * @var array<int>
     */
    private $partitionColumnValues = [];

    /**
     * The value to use in the label of the 'Others' row.
     *
     * @var string
     */
    private $othersLabelValue = self::LABEL_SUMMARY_ROW;

    /**
     * Constructor.
     *
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
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * Set the value to use for the label in the 'Others' row.
     *
     * @param string $value
     */
    public function setOthersLabel(string $value): void
    {
        $this->othersLabelValue = $value;
    }

    /**
     * Add a label column.
     * Labels are the columns that are replaced with "Others" after the limit.
     *
     * @param string|array<string> $labelColumn
     */
    public function addLabelColumn($labelColumn): void
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
     * @return array<string, true>
     */
    public function getLabelColumns(): array
    {
        return $this->labelColumns;
    }

    /**
     * Add a column that has be added to the outer queries.
     *
     * @param int|string|array<int|string> $column
     * @param string|false $aggregationFunction If set, this function is used to aggregate the values of "Others",
     *                                          eg, `'min'`, `'max'` or `'sum'`.
     */
    public function addColumn($column, $aggregationFunction = false): void
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
    public function setColumnToMarkExcludedRows(string $column): void
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
     * @param array<int> $possibleValues Array of possible column values.
     * @throws Exception if method is used more than once.
     */
    public function partitionResultIntoMultipleGroups(string $partitionColumn, array $possibleValues): void
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
     * @param string $innerQuery         The "payload" query that does the actual data aggregation. The ordering
     *                                   has to be specified in this query. {@link RankingQuery} cannot apply ordering
     *                                   itself.
     * @param array<string, mixed> $bind Bindings for the inner query.
     * @param int $timeLimit             Adds a MAX_EXECUTION_TIME query hint to the query if $timeLimit > 0
     *                                   for more details see {@link DbHelper::addMaxExecutionTimeHintToQuery}
     * @return array<mixed>              The format depends on which methods have been used
     *                                   to configure the ranking query.
     */
    public function execute(string $innerQuery, array $bind, int $timeLimit = 0): array
    {
        $query = $this->generateRankingQuery($innerQuery);
        $query = DbHelper::addMaxExecutionTimeHintToQuery($query, $timeLimit);

        $data  = Db::getReader()->fetchAll($query, $bind);

        if ($this->columnToMarkExcludedRows !== false) {
            // split the result into the regular result and the rows with special treatment
            $excludedFromLimit = [];
            $result = [];

            foreach ($data as &$row) {
                if ($row[$this->columnToMarkExcludedRows] != 0) {
                    $excludedFromLimit[] = $row;
                } else {
                    $result[] = $row;
                }
            }

            $data = [
                'result'            => &$result,
                'excludedFromLimit' => &$excludedFromLimit
            ];
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

    /**
     * @param array<array<mixed>> $data
     *
     * @return array<array<array<mixed>>>
     */
    private function splitPartitions(array &$data): array
    {
        $result = [];

        foreach ($data as &$row) {
            $partition = $row[$this->partitionColumn];

            if (!isset($result[$partition])) {
                $result[$partition] = [];
            }

            $result[$partition][] = &$row;
        }

        return $result;
    }

    /**
     * Generate the SQL code that does the magic.
     * If you want to get the result, use execute() instead. If you want to run the query
     * yourself, use this method.
     *
     * @param string $innerQuery The "payload" query that does the actual data aggregation. The ordering
     *                           has to be specified in this query. {@link RankingQuery} cannot apply ordering
     *                           itself.
     * @param bool $withRollup   A flag which determines whether to generate the SQL query using ROLLUP
     * @return string            The entire ranking query SQL.
     */
    public function generateRankingQuery(string $innerQuery, bool $withRollup = false): string
    {
        // +1 to include "Others"
        $limit = $this->limit + 1;

        $labelColumnsString = $this->generateLabelColumnsString();
        $labelColumnsOthersSwitch = $this->generateLabelColumnsOthersSwitch($limit, $withRollup);
        $additionalColumnsExpressions = $this->generateAdditionalColumnsExpressions();
        $counterExpressions = $this->generateVariableCounterExpressions($limit, $withRollup);
        $counterRollupExpressions = $this->generateVariableCounterRollupExpressions($limit, $withRollup);

        $innerQuery = $this->prepareInnerQuery($innerQuery);
        $withCounterQuery = $this->prepareWithCounterQuery(
            $innerQuery,
            $withRollup,
            $labelColumnsString,
            $counterExpressions,
            $counterRollupExpressions,
            $additionalColumnsExpressions
        );

        // group by the counter - this groups "Others" because the counter stops at $limit
        $groupBy = 'counter';

        if ($withRollup) {
            $groupBy .= ', counterRollup';
        }

        if ($this->partitionColumn !== false) {
            $groupBy .= ', `' . $this->partitionColumn . '`';
        }

        $rankingSelectString = implode(
            ',
            ',
            array_filter([
                $labelColumnsOthersSwitch,
                $additionalColumnsExpressions['additionalColumnsAggregated'],
            ])
        );

        $rankingQuery = "
            SELECT
                $rankingSelectString
            FROM ( $withCounterQuery ) AS withCounter
            GROUP BY $groupBy
        ";

        if (!Schema::getInstance()->supportsSortingInSubquery()) {
            // When subqueries aren't sorted, we need to sort the result manually again
            $rankingQuery .= " ORDER BY counter";

            if ($withRollup) {
                $rankingQuery .= ', counterRollup';
            }
        }

        return $rankingQuery;
    }

    /**
     * Generate the additional column parts of the ranking query.
     *
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
     * Generate the "Others" switch conditions for all label columns.
     */
    private function generateLabelColumnsOthersSwitch(int $limit, bool $withRollup): string
    {
        $isFirstLabelColumn = true;
        $switches = [];

        foreach (array_keys($this->labelColumns) as $column) {
            $rollupWhen = '';

            if ($withRollup) {
                $rollupLimitValue = $isFirstLabelColumn ? "'" . $this->othersLabelValue . "'" : 'NULL';
                $rollupWhen = "
                    WHEN counterRollup = $limit THEN $rollupLimitValue
                    WHEN counterRollup > 0 THEN `$column`
                ";

                $isFirstLabelColumn = false;
            }

            $switches[] = "
                CASE
                    $rollupWhen
                    WHEN counter = $limit THEN '" . $this->othersLabelValue . "'
                    ELSE `$column`
                END AS `$column`
            ";
        }

        return implode(', ', $switches);
    }

    /**
     * Generate the label column part of the ranking query.
     */
    private function generateLabelColumnsString(): string
    {
        return '`' . implode('`, `', array_keys($this->labelColumns)) . '`';
    }

    /**
     * Generate the ranking query counter expressions using variables.
     *
     * @return array{counter: string, init: string}
     */
    private function generateVariableCounterExpressions(int $limit, bool $withRollup): array
    {
        $inits = [];
        $whens = [];

        if ($this->columnToMarkExcludedRows !== false) {
            // when a row has been specified that marks which records should be excluded
            // from limiting, we don't give those rows the normal counter but -1 times the
            // value they had before. this way, they have a separate number space (i.e. negative
            // integers).
            $whens[] = "WHEN {$this->columnToMarkExcludedRows} != 0 THEN -1 * {$this->columnToMarkExcludedRows}";
        }

        if ($withRollup) {
            foreach (array_keys($this->labelColumns) as $column) {
                $whens[] = "WHEN `$column` IS NULL THEN -1";
            }
        }

        if ($this->partitionColumn !== false) {
            // partition: one counter per possible value
            foreach ($this->partitionColumnValues as $value) {
                $isValue = '`' . $this->partitionColumn . '` = ' . intval($value);
                $partitionCounter = '@counter' . intval($value);

                $whens[] = "WHEN $isValue AND $partitionCounter = $limit THEN $limit";
                $whens[] = "WHEN $isValue THEN $partitionCounter := $partitionCounter + 1";
                $inits[] = "( SELECT $partitionCounter := 0 ) initCounter" . intval($value);
            }

            $whens[] = "ELSE 0";
        } else {
            // no partitioning: add a single counter
            $whens[] = "WHEN @counter = $limit THEN $limit";
            $whens[] = "ELSE @counter := @counter + 1";

            $inits[] = '( SELECT @counter := 0 ) initCounter';
        }

        $init = implode(', ', $inits);
        $counter = "
            CASE
                " . implode("
                ", $whens) . "
            END AS counter
        ";

        return [
            'init' => $init,
            'counter' => $counter,
        ];
    }

    /**
     * Generate the rollup counter expressions using variables.
     *
     * @return array{counter: string, init: string}
     */
    private function generateVariableCounterRollupExpressions(int $limit, bool $withRollup): array
    {
        $counter = '';
        $init = '';

        if ($withRollup) {
            $rollupColumns = array_keys($this->labelColumns);
            $whens = [];

            if (count($rollupColumns) >= 2) {
                $whens[] = "WHEN `" . implode('` IS NULL AND `', $rollupColumns) . "` IS NULL THEN -1";
            }

            foreach ($rollupColumns as $withRollupColumn) {
                $whens[] = "WHEN `$withRollupColumn` IS NULL AND @counterRollup = $limit THEN $limit";
                $whens[] = "WHEN `$withRollupColumn` IS NULL THEN @counterRollup := @counterRollup + 1";
            }

            $init = '( SELECT @counterRollup := 0 ) initCounterRollup';
            $counter = "
                CASE
                    " . implode("
                    ", $whens) . "
                    ELSE 0
                END AS counterRollup
            ";
        }

        return [
            'init' => $init,
            'counter' => $counter,
        ];
    }

    /**
     * Prepare the inner query for usage in the ranking query.
     */
    private function prepareInnerQuery(string $query): string
    {
        if (false === strpos($query, ' LIMIT ') && !Schema::getInstance()->supportsSortingInSubquery()) {
            // Setting a limit for the inner query forces the optimizer to use a temporary table, which uses the sorting
            $query .= ' LIMIT 18446744073709551615';
        }

        return $query;
    }

    /**
     * Prepare the query with added counters.
     *
     * @param array{counter: string, init: string} $counterExpressions
     * @param array{counter: string, init: string} $counterRollupExpressions
     * @param array{additionalColumns: string, additionalColumnsAggregated: string} $additionalColumnsExpressions
     */
    private function prepareWithCounterQuery(
        string $innerQuery,
        bool $withRollup,
        string $labelColumnsString,
        array $counterExpressions,
        array $counterRollupExpressions,
        array $additionalColumnsExpressions
    ): string {
        $selectString = implode(
            ',
            ',
            array_filter([
                $labelColumnsString,
                $counterExpressions['counter'],
                $counterRollupExpressions['counter'],
                $additionalColumnsExpressions['additionalColumns'],
            ])
        );

        $fromString = implode(
            ',
            ',
            array_filter([
                $counterExpressions['init'],
                $counterRollupExpressions['init'],
                "( $innerQuery ) actualQuery",
            ])
        );

        // add a counter to the query
        // we rely on the sorting of the inner query
        $query = "
            SELECT
                $selectString
            FROM
            $fromString
        ";

        if ($withRollup && !Schema::getInstance()->supportsRankingRollupWithoutExtraSorting()) {
            // MariaDB requires an additional sorting layer to return
            // the counter/counterRollup values we expect
            $rollupColumnSorts = [];

            foreach (array_keys($this->labelColumns) as $rollupColumn) {
                $rollupColumnSorts[] = "`$rollupColumn` IS NULL";
            }

            $query .= ' ORDER BY ' . implode(', ', $rollupColumnSorts);
            $innerQueryOrderBy = DbHelper::extractOrderByFromQuery($innerQuery, true);

            if (null !== $innerQueryOrderBy) {
                // copy ORDER BY from inner query to rollup sorting
                $query .= ', ' . $innerQueryOrderBy;
            }
        }

        return $query;
    }
}
