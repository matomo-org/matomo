<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Segment;

use Exception;

/**
 *
 */
class SegmentExpression
{
    const AND_DELIMITER = ';';
    const OR_DELIMITER = ',';

    const MATCH_EQUAL = '==';
    const MATCH_NOT_EQUAL = '!=';
    const MATCH_GREATER_OR_EQUAL = '>=';
    const MATCH_LESS_OR_EQUAL = '<=';
    const MATCH_GREATER = '>';
    const MATCH_LESS = '<';
    const MATCH_CONTAINS = '=@';
    const MATCH_DOES_NOT_CONTAIN = '!@';
    const MATCH_STARTS_WITH = '=^';
    const MATCH_ENDS_WITH = '=$';

    const BOOL_OPERATOR_OR = 'OR';
    const BOOL_OPERATOR_AND = 'AND';
    const BOOL_OPERATOR_END = '';

    // Note: you can't write this in the API, but access this feature
    // via field!=        <- IS NOT NULL
    // or via field==     <- IS NULL / empty
    const MATCH_IS_NOT_NULL_NOR_EMPTY = '::NOT_NULL';
    const MATCH_IS_NULL_OR_EMPTY = '::NULL';

    // Special case, since we look up Page URLs/Page titles in a sub SQL query
    const MATCH_ACTIONS_CONTAINS = 'IN';
    const MATCH_ACTIONS_NOT_CONTAINS = 'NOTIN';

    const INDEX_BOOL_OPERATOR = 0;
    const INDEX_OPERAND = 1;

    const INDEX_OPERAND_NAME = 0;
    const INDEX_OPERAND_OPERATOR = 1;
    const INDEX_OPERAND_VALUE = 2;

    const SQL_WHERE_DO_NOT_MATCH_ANY_ROW = "(1 = 0)";
    const SQL_WHERE_MATCHES_ALL_ROWS = "(1 = 1)";

    protected $string;
    protected $joins = [];
    protected $valuesBind = [];
    public $tree = [];
    public $parsedSubExpressions = [];

    public function __construct($string)
    {
        $this->string = $string;
        $this->tree = $this->parseTree();
    }

    public function getSegmentDefinition()
    {
        return $this->string;
    }

    public function isEmpty()
    {
        return count($this->tree) == 0;
    }


    public function getSubExpressionCount()
    {
        $cleaned = array_filter($this->parsedSubExpressions, function ($part) {
            $isExpressionColumnPresent = !empty($part[1][0]);
            return $isExpressionColumnPresent;
        });
        return count($cleaned);
    }

    /**
     * Given the array of parsed filters containing, for each filter,
     * the boolean operator (AND/OR) and the operand,
     * Will return the array where the filters are in SQL representation
     *
     * @throws Exception
     * @return array
     */
    public function parseSubExpressions()
    {
        $parsedSubExpressions = array();
        foreach ($this->tree as $leaf) {
            $operand = $leaf[self::INDEX_OPERAND];

            $operand = urldecode($operand);

            $operator = $leaf[self::INDEX_BOOL_OPERATOR];
            $pattern = '/^(.+?)(' . self::MATCH_EQUAL . '|'
                . self::MATCH_NOT_EQUAL . '|'
                . self::MATCH_GREATER_OR_EQUAL . '|'
                . self::MATCH_GREATER . '|'
                . self::MATCH_LESS_OR_EQUAL . '|'
                . self::MATCH_LESS . '|'
                . self::MATCH_CONTAINS . '|'
                . self::MATCH_DOES_NOT_CONTAIN . '|'
                . preg_quote(self::MATCH_STARTS_WITH) . '|'
                . preg_quote(self::MATCH_ENDS_WITH)
                . '){1}(.*)/';
            $match = preg_match($pattern, $operand, $matches);
            if ($match == 0) {
                throw new Exception('The segment condition \'' . $operand . '\' is not valid.');
            }

            $leftMember = $matches[1];
            $operation  = $matches[2];
            $valueRightMember = urldecode($matches[3]);

            // is null / is not null
            if ($valueRightMember === '') {
                if ($operation == self::MATCH_NOT_EQUAL) {
                    $operation = self::MATCH_IS_NOT_NULL_NOR_EMPTY;
                } elseif ($operation == self::MATCH_EQUAL) {
                    $operation = self::MATCH_IS_NULL_OR_EMPTY;
                } else {
                    throw new Exception('The segment \'' . $operand . '\' has no value specified. You can leave this value empty ' .
                        'only when you use the operators: ' . self::MATCH_NOT_EQUAL . ' (is not) or ' . self::MATCH_EQUAL . ' (is)');
                }
            }

            $parsedSubExpressions[] = array(
                self::INDEX_BOOL_OPERATOR => $operator,
                self::INDEX_OPERAND       => array(
                    self::INDEX_OPERAND_NAME => $leftMember,
                    self::INDEX_OPERAND_OPERATOR => $operation,
                    self::INDEX_OPERAND_VALUE => $valueRightMember,
                ));
        }
        $this->parsedSubExpressions = $parsedSubExpressions;
        return $parsedSubExpressions;
    }

