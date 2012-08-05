<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

require_once dirname(__FILE__) . '/TwoVisitsWithCustomVariablesTest.php';

/**
 * test Metadata API + period=range&date=lastN
 */
class Test_Piwik_Integration_PeriodIsRange_DateIsLastN_MetadataAndNormalAPI extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
    public static function setUpBeforeClass()
    {
        IntegrationTestCase::setUpBeforeClass();
        self::$visitorId = substr(md5(uniqid()), 0, 16);
        self::$dateTime = Piwik_Date::factory('now')->getDateTime();
        try {
            self::setUpWebsitesAndGoals();
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        };
    }

    public function setUp()
    {
        if (date('G') == 23 || date('G') == 22) {
            $this->markTestSkipped("SKIPPED since it fails around midnight...");
        }

        parent::setUp();
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        PeriodIsRange_DateIsLastN_MetadataAndNormalAPI
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array(
            'API.getProcessedReport',
            'Actions.getPageUrls',
            'Goals.get',
            'CustomVariables.getCustomVariables',
            'Referers.getCampaigns',
            'Referers.getKeywords',
            'VisitsSummary.get',
            'Live');

        $segments = array(
            false,
            'daysSinceFirstVisit!=50',
            'visitorId!=33c31e01394bdc63',
            // testing both filter on Actions table and visit table
            'visitorId!=33c31e01394bdc63;daysSinceFirstVisit!=50',
            //'pageUrl!=http://unknown/not/viewed',
        );
        $dates    = array(
            'last7',
            Piwik_Date::factory('now')->subDay(6)->toString() . ',today',
            Piwik_Date::factory('now')->subDay(6)->toString() . ',now',
        );

        $result = array();
        foreach ($segments as $segment) {
            foreach ($dates as $date) {
                $result[] = array($apiToCall, array('idSite'    => self::$idSite, 'date' => $date,
                                                    'periods'   => array('range'), 'segment' => $segment,
                                                    // testing getLastVisitsForVisitor requires a visitor ID
                                                    'visitorId' => self::$visitorId));
            }
        }

        return $result;
    }

    public function getOutputPrefix()
    {
        return 'periodIsRange_dateIsLastN_MetadataAndNormalAPI';
    }
}

