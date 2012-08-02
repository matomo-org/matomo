<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
/**
 * Tests that filter_truncate works recursively in Page URLs report AND in the case there are 2 different data Keywords -> search engine
 */
class Test_Piwik_Integration_OneVisitor_LongUrlsTruncated extends IntegrationTestCase
{
    protected static $dateTime = '2010-03-06 01:22:33';
    protected static $idSite   = 1;

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
     * @group        OneVisitor_LongUrlsTruncated
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array('Referers.getKeywords', 'Actions.getPageUrls');

        return array(
            array($apiToCall, array('idSite'                 => self::$idSite,
                                    'date'                   => self::$dateTime,
                                    'language'               => 'fr',
                                    'otherRequestParameters' => array('expanded' => 1, 'filter_truncate' => 2)))
        );
    }

    public function getOutputPrefix()
    {
        return 'OneVisitor_LongUrlsTruncated';
    }

    protected static function setUpWebsitesAndGoals()
    {
        self::createWebsite(self::$dateTime);
    }

    protected static function trackVisits()
    {
        // tests run in UTC, the Tracker in UTC
        $dateTime = self::$dateTime;
        $idSite   = self::$idSite;

        // Visit 1: keyword and few URLs
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true, $useThirdPartyCookie = 1);
        $t->setUrlReferrer('http://bing.com/search?q=Hello world');

        // Generate a few page views that will be truncated
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page2');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page3');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page3');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page4');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page4');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page4');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/page.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/page.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/page.htm');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/contact.htm');
        self::checkResponse($t->doTrackPageView('Hello'));

        // VISIT 2 = Another keyword
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrlReferrer('http://www.google.com.vn/url?q=Salut');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 3 = Another keyword
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2)->getDatetime());
        $t->setUrlReferrer('http://www.google.com.vn/url?q=Kia Ora');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 4 = Kia Ora again
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(3)->getDatetime());
        $t->setUrlReferrer('http://www.google.com.vn/url?q=Kia Ora');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 5 = Another search engine
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(4)->getDatetime());
        $t->setUrlReferrer('http://nz.search.yahoo.com/search?p=Kia Ora');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 6 = Another search engine
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(5)->getDatetime());
        $t->setUrlReferrer('http://images.search.yahoo.com/search/images;_ylt=A2KcWcNKJzF?p=Kia%20Ora%20');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Visit 7 = Another search engine
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(6)->getDatetime());
        $t->setUrlReferrer('http://nz.bing.com/images/search?q=+++Kia+ora+++');
        self::checkResponse($t->doTrackPageView('incredible title!'));
    }
}

