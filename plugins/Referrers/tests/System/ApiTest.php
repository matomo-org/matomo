<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\tests\System;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Tests\Fixtures\TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Referrers
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $api = array(
            'API.getProcessedReport'
        );

        $apiToTest   = array();

        // we make sure it returns a subtableIds even if a DataTable\Map is requested
        $apiToTest[] = array($api,
            array(
                'idSite'     => 1,
                'apiModule'  => 'Referrers',
                'apiAction'  => 'getReferrerType',
                'date'       => '2010-01-01,2010-03-10',
                'periods'    => array('day'),
                'testSuffix' => 'Referrers_getReferrerType',
                'otherRequestParameters' => array('expanded' => 0)
            )
        );

        return $apiToTest;
    }

    public function test_forceNewVisit_shouldNotForceANewVisitWhenNoKeywordIsSetAndNoReferrerWasSetInitially()
    {
        $dateTime = '2015-01-02';
        $idSite = self::$fixture->idSite;

        $t = Fixture::getTracker($idSite, $dateTime . ' 00:01:02', $defaultInit = true);
        // track a campaign that was opened directly (no referrer)
        $t->setUrlReferrer('');
        $t->setUrl('http://piwik.net/?pk_campaign=adwbuccc');
        $t->doTrackPageView('My Title');

        // navigate to next page on same page
        $t->setUrlReferrer('http://piwik.net/?pk_campaign=adwbuccc');
        $t->setCustomTrackingParameter('_rcn', 'adwbuccc'); // this parameter would be set by piwik.js from cookie / attributionInfo
        $t->setCustomTrackingParameter('_rck', ''); // no keyword was used in previous tracking request
        $t->setUrl('http://piwik.net/page1');
        $t->doTrackPageView('Page 1');

        /** @var DataTable $visits */
        $visits = Request::processRequest('VisitsSummary.get', array('idSite' => 1, 'period' => 'day', 'date' => $dateTime));

        $this->assertEquals(1, $visits->getFirstRow()->getColumn('nb_visits'));
        $this->assertEquals(2, $visits->getFirstRow()->getColumn('nb_actions'));
    }

    public function test_forceNewVisit_shouldNotForceANewVisitWhenNoKeywordIsSetAndReferrerHostChanges()
    {
        $dateTime = '2015-01-03';
        $idSite = self::$fixture->idSite;

        $t = Fixture::getTracker($idSite, $dateTime . ' 00:01:02', $defaultInit = true);
        // track a campaign that was opened directly (no referrer)
        $t->setUrlReferrer('http://www.google.com');
        $t->setUrl('http://piwik.net/?pk_campaign=adwbuccc');
        $t->doTrackPageView('My Title');

        // navigate to next page on same page
        $t->setUrlReferrer('http://piwik.net/?pk_campaign=adwbuccc');
        $t->setCustomTrackingParameter('_rcn', 'adwbuccc'); // this parameter would be set by piwik.js from cookie / attributionInfo
        $t->setCustomTrackingParameter('_rck', ''); // no keyword was used in previous tracking request
        $t->setUrl('http://piwik.net/page1');
        $t->doTrackPageView('Page 1');

        /** @var DataTable $visits */
        $visits = Request::processRequest('VisitsSummary.get', array('idSite' => 1, 'period' => 'day', 'date' => $dateTime));

        $this->assertEquals(1, $visits->getFirstRow()->getColumn('nb_visits'));
        $this->assertEquals(2, $visits->getFirstRow()->getColumn('nb_actions'));
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

}

ApiTest::$fixture = new TwoSitesManyVisitsOverSeveralDaysWithSearchEngineReferrers();