    /**
     * TODO
     *
     * @param array $expressions
     * @return void
     */
    public function collapseExpressionsWithSameOperand(array $expressions)
    {
        // replace AND expressions of single conditions w/ the same

        // replace contiguous ORs (includes last OR operator + next one)

        return $expressions;
    }

    /**
     * Set the given expression
     * @param $parsedSubExpressions
     */
    public function setSubExpressionsAfterCleanup($parsedSubExpressions)
    {
        $this->parsedSubExpressions = $parsedSubExpressions;
    }

    /**
     * @param array $availableTables
     */
    public function parseSubExpressionsIntoSqlExpressions(&$availableTables = array())
    {
        $sqlSubExpressions = $this->parsedSubExpressions;
        $this->valuesBind = array();
        $this->joins = array();

        foreach ($sqlSubExpressions as &$orExpressions) {
            foreach ($orExpressions as &$condition) {
                $operand = $this->getSqlMatchFromDefinition($condition, $availableTables);

                if ($operand[self::INDEX_OPERAND_OPERATOR] !== null) {
                    if (is_array($operand[self::INDEX_OPERAND_OPERATOR])) {
                        $this->valuesBind = array_merge($this->valuesBind, $operand[self::INDEX_OPERAND_OPERATOR]);
                    } else {
                        $this->valuesBind[] = $operand[self::INDEX_OPERAND_OPERATOR];
                    }
                }

                $operand = $operand[self::INDEX_OPERAND_NAME];
                $condition = $operand;
            }
        }

        $this->tree = $sqlSubExpressions;
    }

