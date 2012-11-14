<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * testing period=range use case. Recording data before and after, checking that the requested range is processed correctly
 */
class Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange extends IntegrationTestCase
{
    protected static $dateTimes = array(
        '2010-12-14 01:00:00',
        '2010-12-15 01:00:00',
        '2010-12-25 01:00:00',
        '2011-01-15 01:00:00',
        '2011-01-16 01:00:00',
    );
    protected static $idSite = 1;
    
    // one per visit
    protected static $referrerUrls = array(
    	'http://facebook.com/whatever',
    	'http://www.facebook.com/another/path',
    	'http://fb.me/?q=sdlfjs&n=slfjsd',
    	'http://twitter.com/whatever2',
    	'http://www.twitter.com/index?a=2334',
    	'http://t.co/id/?y=dsfs',
    	'http://www.flickr.com',
    	'http://xanga.com',
    	'http://skyrock.com',
    	'http://mixi.jp',
    );

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
     * @group        OneVisitorOneWebsite_SeveralDaysDateRange
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        return array(
            // range test
            array('MultiSites.getAll', array('idSite'  => self::$idSite,
                                             'date'    => '2010-12-15,2011-01-15',
                                             'periods' => array('range'))),

            // test several dates (tests use of IndexedByDate w/ 'date1,date2,etc.')
            array('MultiSites.getAll', array('idSite'       => self::$idSite,
                                             'date'         => '2010-12-10',
                                             'periods'      => array('day'),
                                             'setDateLastN' => true,
                                             'testSuffix'   => '_IndexedByDate')),
			
			// test socials
			array('Referers.getSocials', array('idSite'  => 'all',
											   'date'    => '2010-12-13,2011-01-18',
											   'periods' => array('range'))),
			
			array('Referers.getSocials', array('idSite'		  => 'all',
											   'date'		  => '2010-12-10',
											   'periods'	  => array('day'),
											   'setDateLastN' => true,
											   'testSuffix'   => '_IndexedByDate')),
			
			array('Referers.getUrlsForSocial', array('idSite'		=> 'all', // test w/o idSubtable
													 'date'			=> '2010-12-13,2011-01-18',
													 'periods'		=> 'range',
													 'testSuffix'	=> '_noIdSubtable')),
			
			array('Referers.getUrlsForSocial', array('idSite'   	 => self::$idSite, // test w/ idSubtable
													 'date'			 => '2010-12-13,2011-01-18',
													 'periods'		 => 'range',
													 'supertableApi' => 'Referers.getSocials')),
        );
    }

    public function getOutputPrefix()
    {
        return 'oneVisitor_oneWebsite_severalDays_DateRange';
    }

    protected static function setUpWebsitesAndGoals()
    {
        self::createWebsite(self::$dateTimes[0]);
    }

    protected static function trackVisits()
    {
        $dateTimes = self::$dateTimes;
        $idSite    = self::$idSite;

        $i = 0;
        $ridx = 0;
        foreach ($dateTimes as $dateTime) {
            $i++;
            $visitor = self::getTracker($idSite, $dateTime, $defaultInit = true);
            // Fake the visit count cookie
            $visitor->setDebugStringAppend("&_idvc=$i");

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
            $visitor->setUrl('http://example.org/homepage');
            $visitor->setUrlReferrer(self::$referrerUrls[$ridx++]);
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            // Test change the IP, the visit should not be split but recorded to the same idvisitor
            $visitor->setIp('200.1.15.22');

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            $visitor->setUrlReferrer(self::$referrerUrls[$ridx++]);
            self::checkResponse($visitor->doTrackPageView('ou pas'));
        }
    }
}

