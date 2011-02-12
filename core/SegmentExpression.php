<?php
class Piwik_SegmentExpression
{
    const AND_DELIMITER = ';';
    const OR_DELIMITER = ',';
    
    const MATCH_EQUAL = '==';
    const MATCH_NOT_EQUAL = '!=';
    
    const INDEX_BOOL_OPERATOR = 0;
    const INDEX_OPERAND = 1;
    
    function __construct($string)
    {
        $this->string = $string;
        $this->tree = $this->parseTree();
    }
    protected $valuesBind = array();
    protected $parsedTree = array();
    protected $tree = array();
    
    /**
     * Given the array of parsed filters containing, for each filter, 
     * the boolean operator (AND/OR) and the operand,
     * Will return the array where the filters are in SQL representation
     */
    public function parseSubExpressions()
    {
        $parsedSubExpressions = array();
        foreach($this->tree as $id => $leaf)
        {
            $operand = $leaf[self::INDEX_OPERAND];
            $operator = $leaf[self::INDEX_BOOL_OPERATOR];
            $pattern = '/^(.+?)('.self::MATCH_EQUAL.'|'.self::MATCH_NOT_EQUAL.'){1}(.+)/';
            $match = preg_match( $pattern, $operand, $matches );
            if($match == 0)
            {
                throw new Exception('Segment parameter \''.$operand.'\' does not appear to have a valid format.');
            }
//            var_dump($matches);
            
            $leftMember = $matches[1];
            $operation = $matches[2];
            $valueRightMember = $matches[3];
            $parsedSubExpressions[] = array( 
                self::INDEX_BOOL_OPERATOR => $operator,
                self::INDEX_OPERAND => array(
                    $leftMember,
                    $operation, 
                    $valueRightMember, 
            ));
        }
        $this->parsedSubExpressions = $parsedSubExpressions;
        return $parsedSubExpressions;
    }
    
    public function setSubExpressionsAfterCleanup($parsedSubExpressions)
    {
        $this->parsedSubExpressions = $parsedSubExpressions;
    }
    
    public function getSubExpressions()
    {
        return $this->parsedSubExpressions;
    }
    
    public function parseSubExpressionsIntoSqlExpressions()
    {
        $sqlSubExpressions = array();
        $this->valuesBind = array();
        foreach($this->parsedSubExpressions as $leaf)
        {
            $operator = $leaf[self::INDEX_BOOL_OPERATOR];
            $operandDefinition = $leaf[self::INDEX_OPERAND];
            
            $operand = $this->getSqlMatchFromDefinition($operandDefinition);
            
            $this->valuesBind[] = $operand[1];
            $operand = $operand[0];
            $sqlSubExpressions[] = array(
                self::INDEX_BOOL_OPERATOR => $operator,
                self::INDEX_OPERAND => $operand,
                );
        }
        $this->tree = $sqlSubExpressions;
    }
    
    /**
     * Given an array representing one filter operand ( left member , operation , right member)
     * Will return an array containing 
     * - the SQL substring, 
     * - the values to bind to this substring
     */
    // @todo case insensitive?
    protected function getSqlMatchFromDefinition($def)
    {
        $field = $def[0];
        $matchType = $def[1];
        $value = $def[2];
        
        $sqlMatch = '';
        if($matchType == self::MATCH_EQUAL)
        {
            $sqlMatch = '=';
        }
        elseif($matchType == self::MATCH_NOT_EQUAL)
        {
            $sqlMatch = '<>';
        } 
        else
        {
            throw new Exception("Filter contains the match type '".$matchType."' which is not supported");
        }
        return array("$field $sqlMatch ?", $value); 
    }
    
    /**
     * Given a filter string, 
     * will parse it into an array where each row contains the boolean operator applied to it, 
     * and the operand 
     */
    protected function parseTree()
    {
        $string = $this->string;
        if(empty($string)) {
            return array();
        }
        $tree = array();
        $i = 0;
        $length = strlen($string);
        $isBackslash = false;
        $operand = '';
        while($i <= $length)
        {
            $char = $string[$i];

            $isAND = ($char == self::AND_DELIMITER);
            $isOR = ($char == self::OR_DELIMITER);
            $isEnd = ($length == $i+1);
            
            if($isEnd)
            {
        	    if($isBackslash && ($isAND || $isOR))
        	    {
        	        $operand = substr($operand, 0, -1);
        	    }
                $operand .= $char;
                $tree[] = array(self::INDEX_BOOL_OPERATOR => '', self::INDEX_OPERAND => $operand);
                break;
            }
            
            if($isAND && !$isBackslash)
            {
            	$tree[] = array(self::INDEX_BOOL_OPERATOR => 'AND', self::INDEX_OPERAND => $operand);
            	$operand = '';
        	}
        	elseif($isOR && !$isBackslash)
        	{
        	    $tree[] = array(self::INDEX_BOOL_OPERATOR => 'OR', self::INDEX_OPERAND => $operand);
            	$operand = '';
        	}
        	else
        	{
        	    if($isBackslash && ($isAND || $isOR))
        	    {
        	        $operand = substr($operand, 0, -1);
        	    }
            	$operand .= $char;
        	}
            $isBackslash = ($char == "\\");
            $i++;
        }
        return $tree;
    }
    
    /**
     * Given the array of parsed boolean logic, will return
     * an array containing the full SQL string representing the filter, 
     * and the values to bind to it
     * 
     * @return array SQL Query, and Bind parameters
     */
    public function getSql()
    {
        if(count($this->tree) == 0) 
        {
            throw new Exception("Invalid segment, please specify a valid segment.");
        }
        $bind = array();
        $sql = '';
        $subExpression = false;
        foreach($this->tree as $expression)
        {
            $operator = $expression[self::INDEX_BOOL_OPERATOR];
            $operand = $expression[self::INDEX_OPERAND];
        
            if($operator == 'OR'
                && !$subExpression)
            {
                $sql .= ' (';
                $subExpression = true;
            }
            else
            {
                $sql .= ' ';
            }
            
            $sql .= $operand;
            
            if($operator == 'AND'
                && $subExpression)
            {
                $sql .= ')';
                $subExpression = false;
            }
            
            $sql .= " $operator";
        }
        if($subExpression)
        {
            $sql .= ')';
        }
        return array(
        	'sql' => $sql, 
        	'bind' => $this->valuesBind
        );
    }
}