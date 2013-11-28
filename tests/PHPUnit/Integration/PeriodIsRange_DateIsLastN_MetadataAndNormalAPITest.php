<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;

/**
 * test Metadata API + period=range&date=lastN
 */
class Test_Piwik_Integration_PeriodIsRange_DateIsLastN_MetadataAndNormalAPI extends IntegrationTestCase
{
    public static $fixture = null;

    static $shouldSkipTestThisTime = false;

    public static function setUpBeforeClass()
    {
        self::$shouldSkipTestThisTime = in_array(date('G'), array(22, 23));

        if (self::$shouldSkipTestThisTime) {
            print("\nSKIPPED test PeriodIsRange_DateIsLastN_MetadataAndNormalAPI since it fails around midnight...\n");
            return;
        }

        self::$fixture->dateTime = Date::factory('now')->getDateTime();
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        if (self::$shouldSkipTestThisTime) {
            return;
        }

        parent::tearDownAfterClass();
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        if (self::$shouldSkipTestThisTime) {
            return;
        }
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $visitorId = self::$fixture->visitorId;

        $apiToCall = array(
            'API.getProcessedReport',
            'Actions.getPageUrls',
            'Goals.get',
            'CustomVariables.getCustomVariables',
            'Referrers.getCampaigns',
            'Referrers.getKeywords',
            'VisitsSummary.get',
            'Live');

        $segments = array(
            false,
            'daysSinceFirstVisit!=50',
            'visitorId!=33c31e01394bdc63',
            'visitorId!=33c31e01394bdc63;daysSinceFirstVisit!=50',
            // testing segment on Actions table
            'pageUrl!=http://unknown/not/viewed',
        );
        $dates = array(
            'last7',
            Date::factory('now')->subDay(6)->toString() . ',today',
            Date::factory('now')->subDay(6)->toString() . ',now',
        );

        $result = array();
        foreach ($segments as $segment) {
            foreach ($dates as $date) {
                $result[] = array($apiToCall, array('idSite'    => $idSite, 'date' => $date,
                                                    'periods'   => array('range'), 'segment' => $segment,
                    // testing getLastVisitsForVisitor requires a visitor ID
                                                    'visitorId' => $visitorId));
            }
        }

        return $result;
    }

    public static function getOutputPrefix()
    {
        return 'periodIsRange_dateIsLastN_MetadataAndNormalAPI';
    }
}

Test_Piwik_Integration_PeriodIsRange_DateIsLastN_MetadataAndNormalAPI::$fixture =
    new Test_Piwik_Fixture_TwoVisitsWithCustomVariables();
Test_Piwik_Integration_PeriodIsRange_DateIsLastN_MetadataAndNormalAPI::$fixture->doExtraQuoteTests = false;