    /**
     * Given an array representing one filter operand ( left member , operation , right member)
     * Will return an array containing
     * - the SQL substring,
     * - the values to bind to this substring
     *
     * @param array $def
     * @param array $availableTables
     * @throws Exception
     * @return array
     */
    protected function getSqlMatchFromDefinition($def, &$availableTables)
    {
        $field     = $def[0];
        $matchType = $def[1];
        $value     = $def[2];

        // Segment::getCleanedExpression() may return array(null, $matchType, null)
        $operandWillNotMatchAnyRow = empty($field) && is_null($value);
        if($operandWillNotMatchAnyRow) {
            if($matchType == self::MATCH_EQUAL) {
                // eg. pageUrl==DoesNotExist
                // Equal to NULL means it will match none
                $sqlExpression = self::SQL_WHERE_DO_NOT_MATCH_ANY_ROW;
            } elseif($matchType == self::MATCH_NOT_EQUAL) {
                // eg. pageUrl!=DoesNotExist
                // Not equal to NULL means it matches all rows
                $sqlExpression = self::SQL_WHERE_MATCHES_ALL_ROWS;
            } elseif($matchType == self::MATCH_CONTAINS
                  || $matchType == self::MATCH_DOES_NOT_CONTAIN
                  || $matchType == self::MATCH_STARTS_WITH
                  || $matchType == self::MATCH_ENDS_WITH) {
                // no action was found for CONTAINS / DOES NOT CONTAIN
                // eg. pageUrl=@DoesNotExist -> matches no row
                // eg. pageUrl!@DoesNotExist -> matches no rows
                $sqlExpression = self::SQL_WHERE_DO_NOT_MATCH_ANY_ROW;
            } else {
                // it is not expected to reach this code path
                throw new Exception("Unexpected match type $matchType for your segment. " .
                    "Please report this issue to the Matomo team with the segment you are using.");
            }

            return array($sqlExpression, $value = null);
        }

        $alsoMatchNULLValues = false;
        switch ($matchType) {
            case self::MATCH_EQUAL:
                $sqlMatch = '%s =';
                break;
            case self::MATCH_NOT_EQUAL:
                $sqlMatch = '%s <>';
                $alsoMatchNULLValues = true;
                break;
            case self::MATCH_GREATER:
                $sqlMatch = '%s >';
                break;
            case self::MATCH_LESS:
                $sqlMatch = '%s <';
                break;
            case self::MATCH_GREATER_OR_EQUAL:
                $sqlMatch = '%s >=';
                break;
            case self::MATCH_LESS_OR_EQUAL:
                $sqlMatch = '%s <=';
                break;
            case self::MATCH_CONTAINS:
                $sqlMatch = '%s LIKE';
                $value    = '%' . $this->escapeLikeString($value) . '%';
                break;
            case self::MATCH_DOES_NOT_CONTAIN:
                $sqlMatch = '%s NOT LIKE';
                $value    = '%' . $this->escapeLikeString($value) . '%';
                $alsoMatchNULLValues = true;
                break;
            case self::MATCH_STARTS_WITH:
                $sqlMatch = '%s LIKE';
                $value    = $this->escapeLikeString($value) . '%';
                break;
            case self::MATCH_ENDS_WITH:
                $sqlMatch = '%s LIKE';
                $value    = '%' . $this->escapeLikeString($value);
                break;

            case self::MATCH_IS_NOT_NULL_NOR_EMPTY:
                $sqlMatch = '%s IS NOT NULL AND %s <> \'\' AND %s <> \'0\'';
                $value    = null;
                break;

            case self::MATCH_IS_NULL_OR_EMPTY:
                $sqlMatch = '%s IS NULL OR %s = \'\' OR %s = \'0\'';
                $value    = null;
                break;

            case self::MATCH_ACTIONS_CONTAINS:
                // this match type is not accessible from the outside
                // (it won't be matched in self::parseSubExpressions())
                // it can be used internally to inject sub-expressions into the query.
                // see Segment::getCleanedExpression()
                $sqlMatch = '%s IN (' . $value['SQL'] . ')';
                $value    = $value['bind'];
                break;
            case self::MATCH_ACTIONS_NOT_CONTAINS:
                // this match type is not accessible from the outside
                // (it won't be matched in self::parseSubExpressions())
                // it can be used internally to inject sub-expressions into the query.
                // see Segment::getCleanedExpression()
                $sqlMatch = '%s NOT IN (' . $value['sql'] . ')';
                $value    = $value['bind'];
                break;
            default:
                throw new Exception("Filter contains the match type '" . $matchType . "' which is not supported");
                break;
        }

        // We match NULL values when rows are excluded only when we are not doing a
        $alsoMatchNULLValues = $alsoMatchNULLValues && !empty($value);
        $sqlMatch = str_replace('%s', $field, $sqlMatch);

        if ($matchType === self::MATCH_ACTIONS_CONTAINS || $matchType === self::MATCH_ACTIONS_NOT_CONTAINS
            || is_null($value)
        ) {
            $sqlExpression = "( $sqlMatch )";
        } else {
            if ($alsoMatchNULLValues) {
                $sqlExpression = "( $field IS NULL OR $sqlMatch ? )";
            } else {
                $sqlExpression = "$sqlMatch ?";
            }
        }

        $columns = self::parseColumnsFromSqlExpr($field);
        foreach ($columns as $column) {
            $this->checkFieldIsAvailable($column, $availableTables);
        }

        return array($sqlExpression, $value);
    }

    /**
     * @param string $field
     * @return string[]
     */
    public static function parseColumnsFromSqlExpr($field)
    {
        preg_match_all('/[^@a-zA-Z0-9_]?`?([@a-zA-Z_][@a-zA-Z0-9_]*`?\.`?[a-zA-Z0-9_`]+)`?\b/', $field, $matches);
        $result = isset($matches[1]) ? $matches[1] : [];
        $result = array_filter($result, function ($value) { // remove uses of session vars
            return strpos($value, '@') === false;
        });
        $result = array_map(function ($item) {
            return str_replace('`', '', $item);
        }, $result);
        $result = array_unique($result);
        $result = array_values($result);
        return $result;
    }

    /**
     * Check whether the field is available
     * If not, add it to the available tables
     *
     * @param string $field
     * @param array $availableTables
     */
    private function checkFieldIsAvailable($field, &$availableTables)
    {
        $fieldParts = explode('.', $field);

        $table = count($fieldParts) == 2 ? $fieldParts[0] : false;

        // remove sql functions from field name
        // example: `HOUR(log_visit.visit_last_action_time)` gets `HOUR(log_visit` => remove `HOUR(`
        $table = preg_replace('/^[A-Z_]+\(/', '', $table);
        $tableExists = !$table || in_array($table, $availableTables);

        if ($tableExists) {
            return;
        }

        if (is_array($availableTables)) {
            foreach ($availableTables as $availableTable) {
                if (is_array($availableTable)) {
                    if (!isset($availableTable['tableAlias']) && $availableTable['table'] === $table) {
                        return;
                    } elseif (isset($availableTable['tableAlias']) && $availableTable['tableAlias'] === $table) {
                        return;
                    }
                }
            }
        }

        $availableTables[] = $table;
    }

