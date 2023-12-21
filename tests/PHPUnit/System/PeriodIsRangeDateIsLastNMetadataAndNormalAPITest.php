<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Date;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoVisitsWithCustomVariables;

/**
 * test Metadata API + period=range&date=lastN
 *
 * @group PeriodIsRangeDateIsLastNMetadataAndNormalAPITest
 * @group Core
 */
class PeriodIsRangeDateIsLastNMetadataAndNormalAPITest extends SystemTestCase
{
    public static $fixture = null;

    public static function setUpBeforeClass(): void
    {
        self::$fixture->dateTime = Date::factory('yesterday')->getDateTime();
        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        // test results change based on time of day for some reason
        Date::$now = strtotime(date('Y-m-d') . ' 20:00:00');
        parent::setUp();
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
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
            $testSuffix = '';
            if (!empty($segment) && false !== strpos($segment, 'pageUrl')) {
                $testSuffix .= '_pagesegment';
            }

            foreach ($dates as $date) {
                $result[] = array($apiToCall, array('idSite'    => $idSite, 'date' => $date,
                                                    'periods'   => array('range'), 'segment' => $segment,
                                                    'testSuffix' => $testSuffix,
                                                    'otherRequestParameters' => array(
                                                        'lastMinutes' => 60 * 24 * 2,
                                                        'visitorId' => $visitorId,
                                                        'hideColumns' => 'serverDate,lastActionTimestamp,lastActionDateTime,serverTimestamp,' .
                                                                         'firstActionTimestamp,serverTimePretty,serverDatePretty,' .
                                                                         'serverDatePrettyFirstAction,serverTimePrettyFirstAction'
                                                    )));
            }
        }

        return $result;
    }

    public static function getOutputPrefix()
    {
        return 'periodIsRange_dateIsLastN_MetadataAndNormalAPI';
    }
}

PeriodIsRangeDateIsLastNMetadataAndNormalAPITest::$fixture = new TwoVisitsWithCustomVariables();
PeriodIsRangeDateIsLastNMetadataAndNormalAPITest::$fixture->doExtraQuoteTests = false;
