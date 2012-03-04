<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once "Database.test.php";
class Test_Piwik_Segment extends UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();	

		// setup the access layer (required in Segment contrustor testing if anonymous is allowed to use segments)
		$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
		
		// Load and install plugins
    	$pluginsManager = Piwik_PluginsManager::getInstance();
    	$pluginsManager->loadPlugins( Piwik_Config::getInstance()->Plugins['Plugins'] );
    }
    
    public function test_common()
    {
        $tests = array(
            // Normal segment
        	'country==France' => array(
        			'where' => ' log_visit.location_country = ? ',
        			'bind' => array('France')),
        
            // unescape the comma please
            'country==a\,==' => array(
            		'where' => ' log_visit.location_country = ? ',
            		'bind' => array('a,==')), 

            // AND, with 2 values rewrites
            'country==a;visitorType!=returning;visitorType==new' => array(
					'where' => ' log_visit.location_country = ? AND log_visit.visitor_returning <> ? AND log_visit.visitor_returning = ? ', 
					'bind' => array('a', '1', '0')), 
            
            // OR, with 2 value rewrites
            'referrerType==search,referrerType==direct' => array(
					'where'=>' (log_visit.referer_type = ? OR log_visit.referer_type = ? )',
					'bind' => array(Piwik_Common::REFERER_TYPE_SEARCH_ENGINE, 
        							Piwik_Common::REFERER_TYPE_DIRECT_ENTRY)),
        );
        
        foreach($tests as $segment => $expected)
        {
        	$select = 'log_visit.idvisit';
        	$from = 'log_visit';
        	
        	$expected = array(
        		'sql' => '
			SELECT
				log_visit.idvisit
			FROM
				'.Piwik_Common::prefixTable('log_visit').' AS log_visit
			WHERE
				'.$expected['where'],
        		'bind' => $expected['bind']
        	);
        	
        	$segment = new Piwik_Segment($segment, $idSites = array());
            $sql = $segment->getSelectQuery($select, $from, false);
            $this->assertEqual($sql, $expected, var_export($sql, true));
            
            // calling twice should give same results
            $sql = $segment->getSelectQuery($select, array($from));
            $this->assertEqual($sql, $expected, var_export($sql, true));
            
            $this->assertEqual(strlen($segment->getHash()), 32);
        }
    }
    
    public function test_getSelectQuery_noJoin()
    {
    	$select = '*';
    	$from = 'log_visit';
    	$where = 'idsite = ?';
    	$bind = array(1);
    	
    	$segment = 'customVariableName1==Test;visitorType==new';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				*
			FROM
				".Piwik_Common::prefixTable('log_visit')." AS log_visit
			WHERE
				( idsite = ? )
				AND
				( log_visit.custom_var_k1 = ? AND log_visit.visitor_returning = ? )",
    		"bind" => array(1, 'Test', 0));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }
    
    public function test_getSelectQuery_joinVisitOnAction()
    {
    	$select = '*';
    	$from = 'log_link_visit_action';
    	$where = 'log_link_visit_action.idvisit = ?';
    	$bind = array(1);
    	
    	$segment = 'customVariablePageName1==Test;visitorType==new';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				*
			FROM
				".Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action
				LEFT JOIN ".Piwik_Common::prefixTable('log_visit')." AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
			WHERE
				( log_link_visit_action.idvisit = ? )
				AND
				( log_link_visit_action.custom_var_k1 = ? AND log_visit.visitor_returning = ? )",
    		"bind" => array(1, 'Test', 0));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }

    public function test_getSelectQuery_joinActionOnVisit()
    {
    	$select = 'sum(log_visit.visit_total_actions) as nb_actions, max(log_visit.visit_total_actions) as max_actions, sum(log_visit.visit_total_time) as sum_visit_length';
    	$from = 'log_visit';
    	$where = 'log_visit.idvisit = ?';
    	$bind = array(1);
    	
    	$segment = 'customVariablePageName1==Test;visitorType==new';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				sum(log_inner.visit_total_actions) as nb_actions, max(log_inner.visit_total_actions) as max_actions, sum(log_inner.visit_total_time) as sum_visit_length
			FROM
				(
			SELECT
				log_visit.visit_total_actions,
				log_visit.visit_total_time
			FROM
				".Piwik_Common::prefixTable('log_visit')." AS log_visit
				LEFT JOIN ".Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
			WHERE
				( log_visit.idvisit = ? )
				AND
				( log_link_visit_action.custom_var_k1 = ? AND log_visit.visitor_returning = ? )
			GROUP BY log_visit.idvisit
				) AS log_inner",
    		"bind" => array(1, 'Test', 0));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }
    
    public function test_getSelectQuery_joinConversionOnAction()
    {
    	$select = '*';
    	$from = 'log_link_visit_action';
    	$where = 'log_link_visit_action.idvisit = ?';
    	$bind = array(1);
    	
    	$segment = 'customVariablePageName1==Test;visitConvertedGoalId==1;customVariablePageName2==Test2';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				*
			FROM
				".Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action
				LEFT JOIN ".Piwik_Common::prefixTable('log_conversion')." AS log_conversion ON log_conversion.idlink_va = log_link_visit_action.idlink_va AND log_conversion.idsite = log_link_visit_action.idsite
			WHERE
				( log_link_visit_action.idvisit = ? )
				AND
				( log_link_visit_action.custom_var_k1 = ? AND log_conversion.idgoal = ? AND log_link_visit_action.custom_var_k2 = ? )",
    		"bind" => array(1, 'Test', 1, 'Test2'));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }
    
    public function test_getSelectQuery_joinActionOnConversion()
    {
    	$select = '*';
    	$from = 'log_conversion';
    	$where = 'log_conversion.idvisit = ?';
    	$bind = array(1);
    	
    	$segment = 'visitConvertedGoalId!=2;customVariablePageName1==Test;visitConvertedGoalId==1';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				*
			FROM
				".Piwik_Common::prefixTable('log_conversion')." AS log_conversion
				LEFT JOIN ".Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action ON log_conversion.idlink_va = log_link_visit_action.idlink_va
			WHERE
				( log_conversion.idvisit = ? )
				AND
				( log_conversion.idgoal <> ? AND log_link_visit_action.custom_var_k1 = ? AND log_conversion.idgoal = ? )",
    		"bind" => array(1, 2, 'Test', 1));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }

    public function test_getSelectQuery_joinConversionOnVisit()
    {
    	$select = 'log_visit.*';
    	$from = 'log_visit';
    	$where = 'log_visit.idvisit = ?';
    	$bind = array(1);
    	
    	$segment = 'visitConvertedGoalId==1';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				log_inner.*
			FROM
				(
			SELECT
				log_visit.*
			FROM
				".Piwik_Common::prefixTable('log_visit')." AS log_visit
				LEFT JOIN ".Piwik_Common::prefixTable('log_conversion')." AS log_conversion ON log_conversion.idvisit = log_visit.idvisit
			WHERE
				( log_visit.idvisit = ? )
				AND
				( log_conversion.idgoal = ? )
			GROUP BY log_visit.idvisit
				) AS log_inner",
    		"bind" => array(1, 1));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }
    public function test_getSelectQuery_conversionOnly()
    {
    	$select = 'log_conversion.*';
    	$from = 'log_conversion';
    	$where = 'log_conversion.idvisit = ?';
    	$bind = array(1);
    	
    	$segment = 'visitConvertedGoalId==1';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				log_conversion.*
			FROM
				".Piwik_Common::prefixTable('log_conversion')." AS log_conversion
			WHERE
				( log_conversion.idvisit = ? )
				AND
				( log_conversion.idgoal = ? )",
    		"bind" => array(1, 1));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }
    
    public function test_getSelectQuery_joinVisitOnConversion()
    {
    	$select = '*';
    	$from = 'log_conversion';
    	$where = 'log_conversion.idvisit = ?';
    	$bind = array(1);
    	
    	$segment = 'visitConvertedGoalId==1,visitServerHour==12';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				*
			FROM
				".Piwik_Common::prefixTable('log_conversion')." AS log_conversion
				LEFT JOIN ".Piwik_Common::prefixTable('log_visit')." AS log_visit ON log_conversion.idvisit = log_visit.idvisit
			WHERE
				( log_conversion.idvisit = ? )
				AND
				( (log_conversion.idgoal = ? OR HOUR(log_visit.visit_last_action_time) = ? ))",
    		"bind" => array(1, 1, 12));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }
    
    // visit is joined on action, then conversion is joined
    // make sure that conversion is joined on action not visit
    public function test_getSelectQuery_joinVisitAndConversionOnAction()
    {
    	$select = '*';
    	$from = 'log_link_visit_action';
    	$where = false;
    	$bind = array();
    	
    	$segment = 'visitServerHour==12;visitConvertedGoalId==1';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				*
			FROM
				".Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action
				LEFT JOIN ".Piwik_Common::prefixTable('log_visit')." AS log_visit ON log_visit.idvisit = log_link_visit_action.idvisit
				LEFT JOIN ".Piwik_Common::prefixTable('log_conversion')." AS log_conversion ON log_conversion.idlink_va = log_link_visit_action.idlink_va AND log_conversion.idsite = log_link_visit_action.idsite
			WHERE
				 HOUR(log_visit.visit_last_action_time) = ? AND log_conversion.idgoal = ? ",
    		"bind" => array(12, 1));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }
    
    // join conversion on visit, then actions
    // make sure actions are joined before conversions
    public function test_getSelectQuery_joinConversionAndActionOnVisit()
    {
    	$select = 'log_visit.*';
    	$from = 'log_visit';
    	$where = false;
    	$bind = array();
    	
    	$segment = 'visitConvertedGoalId==1;visitServerHour==12;customVariablePageName1==Test';
    	$segment = new Piwik_Segment($segment, $idSites = array());
    	
    	$query = $segment->getSelectQuery($select, $from, $where, $bind);
    	
    	$expected = array(
    		"sql" => "
			SELECT
				log_inner.*
			FROM
				(
			SELECT
				log_visit.*
			FROM
				".Piwik_Common::prefixTable('log_visit')." AS log_visit
				LEFT JOIN ".Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action ON log_link_visit_action.idvisit = log_visit.idvisit
				LEFT JOIN ".Piwik_Common::prefixTable('log_conversion')." AS log_conversion ON log_conversion.idlink_va = log_link_visit_action.idlink_va AND log_conversion.idsite = log_link_visit_action.idsite
			WHERE
				 log_conversion.idgoal = ? AND HOUR(log_visit.visit_last_action_time) = ? AND log_link_visit_action.custom_var_k1 = ? 
			GROUP BY log_visit.idvisit
				) AS log_inner",
    		"bind" => array(1, 12, 'Test'));
    	
    	$this->assertEqual($query, $expected, var_export($query, true));
    }
    
    public function test_bogusSegment_ThrowsException()
    {
        $tests = array(
            'referrerType==not',
            'someRandomSegment==not',
            'A=B'
        );
        
        foreach($tests as $segment)
        {
            try {
                $segment = new Piwik_Segment($segment, $idSites = array());
                $sql = $segment->getSelectQuery();
                $this->fail();
            } catch(Exception $e) {
//                var_dump($e->getMessage());
                $this->pass();
            }
        }
    }
}
