<?php

class Piwik_DataAccess_LogAggregator
{


    /**
     * Creates and returns an array of SQL SELECT expressions that will summarize
     * the data in a column of a specified table, over a set of ranges.
     *
     * The SELECT expressions will count the number of column values that are
     * within each range.
     *
     * @param string $column              The column of the log_conversion table to reduce.
     * @param array $ranges              The ranges to reduce data over.
     * @param string $table               The table the SELECTs should use.
     * @param string $selectColumnPrefix  The prefix when specifying what a SELECT
     *                                          expression will be selected AS.
     * @param bool|string $extraCondition      An extra condition to be appended to 'case when'
     *                                          expressions. Must start with the logical operator,
     *                                          ie (AND, OR, etc.).
     * @return array  An array of SQL SELECT expressions.
     */
    public static function buildReduceByRangeSelect( $column, $ranges, $table, $selectColumnPrefix = '', $extraCondition = false)
    {
        $selects = array();

        foreach ($ranges as $gap) {
            if (count($gap) == 2) {
                $lowerBound = $gap[0];
                $upperBound = $gap[1];

                $selectAs = "$selectColumnPrefix$lowerBound-$upperBound";

                $selects[] = "sum(case when $table.$column between $lowerBound and $upperBound $extraCondition" .
                    " then 1 else 0 end) as `$selectAs`";
            } else {
                $lowerBound = $gap[0];

                $selectAs = $selectColumnPrefix . ($lowerBound + 1) . urlencode('+');

                $selects[] = "sum(case when $table.$column > $lowerBound $extraCondition then 1 else 0 end) as `$selectAs`";
            }
        }

        return $selects;
    }

}