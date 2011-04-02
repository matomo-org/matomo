<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_Piwik_Segment extends UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        Piwik::createConfigObject();
		// Load and install plugins
    	$pluginsManager = Piwik_PluginsManager::getInstance();
    	$pluginsManager->loadPlugins( Zend_Registry::get('config')->Plugins->Plugins->toArray() );
    	
    }
    
    public function test_()
    {
        $tests = array(
            // Normal segment
        	'country==France' => array('sql' => ' location_country = ? ', 'bind' => array('France')),
        
            // unescape the comma please
            'country==a\,==' => array('sql' => ' location_country = ? ', 'bind' => array('a,==')), 

            // AND, with 2 values rewrites
            'country==a;visitorType!=returning;visitorType==new' => 
                        array(
                        	'sql' => ' location_country = ? AND visitor_returning <> ? AND visitor_returning = ? ', 
                        	'bind' => array('a', '1', '0')), 
            
            // OR, with 2 value rewrites
            'referrerType==search,referrerType==direct' => 
                        array(
                        'sql'=>' (referer_type = ? OR referer_type = ? )', 
                        'bind' => array(    Piwik_Common::REFERER_TYPE_SEARCH_ENGINE, 
                                  			Piwik_Common::REFERER_TYPE_DIRECT_ENTRY
            )),
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