    /**
     * Escape the characters % and _ in the given string
     * @param string $str
     * @return string
     */
    private function escapeLikeString($str)
    {
        if (false !== strpos($str, '%')) {
            $str = str_replace("%", "\%", $str);
        }

        if (false !== strpos($str, '_')) {
            $str = str_replace("_", "\_", $str);
        }

        return $str;
    }

    /**
     * Given a filter string,
     * will parse it into an array where each row contains the boolean operator applied to it,
     * and the operand
     *
     * @return array
     */
    protected function parseTree()
    {
        // split by AND first, then OR since OR has greater precedence
        $expressions = preg_split('/(?<!\\\\)' . self::AND_DELIMITER . '/', $this->string);
        $expressions = array_filter($expressions);

        $expressions = array_map(function ($expr) {
            $ors = preg_split('/(?<!\\\\)' . self::OR_DELIMITER . '/', $expr);
            $ors = array_filter($ors);
            return $ors;
        }, $expressions);

        $conditionParsePattern = '/^(.+?)(' . self::MATCH_EQUAL . '|'
            . self::MATCH_NOT_EQUAL . '|'
            . self::MATCH_GREATER_OR_EQUAL . '|'
            . self::MATCH_GREATER . '|'
            . self::MATCH_LESS_OR_EQUAL . '|'
            . self::MATCH_LESS . '|'
            . self::MATCH_CONTAINS . '|'
            . self::MATCH_DOES_NOT_CONTAIN . '|'
            . preg_quote(self::MATCH_STARTS_WITH) . '|'
            . preg_quote(self::MATCH_ENDS_WITH)
            . '){1}(.*)/';

        // parse individual conditions
        foreach ($expressions as &$orExprs) {
            foreach ($orExprs as &$condition) {
                $condition = urldecode($condition);
                $condition = str_replace(['\\,', '\\;'], [',', ';'], $condition); // TODO: check if we need to escape everything or just ; & ,

                $match = preg_match($conditionParsePattern, $condition, $matches);
                if ($match == 0) {
                    throw new Exception('The segment condition \'' . $condition . '\' is not valid.');
                }

                $leftMember = $matches[1];
                $operation  = $matches[2];
                $valueRightMember = urldecode($matches[3]);

                // is null / is not null
                if ($valueRightMember === '') {
                    if ($operation == self::MATCH_NOT_EQUAL) {
                        $operation = self::MATCH_IS_NOT_NULL_NOR_EMPTY;
                    } elseif ($operation == self::MATCH_EQUAL) {
                        $operation = self::MATCH_IS_NULL_OR_EMPTY;
                    } else {
                        throw new Exception('The segment condition \'' . $condition . '\' has no value specified. You can leave this value empty ' .
                            'only when you use the operators: ' . self::MATCH_NOT_EQUAL . ' (is not) or ' . self::MATCH_EQUAL . ' (is)');
                    }
                }

                $condition = [
                    self::INDEX_OPERAND_NAME => $leftMember,
                    self::INDEX_OPERAND_OPERATOR => $operation,
                    self::INDEX_OPERAND_VALUE => $valueRightMember,
                ];
            }
        }

        return $expressions;
    }

    /**
     * Given the array of parsed boolean logic, will return
     * an array containing the full SQL string representing the filter,
     * the needed joins and the values to bind to the query
     *
     * @throws Exception
     * @return array SQL Query, Joins and Bind parameters
     */
    public function getSql()
    {
        if ($this->isEmpty()) {
            throw new Exception("Invalid segment, please specify a valid segment.");
        }

        $andExpressions = array_map(function ($orExpressions) {
            if (count($orExpressions) == 1) {
                return $orExpressions[0];
            }

            return '(' . implode(' OR ', $orExpressions) . ')';
        }, $this->tree);

        $sql = ' ' . implode(' AND ', $andExpressions);

        return array(
            'where' => $sql,
            'bind'  => $this->valuesBind,
            'join'  => implode(' ', $this->joins)
        );
    }
}

