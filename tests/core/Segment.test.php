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
		Zend_Registry::get('config')->setTestEnvironment();	

		// setup the access layer (required in Segment contrustor testing if anonymous is allowed to use segments)
		$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
		
		// Load and install plugins
    	$pluginsManager = Piwik_PluginsManager::getInstance();
    	$pluginsManager->loadPlugins( Zend_Registry::get('config')->Plugins->Plugins->toArray() );
    }
    
    public function test_common()
    {
        $tests = array(
            // Normal segment
        	'country==France' => array('sql' => ' location_country = ? ', 'bind' => array('France'), 'sql_join_visits' => ''),
        
            // unescape the comma please
            'country==a\,==' => array('sql' => ' location_country = ? ', 'bind' => array('a,=='), 'sql_join_visits' => ''), 

            // AND, with 2 values rewrites
            'country==a;visitorType!=returning;visitorType==new' => 
                        array(
                        	'sql' => ' location_country = ? AND visitor_returning <> ? AND visitor_returning = ? ', 
                        	'bind' => array('a', '1', '0'),
                        	'sql_join_visits' => ''), 
            
            // OR, with 2 value rewrites
            'referrerType==search,referrerType==direct' => 
                        array(
                        'sql'=>' (referer_type = ? OR referer_type = ? )', 
                        'bind' => array(    Piwik_Common::REFERER_TYPE_SEARCH_ENGINE, 
                                  			Piwik_Common::REFERER_TYPE_DIRECT_ENTRY
            ), 
            			'sql_join_visits' => ''),
        );
        
        foreach($tests as $segment => $expected)
        {
            $segment = new Piwik_Segment($segment, $idSites = array());
            $sql = $segment->getSql();
            $this->assertEqual($sql, $expected, var_export($sql, true));

            // calling twice should give same results
            $sql = $segment->getSql();
            $this->assertEqual($sql, $expected, var_export($sql, true));
            
            $this->assertEqual(strlen($segment->getHash()), 32);
        }
    }
    
    public function test_withJoin()
    {
        $segment = 'country==France;visitorType==new';
        $expected = array(
        		'sql' => ' log_test.location_country = ? AND visitor_returning = ? ', 
        		'bind' => array('France', 0), 
        		'sql_join_visits' => 'LEFT JOIN piwiktests_log_visit AS log_visit USING(idvisit)'); 
        
        $segment = new Piwik_Segment($segment, $idSites = array());
        $sql = $segment->getSql(array('location_country'), 'log_test');
		$tables_prefix = Zend_Registry::get('config')->database->tables_prefix;
        $sql['sql_join_visits'] = str_replace("LEFT JOIN $tables_prefix", 'LEFT JOIN piwiktests_', $sql['sql_join_visits']);
//        var_dump($sql);
//        var_dump($expected);
        $this->assertEqual($sql, $expected, var_export($sql, true));
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
                $sql = $segment->getSql();
                $this->fail();
            } catch(Exception $e) {
//                var_dump($e->getMessage());
                $this->pass();
            }
        }
    }
}
