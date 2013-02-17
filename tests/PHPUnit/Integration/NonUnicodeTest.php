<?php
/**
 * Piwik - Open source web analytics
 *
 * @link	http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Tests that visits track & reports display correctly when non-unicode text is
 * used in URL query params of visits.
 */
class Test_Piwik_Integration_NonUnicodeTest extends IntegrationTestCase
{
	protected static $idSite1 = 1;
	protected static $dateTime = '2010-01-03 11:22:33';
	
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		try {
			self::setUpWebsites();
			self::trackVisits();
		} catch(Exception $e) {
			// Skip whole test suite if an error occurs while setup
			throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
		}
	}

	/**
	 * @dataProvider getApiForTesting
	 * @group		Integration
	 * @group		NonUnicodeTest
	 */
	public function testApi($api, $params)
	{
		$this->runApiTests($api, $params);
	}
	
	public function getApiForTesting()
	{
		$apiToCall = array(
			'Actions.getSiteSearchKeywords',
			'Actions.getPageTitles',
			'Actions.getPageUrls',
			'Referers.getWebsites',
			'Live.getLastVisitsDetails',
		);
		
		return array(
			array($apiToCall, array('idSite'	=> self::$idSite1,
									'date'		=> self::$dateTime,
									'periods'	=> 'day'))
		);
	}

	public function getOutputPrefix()
	{
		return 'NonUnicode';
	}

	/**
	 * One site with custom search parameters,
	 * One site using default search parameters,
	 * One site with disabled site search
	 */
	protected static function setUpWebsites()
	{
		Piwik_SitesManager_API::getInstance()->setGlobalSearchParameters($searchKeywordParameters='gkwd', $searchCategoryParameters='gcat');
		self::$idSite1 = self::createWebsite(Piwik_Date::factory(self::$dateTime)->getDatetime(), 0, "Site 1 - Site search", $siteurl=false, $search=1, $searchKwd='q,mykwd,p', $searchCat='cats' );
	}

	protected static function trackVisits()
	{
		self::assertTrue(function_exists('mb_check_encoding'), ' check mb_check_encoding ');
		self::assertTrue(function_exists('mb_convert_encoding'), ' check mb_convert_encoding ');

		// Visitor site1
		$visitor = self::getTracker(self::$idSite1, self::$dateTime, $defaultInit = true);
		
		// Test w/ iso-8859-15
		$visitor->setForceVisitDateTime(Piwik_Date::factory(self::$dateTime)->addHour(0.3)->getDatetime());
		$visitor->setUrlReferrer('http://anothersite.com/whatever.html?whatever=Ato%FC');
		// Also testing that the value is encoded when passed as an array
		$visitor->setUrl('http://example.org/index.htm?random=param&mykwd[]=Search 2%FC&test&cats= Search Kategory &search_count=INCORRECT!');
		$visitor->setPageCharset('iso-8859-15');
		self::checkResponse($visitor->doTrackPageView('Site Search results'));
		$visitor->setPageCharset('');
		
		// Test w/ windows-1251
		$visitor = self::getTracker(self::$idSite1, self::$dateTime, $defaultInit = true);
		$visitor->setForceVisitDateTime(Piwik_Date::factory(self::$dateTime)->addHour(0.3)->getDatetime());
		$visitor->setUrlReferrer('http://anothersite.com/whatever.html?txt=%EC%E5%F8%EA%EE%E2%FB%E5');
		$visitor->setUrl('http://example.org/page/index.htm?whatever=%EC%E5%F8%EA%EE%E2%FB%E5');
		$visitor->setPageCharset('windows-1251');
		self::checkResponse($visitor->doTrackPageView('Page title is always UTF-8'));

		$visitor->setForceVisitDateTime(Piwik_Date::factory(self::$dateTime)->addHour(0.4)->getDatetime());
		$nonUnicodeKeyword = '%EC%E5%F8%EA%EE%E2%FB%E5';
		$visitor->setUrl('http://example.org/page/index.htm?q='.$nonUnicodeKeyword);
		$visitor->setPageCharset('windows-1251');
		self::checkResponse($visitor->doTrackPageView('Site Search'));


		// Test URL with non unicode Site Search keyword
		$visitor->setForceVisitDateTime(Piwik_Date::factory(self::$dateTime)->addHour(0.5)->getDatetime());
		//TESTS: on jenkins somehow the "<-was here" was cut off so removing this test case and simply append the wrong keyword
//		$visitor->setUrl('http://example.org/page/index.htm?q=non unicode keyword %EC%E5%F8%EAe%EE%E2%FBf%E5 <-was here');
		$visitor->setUrl('http://example.org/page/index.htm?q=non unicode keyword %EC%E5%F8%EA%EE%E2%FB%E5');
		$visitor->setPageCharset('utf-8');
//		var_dump("hello \n");
//		var_dump($visitor->getUrlTrackPageView('Site Search'));
		self::checkResponse($visitor->doTrackPageView('Site Search'));


		$visitor->setPageCharset('');
		$visitor->setForceVisitDateTime(Piwik_Date::factory(self::$dateTime)->addHour(0.5)->getDatetime());
		$visitor->setUrl('http://example.org/exit-page');
		self::checkResponse($visitor->doTrackPageView('Page title is always UTF-8'));

		// Test set invalid page char set
		$visitor = self::getTracker(self::$idSite1, self::$dateTime, $defaultInit = true);
		$visitor->setForceVisitDateTime(Piwik_Date::factory(self::$dateTime)->addHour(1)->getDatetime());
		$visitor->setUrlReferrer('http://anothersite.com/whatever.html');
		$visitor->setUrl('http://example.org/index.htm?random=param&mykwd=a+keyword&test&cats= Search Kategory &search_count=INCORRECT!');
		$visitor->setPageCharset('GTF-42'); // galactic transformation format
		self::checkResponse($visitor->doTrackPageView('Site Search results'));
		$visitor->setPageCharset('');
	}
}

