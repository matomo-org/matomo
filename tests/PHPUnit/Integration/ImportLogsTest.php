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

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
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

	/**
	 * Logs a couple visits for Aug 9, Aug 10, Aug 11 of 2012.
	 */
    protected static function trackVisits()
    {
		$cmd = "python "
			 . PIWIK_INCLUDE_PATH.'/misc/log-analytics/import_logs.py ' # script loc
			 . '--url="'.self::getRootUrl().'" '
			 . '--tracker-url="'.self::getTrackerUrl().'" '
			 . '--idsite='.self::$idSite.' '
			 . '--recorders=4 '
			 . '--enable-http-errors '
			 . '--enable-http-redirects '
			 . '--enable-static '
			 . '--enable-bots '
			 . PIWIK_INCLUDE_PATH.'/tests/resources/fake_logs.log ' # log file
			 . '2>&1'
			 ;
		
		exec($cmd, $output, $result);
		if ($result !== 0)
		{
			throw new Exception("log importer failed: ".implode("\n", $output));
		}
    }
}
