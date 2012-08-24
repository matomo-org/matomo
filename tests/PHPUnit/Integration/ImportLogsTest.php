<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: $
 */

/**
 * Tests the log importer.
 */
class Test_Piwik_Integration_ImportLogs extends IntegrationTestCase
{
	protected static $dateTime = '2010-03-06 11:22:33';
	protected static $idSite = 1;
	protected static $idGoal = null;
	protected static $tokenAuth = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
		    self::$tokenAuth = self::getTokenAuth();
            
            self::setUpWebsitesAndGoals();
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        ImportLogs
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
		return array(
			array('all', array('idSite'  => self::$idSite,
							   'date'    => '2012-08-09',
							   'periods' => 'month')),
		);
    }
    
    /**
     * @group        Integration
     * @group        ImportLogs
     */
    public function testDynamicResolverSitesCreated()
    {
    	self::logVisitsWithDynamicResolver(self::$tokenAuth);
    	
    	// reload access so new sites are viewable
    	Zend_Registry::get('access')->setSuperUser(true);
    	
    	// make sure sites aren't created twice
    	$piwikDotNet = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://piwik.net');
    	$this->assertEquals(1, count($piwikDotNet));
    	
    	$anothersiteDotCom = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://anothersite.com');
    	$this->assertEquals(1, count($anothersiteDotCom));
    	
    	$whateverDotCom = Piwik_SitesManager_API::getInstance()->getSitesIdFromSiteUrl('http://whatever.com');
    	$this->assertEquals(1, count($whateverDotCom));
	}

	public function getOutputPrefix()
	{
		return 'ImportLogs';
	}

    public static function setUpWebsitesAndGoals()
    {
		// for conversion testing
        self::createWebsite(self::$dateTime);
		self::$idGoal = Piwik_Goals_API::getInstance()->addGoal(
			self::$idSite, 'all', 'url', 'http', 'contains', false, 5);
    }
    
    protected static function trackVisits()
    {
    	self::logVisitsWithStaticResolver(self::$tokenAuth);
    	self::logVisitsWithAllEnabled(self::$tokenAuth);
    }

	/**
	 * Logs a couple visits for Aug 9, Aug 10, Aug 11 of 2012, for site we create.
	 */
	protected static function logVisitsWithStaticResolver( $token_auth )
    {
    	$logFile = PIWIK_INCLUDE_PATH.'/tests/resources/fake_logs.log'; # log file
    	
    	$opts = array('--idsite' => self::$idSite,
    				  '--token-auth' => $token_auth,
    				  '--recorders' => '4',
    				  '--recorder-max-payload-size' => '2');
		
		self::executeLogImporter($logFile, $opts);
	}
	
	/**
	 * Logs a couple visits for the site we created and two new sites that do not
	 * exist yet. Visits are from Aug 12, 13 & 14 of 2012.
	 */
	protected static function logVisitsWithDynamicResolver( $token_auth )
	{
		$logFile = PIWIK_INCLUDE_PATH.'/tests/resources/fake_logs_dynamic.log'; # log file
		
		$opts = array('--add-sites-new-hosts' => false,
					  '--token-auth' => $token_auth,
    				  '--recorders' => '4',
    				  '--recorder-max-payload-size' => '1');
		
		self::executeLogImporter($logFile, $opts);
	}
	
	/**
	 * Logs a couple visits for the site we created w/ all log importer options
	 * enabled. Visits are for Aug 11 of 2012.
	 */
	protected static function logVisitsWithAllEnabled( $token_auth )
	{
		$logFile = PIWIK_INCLUDE_PATH.'/tests/resources/fake_logs_enable_all.log';
		
		$opts = array('--idsite' => self::$idSite,
					  '--token-auth' => $token_auth,
					  '--recorders' => '4',
					  '--recorder-max-payload-size' => '2',
					  '--enable-static' => false,
					  '--enable-bots' => false,
					  '--enable-http-errors' => false,
					  '--enable-http-redirects' => false,
					  '--enable-reverse-dns' => false);
		
		self::executeLogImporter($logFile, $opts);
	}
	
	protected static function executeLogImporter( $logFile, $options )
	{
		$python = Piwik_Common::isWindows() ? "C:\Python27\python.exe" : 'python';
		
		// create the command
		$cmd = $python
			 . ' "'.PIWIK_INCLUDE_PATH.'/misc/log-analytics/import_logs.py" ' # script loc
		  // . '-ddd ' // debug
			 . '--url="'.self::getRootUrl().'tests/PHPUnit/proxy/" ' # proxy so that piwik uses test config files
			 ;
		
		foreach ($options as $name => $value)
		{
			$cmd .= $name;
			if ($value !== false)
			{
				$cmd .= '="'.$value.'"';
			}
			$cmd .= ' ';
		}
		
		$cmd .= '"'.$logFile.'" 2>&1';
		
		// run the command
		exec($cmd, $output, $result);
		if ($result !== 0)
		{
			throw new Exception("log importer failed: ".implode("\n", $output));
		}
		
		return $output;
    }
}
