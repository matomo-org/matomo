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
                                             'testSuffix'   => '_IndexedByDate'))
        );
    }

    public function getOutputPrefix()
    {
        return 'oneVisitor_oneWebsite_severalDays_DateRange';
    }

    protected function setUpWebsitesAndGoals()
    {
        $this->createWebsite(self::$dateTimes[0]);
    }

    protected function trackVisits()
    {
        $dateTimes = self::$dateTimes;
        $idSite    = self::$idSite;

        $i = 0;
        foreach ($dateTimes as $dateTime) {
            $i++;
            $visitor = $this->getTracker($idSite, $dateTime, $defaultInit = true);
            // Fake the visit count cookie
            $visitor->setDebugStringAppend("&_idvc=$i");

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
            $visitor->setUrl('http://example.org/homepage');
            $this->checkResponse($visitor->doTrackPageView('ou pas'));

            // Test change the IP, the visit should not be split but recorded to the same idvisitor
            $visitor->setIp('200.1.15.22');

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            $this->checkResponse($visitor->doTrackPageView('ou pas'));

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            $this->checkResponse($visitor->doTrackPageView('ou pas'));
        }
    }
}

