<?php
class Piwik_Segment
{
    /**
     * @var Piwik_SegmentExpression
     */
    protected $segment = null;
    public function __construct($string, $idSites)
    {
        $string = trim($string);
        $this->string = $string;
        $this->idSites = $idSites;
        $segment = new Piwik_SegmentExpression($string);
        $this->segment = $segment;

        // parse segments
        $expressions = $segment->parseSubExpressions();
        
        // convert segments name to sql segment
        // check that user is allowed to view this segment
        // and apply a filter to the value to match if necessary (to map DB fields format)
        $cleanedExpressions = array();
        foreach($expressions as $expression)
        {
            $operand = $expression[Piwik_SegmentExpression::INDEX_OPERAND];
            $cleanedExpression = $this->getCleanedExpression($operand);
            $expression[Piwik_SegmentExpression::INDEX_OPERAND] = $cleanedExpression;
            $cleanedExpressions[] = $expression;
        }
        $segment->setSubExpressionsAfterCleanup($cleanedExpressions);
    }
    
    public function getPrettyString()
    {
    	//@TODO
    }
    
    public function isEmpty()
    {
        return empty($this->string);
    }
    protected $availableSegments = array();
    protected $segmentsHumanReadable = '';

    public function getUniqueSqlFields()
    {
        $expressions = $this->segment->parsedSubExpressions;
        $uniqueFields = array();
        foreach($expressions as $expression) {
            $uniqueFields[] = $expression[Piwik_SegmentExpression::INDEX_OPERAND][0];
        }
        return $uniqueFields;
    }
    
    protected function getCleanedExpression($expression)
    {
        if(empty($this->availableSegments))
        {
            $this->availableSegments = Piwik_API_API::getInstance()->getSegmentsMetadata($this->idSites, $_hideImplementationData = false);
        }
        
        $name = $expression[0];
        $matchType = $expression[1];
        $value = $expression[2];
        $sqlName = '';
        
        foreach($this->availableSegments as $segment)
        {
            if($segment['segment'] != $name)
            {
                continue;
            }
            
            $sqlName = $segment['sqlSegment'];
            
            // check permission
            if(isset($segment['permission'])
                && $segment['permission'] != 1)
            {
                throw new Exception("You do not have enough permission to access the segment ".$name);
            }
            
//            $this->segmentsHumanReadable[] = $segment['name'] . " " . 
//                                            $this->getNameForMatchType($matchType) . 
//                                            $value;
            
            // apply presentation filter
            if(isset($segment['sqlFilter'])
            	&& !empty($segment['sqlFilter']))
            {
                $value = call_user_func($segment['sqlFilter'], $value, $segment['sqlSegment']);
            }
            break;
        }
        if(empty($sqlName))
        {
            throw new Exception("Segment '$name' is not a supported segment.");
        }
        return array( $sqlName, $expression[1], $value );
    }
    
    public function getString()
    {
        return $this->string;
    }
    
    public function getHash()
    {
        if(empty($this->string))
        {
            return '';
        }
        return md5(serialize($this->getSql()));
    }
    
    public function getSql()
    {
    	if($this->isEmpty())
    	{
    		return array('sql' => '', 'bind' => array());
    	}
        $this->segment->parseSubExpressionsIntoSqlExpressions();
        
        return $this->segment->getSql();
    }
}

