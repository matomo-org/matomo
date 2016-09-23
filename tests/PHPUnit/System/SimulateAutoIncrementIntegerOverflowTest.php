<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;

/**
 * Simulates the case where there are more than 2 billion visits and pages, and check that Piwik
 * can handle this use case without hitting MySQL INT overflow.
 *
 * This system test will compare against the OneVisitorTwoVisitsWithCookieSupportTest system test
 * (OneVisitorTwoVisits_withCookieSupport* expected api responses)
 *
 * @group SimulateAutoIncrementIntegerOverflowTest
 * @group Core
 */
class SimulateAutoIncrementIntegerOverflowTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);

        $this->checkAutoIncrementIdsAreMoreThanFourBillion();

    }

    private function checkAutoIncrementIdsAreMoreThanFourBillion()
    {
        $fieldsThatShouldNotOverflow = array(
            'log_visit' => 'idvisit',
            'log_action' => 'idaction',
            'log_link_visit_action' => 'idlink_va'
        );
        foreach($fieldsThatShouldNotOverflow as $table => $autoIncrementField) {
            $table = Common::prefixTable($table);
            $value = Db::fetchOne("SELECT MAX($autoIncrementField) FROM $table ");
            $this->assertGreaterThan(4294967295, $value, 'in ' . $table);
        }
    }

    public function getApiForTesting()
    {
        $apiToCall = array(
            'VisitTime', 'VisitsSummary', 'VisitorInterest', 'VisitFrequency', 'DevicesDetection',
            'UserCountry', 'Referrers', 'Actions',
            'Provider', 'Goals', 'CustomVariables', 'CoreAdminHome', 'DevicePlugins',
        );

        return array(
            array($apiToCall, array(
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'compareAgainst' => 'OneVisitorTwoVisits_withCookieSupport',
            ))
        );
    }
}

SimulateAutoIncrementIntegerOverflowTest::$fixture = new OneVisitorTwoVisits();
SimulateAutoIncrementIntegerOverflowTest::$fixture->useThirdPartyCookies = true;
SimulateAutoIncrementIntegerOverflowTest::$fixture->useSiteSearch = true;
SimulateAutoIncrementIntegerOverflowTest::$fixture->simulateIntegerOverflow = true;