<?php
/**
 * Piwik - Open source web analytics
 *
 * @link	http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/MockLocationProvider.php';

/**
 * Tests w/ 14 visitors w/ 2 visits each.
 * Uses geoip location provider to test city/region reports.
 * 
 * TODO Test ServerBased GeoIP implementation somehow. (Use X-FORWARDED-FOR?)
 * TODO Test PECL implementation somehow. (The PECL module must point to the test dir, not the real one.)
 */
class Test_Piwik_Integration_ManyVisitorsOneWebsiteTest extends IntegrationTestCase
{
	public static $fixture = null; // initialized below class definition
	
	/**
	 * @dataProvider getApiForTesting
	 * @group        Integration
	 * @group        TwoVisitors_TwoWebsites_DifferentDays_ArchivingDisabled
	 */
	public function testApi($api, $params)
	{
		$this->runApiTests($api, $params);
	}

	public function getApiForTesting()
	{
		$idSite = self::$fixture->idSite;
		$dateTime = self::$fixture->dateTime;
		
		// Note: we must set  'UserCountry.getLocationFromIP' since it's "excluded" by default in setApiNotToCall
		$apiToCall = array('UserCountry');

		return array(
			array( $apiToCall,
							array(  'idSite'		=> $idSite,
									'date'		=> $dateTime,
									'periods'	=> array('month'))),

			array($apiToCall, array('idSite'		=> $idSite,
									'date'		=> $dateTime,
									'periods'	=> array('month'),
									'testSuffix' => '_segment_region',
									'segment'    => 'region==P3;country==gb')),

			array($apiToCall, array('idSite'		=> $idSite,
									'date'		=> $dateTime,
									'periods'	=> array('month'),
									'testSuffix' => '_segment_city',
									'segment'    => 'city==Stratford-upon-Avon;region==P3;country==gb')),

			array($apiToCall, array('idSite'		=> $idSite,
									'date'		=> $dateTime,
									'periods'	=> array('month'),
									'testSuffix' => '_segment_lat_long',
									'segment'    => 'lat>45;lat<49.3;long>-125;long<-122')),

			array('UserCountry.getCountry', array('idSite'		=> $idSite,
												  'date'		=> $dateTime,
												  'periods'		=> array('month'),
												  'testSuffix'	=> '_segment_continent',
												  'segment'   	=> 'continent==eur')),

			array(array('UserCountry.getLocationFromIP', 'Live.getLastVisitsDetails'), array(
														'idSite'		=> $idSite,
														 'date'		=> $dateTime,
														 'periods'		=> array('month'),
														 'otherRequestParameters' => array('ip' => '194.57.91.215')
													 	)),
		);
	}
}

Test_Piwik_Integration_ManyVisitorsOneWebsiteTest::$fixture
	= new Test_Piwik_Fixture_ManyVisitsWithGeoIP();

