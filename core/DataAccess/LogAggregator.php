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
    public static function getSelectsFromRangedColumn( $metadata )
    {
        @list($column, $ranges, $table, $selectColumnPrefix, $i_am_your_nightmare_DELETE_ME) = $metadata;

        $selects = array();
        $extraCondition = '';
        if($i_am_your_nightmare_DELETE_ME) {
            // extra condition for the SQL SELECT that makes sure only returning visits are counted
            // when creating the 'days since last visit' report
            $extraCondition = 'and log_visit.visitor_returning = 1';
            $extraSelect = "sum(case when log_visit.visitor_returning = 0 then 1 else 0 end) "
                    . " as `". $selectColumnPrefix . 'General_NewVisits' . "`";
            $selects[] = $extraSelect;
        }
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

    /**
     * Clean up the row data and return values.
     * $lookForThisPrefix can be used to make sure only SOME of the data in $row is used.
     *
     * The array will have one column $columnName
     *
     * @param $row
     * @param $columnName
     * @param $lookForThisPrefix A string that identifies which elements of $row to use
     *                                 in the result. Every key of $row that starts with this
     *                                 value is used.
     * @return array
     */
    static public function makeArrayOneColumn($row, $columnName, $lookForThisPrefix = false)
    {
        $cleanRow = array();
        foreach ($row as $label => $count) {
            if (empty($lookForThisPrefix)
                || strpos($label, $lookForThisPrefix) === 0) {
                $cleanLabel = substr($label, strlen($lookForThisPrefix));
                $cleanRow[$cleanLabel] = array($columnName => $count);
            }
        }
        return $cleanRow;
    }

}