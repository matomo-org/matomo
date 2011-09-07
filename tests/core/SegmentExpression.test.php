<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_Piwik_SegmentExpression extends UnitTestCase
{
    public function test_SegmentSql_simpleNoOperation()
    {
        $expressionToSql = array(
            // classic expressions
            'A' => " A ",
            'A,B' => " (A OR B )",
            'A;B' => " A AND B ",
            'A;B;C' => " A AND B AND C ",
            'A,B;C,D;E,F,G' => " (A OR B) AND (C OR D) AND (E OR F OR G )",
        
            // unescape the backslash 
            'A\,B\,C,D' => " (A,B,C OR D )",
            '\,A' => ' ,A ',
            // unescape only when it was escaping a known delimiter
            '\\\A' => ' \\\A ',
            // unescape at the end
            '\,\;\A\B,\,C,D\;E\,' => ' (,;\A\B OR ,C OR D;E, )',
        
            // only replace when a following expression is detected
            'A,' => ' A, ',
            'A;' => ' A; ',
            'A;B;' => ' A AND B; ',
            'A,B,' => ' (A OR B, )',
        );
        foreach($expressionToSql as $expression => $expectedSql)
        {
            $segment = new Piwik_SegmentExpression($expression);
            $expected = array('where' => $expectedSql, 'bind' => array(), 'join' => '');
            $processed = $segment->getSql();
            $this->assertEqual($processed, $expected);
        }
    }
    
    public function test_SegmentSql_withOperations()
    {
        // Filter expression => SQL string + Bind values
        $expressionToSql = array(
            'A==B%' => array('where' => " A = ? ", 'bind' => array('B%')),
            'ABCDEF====B===' => array('where' => " ABCDEF = ? ", 'bind' => array('==B===')),
            'A===B;CDEF!=C!=' => array('where' => " A = ? AND CDEF <> ? ", 'bind' => array('=B', 'C!=' )),
            'A==B,C==D' => array('where' => " (A = ? OR C = ? )", 'bind' => array('B', 'D')),
            'A!=B;C==D' => array('where' => " A <> ? AND C = ? ", 'bind' => array('B', 'D')),
            'A!=B;C==D,E!=Hello World!=' => array('where' => " A <> ? AND (C = ? OR E <> ? )", 'bind' => array('B', 'D', 'Hello World!=')),
        
            'A>B' => array('where' => " A > ? ", 'bind' => array('B')),
            'A<B' => array('where' => " A < ? ", 'bind' => array('B')),
            'A<=B' => array('where' => " A <= ? ", 'bind' => array('B')),
            'A>=B' => array('where' => " A >= ? ", 'bind' => array('B')),
            'ABCDEF>=>=>=B===' => array('where' => " ABCDEF >= ? ", 'bind' => array('>=>=B===')),
            'A>=<=B;CDEF>G;H>=I;J<K;L<=M' => array('where' => " A >= ? AND CDEF > ? AND H >= ? AND J < ? AND L <= ? ", 'bind' => array('<=B', 'G','I','K','M' )),
            'A>=B;C>=D,E<w_ow great!' => array('where' => " A >= ? AND (C >= ? OR E < ? )", 'bind' => array('B', 'D', 'w_ow great!')),

        	'A=@B_' => array('where' => " A LIKE ? ", 'bind' => array('%B\_%')),
        	'A!@B%' => array('where' => " A NOT LIKE ? ", 'bind' => array('%B\%%')),
        );
        foreach($expressionToSql as $expression => $expectedSql)
        {
            $segment = new Piwik_SegmentExpression($expression);
            $segment->parseSubExpressions();
            $segment->parseSubExpressionsIntoSqlExpressions();
            $processed = $segment->getSql();
            
            $expectedSql['join'] = '';
            
            $out = '<br/>'.var_export($processed, true) . "\n *DIFFERENT FROM*   ".var_export($expectedSql, true);
            
            $this->assertEqual($processed, $expectedSql, str_replace('%', '%%', $out));
        }
    }
    
    public function test_bogusFilters_expectExceptionThrown()
    {
        $boguses = array(
            'A=B',
            'C!D',
            '',
            '      ',
            ',;,',
            ',',
            ',,',
            '===',
            '!='
        );
        foreach($boguses as $bogus) 
        {
            $segment = new Piwik_SegmentExpression($bogus);
            try {
                $segment->parseSubExpressions();
                $processed = $segment->getSql();
                $this->fail('expecting exception '.$bogus);
            } catch(Exception $e) {
                $this->pass();
            } 
        }
    }
}

