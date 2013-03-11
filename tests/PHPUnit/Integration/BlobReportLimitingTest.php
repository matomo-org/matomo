<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/MockLocationProvider.php';

/**
 * Test Piwik's report limiting code. Make sure the datatable_archiving_maximum_rows_...
 * config options limit the size of certain reports when archiving.
 */
class Test_Piwik_Integration_BlobReportLimitingTest extends IntegrationTestCase
{
	public static $fixture = null; // initialized below class definition
	
	public static function setUpBeforeClass()
	{
		self::setUpConfigOptions();
		parent::_setUpBeforeClass($dbName = false, $createEmptyDatabase = true, $createConfig = false);
		parent::setUpFixture(self::$fixture);
	}

	public function getApiForTesting()
	{
		// TODO: test Provider plugin? Not sure if it's possible.
		$apiToCall = array(
			'Actions.getPageUrls', 'Actions.getPageTitles', 'Actions.getDownloads', 'Actions.getOutlinks',
			'CustomVariables.getCustomVariables',
			'Referers.getRefererType', 'Referers.getKeywords', 'Referers.getSearchEngines',
			'Referers.getWebsites', 'Referers.getAll', /* TODO 'Referers.getCampaigns', */
			'UserSettings.getResolution', 'UserSettings.getConfiguration', 'UserSettings.getOS',
			'UserSettings.getBrowserVersion',
			'UserCountry.getRegion', 'UserCountry.getCity',
		);
		
		return array(
			array($apiToCall, array('idSite' 	=> self::$fixture->idSite,
									'date'		=> self::$fixture->dateTime,
									'periods'	=> array('day')))
		);
	}
	
	/**
	 * @dataProvider getApiForTesting
	 * @group		Integration
	 * @group		BlobReportLimiting
	 */
	public function testApi($api, $params)
	{
		$this->runApiTests($api, $params);
	}
	
	/**
	 * @group		Integration
	 * @group		BlobReportLimiting
	 */
	public function testApiWithRankingQuery()
	{
		// custom setup
		self::deleteArchiveTables();
		$generalConfig['datatable_archiving_maximum_rows_referers'] = 4;
		$generalConfig['datatable_archiving_maximum_rows_subtable_referers'] = 4;
		$generalConfig['datatable_archiving_maximum_rows_actions'] = 4;
		$generalConfig['datatable_archiving_maximum_rows_subtable_actions'] = 4;
		$generalConfig['datatable_archiving_maximum_rows_standard'] = 4;
		Piwik_Config::getInstance()->General['archiving_ranking_query_row_limit'] = 3;
		Piwik_Actions_ArchivingHelper::reloadConfig();
		
		foreach ($this->getApiForTesting() as $pair)
		{
			list($apiToCall, $params) = $pair;
			$params['testSuffix'] = '_rankingQuery';
			
			$this->runApiTests($apiToCall, $params);
		}
	}

	public function getOutputPrefix()
	{
		return 'reportLimiting';
	}
	
	protected static function setUpConfigOptions()
	{
		self::createTestConfig();
		$generalConfig =& Piwik_Config::getInstance()->General;
		$generalConfig['datatable_archiving_maximum_rows_referers'] = 3;
		$generalConfig['datatable_archiving_maximum_rows_subtable_referers'] = 2;
		$generalConfig['datatable_archiving_maximum_rows_actions'] = 3;
		$generalConfig['datatable_archiving_maximum_rows_subtable_actions'] = 2;
		$generalConfig['datatable_archiving_maximum_rows_standard'] = 3;
		$generalConfig['archiving_ranking_query_row_limit'] = 50000;
	}
}

Test_Piwik_Integration_BlobReportLimitingTest::$fixture = new Test_Piwik_Fixture_ManyVisitsWithMockLocationProvider();

