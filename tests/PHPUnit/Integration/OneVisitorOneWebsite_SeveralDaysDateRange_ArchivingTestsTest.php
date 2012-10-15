<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * Tests some API using range periods & makes sure the correct amount of blob/numeric
 * archives are created.
 */
class Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange_ArchivingTests extends IntegrationTestCase
{
    protected static $dateTimes = array(
        '2010-12-14 01:00:00',
        '2010-12-15 01:00:00',
        '2010-12-25 01:00:00',
        '2011-01-15 01:00:00',
        '2011-01-16 01:00:00',
    );
    protected static $idSite = 1;

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
        foreach ($dateTimes as $dateTime) {
            $i++;
            $visitor = self::getTracker($idSite, $dateTime, $defaultInit = true);
            // Fake the visit count cookie
            $visitor->setDebugStringAppend("&_idvc=$i");

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
            $visitor->setUrl('http://example.org/homepage');
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            // Test change the IP, the visit should not be split but recorded to the same idvisitor
            $visitor->setIp('200.1.15.22');

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            self::checkResponse($visitor->doTrackPageView('ou pas'));
        }
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        OneVisitorOneWebsite_SeveralDaysDateRange_ArchivingTests
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array('Actions.getPageUrls',
            'VisitsSummary.get',
            'UserSettings.getResolution',
            'VisitFrequency.get',
            'VisitTime.getVisitInformationPerServerTime');

        // 2 segments: ALL and another way of expressing ALL but triggering the Segment code path
        $segments = array(
            false,
            'country!=aa',
            'pageUrl!=ThisIsNotKnownPageUrl',
        );

        // Running twice just as health check that second call also works
        $result = array();
        for ($i = 0; $i <= 1; $i++) {
            foreach ($segments as $segment) {
                $result[] = array($apiToCall, array('idSite'  => self::$idSite, 'date' => '2010-12-15,2011-01-15',
                                                    'periods' => array('range'), 'segment' => $segment));
            }
        }

        return $result;
    }

    /**
     * @depends      testApi
     * @group        Integration
     * @group        OneVisitorOneWebsite_SeveralDaysDateRange_ArchivingTests
     */
    public function testCheck()
    {
        // Check that requesting period "Range" means
        // only processing the requested Plugin blob (Actions in this case), not all Plugins blobs
        $tests = array(
            // 4 blobs for the Actions plugin, 7 blobs for UserSettings, 2 blobs VisitTime
            'archive_blob_2010_12'    => (4 + 7 + 2) * 3,
            // (VisitsSummary 5 metrics + 1 flag - no Unique visitors for range)
            // + 1 flag archive UserSettings
            // + (Actions 1 flag + 2 metrics - pageviews, unique pageviews + X??? metrics Site Search)
            // + (Frequency 5 metrics + 1 flag)
            // + 1 flag VisitTime
            // * 3 segments
            'archive_numeric_2010_12' => (6 + 1 + 3 + 6 + 1) * 3,

            // all "Range" records are in December
            'archive_blob_2011_01'    => 0,
            'archive_numeric_2011_01' => 0,
        );
        foreach ($tests as $table => $expectedRows) {
            $sql        = "SELECT count(*) FROM " . Piwik_Common::prefixTable($table) . " WHERE period = " . Piwik::$idPeriods['range'];
            $countBlobs = Zend_Registry::get('db')->fetchOne($sql);
            $this->assertEquals($expectedRows, $countBlobs, "$table expected $expectedRows, got $countBlobs");
        }
    }

}
