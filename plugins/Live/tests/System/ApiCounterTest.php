<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\System;

use Piwik\Date;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\Live\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Live
 * @group ApiTest
 * @group Api
 * @group Plugins
 */
class ApiCounterTest extends SystemTestCase
{
    /**
     * @var int
     */
    private static $testNow;

    /**
     * @var API
     */
    private $api;
    private $idSite = 1;

    public static function setUpBeforeClass(): void
    {
        self::$testNow = strtotime('2018-02-03 04:45:40');

        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();
        $this->setSuperUser();
        $this->createSite();
    }

    public function test_GetCounters_ShouldFail_IfUserHasNoPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasViewAccess Fake exception');

        $this->setAnonymous();
        $this->api->getCounters($this->idSite, 5);
    }

    public function test_GetCounters_ShouldReturnZeroForAllCounters_IfThereAreNoVisitsEtc()
    {
        $counters = $this->api->getCounters($this->idSite, 5);

        $this->assertEquals($this->buildCounter(0, 0, 0, 0), $counters);
    }

    public function test_GetCounters_ShouldOnlyReturnResultsOfLastMinutes()
    {
        $this->trackSomeVisits();

        $counters = $this->api->getCounters($this->idSite, 5);
        $this->assertEquals($this->buildCounter(19, 32, 16, 16), $counters);

        $counters = $this->api->getCounters($this->idSite, 20);
        $this->assertEquals($this->buildCounter(24, 60, 20, 40), $counters);
    }

    /**
     * @dataProvider getInvalidLastMinutesValues
     */
    public function testGetCounterShouldThrowExceptionIfLastMinutesInvalid($lastMinutes)
    {
        self::expectException(\InvalidArgumentException::class);
        $this->api->getCounters($this->idSite, $lastMinutes);
    }

    public function getInvalidLastMinutesValues()
    {
        return [
            [-5], [0], [3000]
        ];
    }

    public function test_GetCounters_ShouldHideAllColumnsIfRequested()
    {
        $exampleCounter = $this->buildCounter(0, 0, 0, 0);
        $counters = $this->api->getCounters($this->idSite, 5, false, array(), array_keys($exampleCounter[0]));
        $this->assertEquals(array(array()), $counters);
    }

    public function test_GetCounters_ShouldHideSomeColumnsIfRequested()
    {
        $counters = $this->api->getCounters($this->idSite, 20, false, array(), array('visitsConverted', 'visitors'));
        $this->assertEquals(array(array('visits' => 24, 'actions' => 60)), $counters);
    }

    public function test_GetCounters_ShouldShowAllColumnsIfRequested()
    {
        $counter = $this->buildCounter(24, 60, 20, 40);
        $counters = $this->api->getCounters($this->idSite, 20, false, array_keys($counter[0]));
        $this->assertEquals($counter, $counters);
    }

    public function test_GetCounters_ShouldShowSomeColumnsIfRequested()
    {
        $counters = $this->api->getCounters($this->idSite, 20, false, array('visits', 'actions'));
        $this->assertEquals(array(array('visits' => 24, 'actions' => 60)), $counters);
    }

    public function test_GetCounters_ShouldHideColumnIfGivenInShowAndHide()
    {
        $counters = $this->api->getCounters($this->idSite, 20, false, array('visits', 'actions'), array('actions'));
        $this->assertEquals(array(array('visits' => 24)), $counters);
    }

    private function trackSomeVisits()
    {
        $nowTimestamp = self::$testNow;

        // use local tracker so mock location provider can be used
        $t = Fixture::getTracker($this->idSite, $nowTimestamp, $defaultInit = true, $useLocal = false);
        $t->enableBulkTracking();

        for ($i = 0; $i != 20; ++$i) {
            $t->setForceNewVisit();
            $t->setVisitorId( substr(md5($i * 1000), 0, $t::LENGTH_VISITOR_ID));

            $factor = 10;
            if ($i > 15) {
                $factor = 30; // make sure first 15 visits are always within 5 minutes to prevent any random fails
            }
            $time = $nowTimestamp - ($i * $factor);

            // first visit -> this one is > 5 minutes and should be ignored in one test
            $date = Date::factory($time - 600);
            $t->setForceVisitDateTime($date->getDatetime());
            $t->setUrl("http://piwik.net/space/quest/iv");
            $t->doTrackPageView("Space Quest XV");

            $t->doTrackGoal(1); // this one is > 5 minutes and should be ignored in one test

            // second visit
            $date = Date::factory($time - 1);
            $t->setForceVisitDateTime($date->getDatetime());
            $t->setUrl("http://piwik.net/space/quest/iv");
            $t->doTrackPageView("Space Quest XII");

            if ($i % 6 == 0) {
                $t->setForceNewVisit(); // to test visitors vs visits
            }

            // third visit
            $date = Date::factory($time);
            $t->setForceVisitDateTime($date->getDatetime());
            $t->setUrl("http://piwik.net/grue/$i");
            $t->doTrackPageView('It is pitch black...');

            $t->doTrackGoal(2);
        }

        Fixture::checkBulkTrackingResponse($t->doBulkTrack());
    }

    private function buildCounter($visits, $actions, $visitors, $visitsConverted)
    {
        return array(array(
            'visits'   => $visits,
            'actions'  => $actions,
            'visitors' => $visitors,
            'visitsConverted' => $visitsConverted,
        ));
    }

    private function createSite()
    {
        Fixture::createWebsite('2013-01-23 01:23:45');
        GoalsApi::getInstance()->addGoal(1, 'MyName', 'manually', '', 'contains');
        GoalsApi::getInstance()->addGoal(1, 'MyGoal', 'manually', '', 'contains');
    }

    private function setSuperUser()
    {
        FakeAccess::$superUser = true;
    }

    private function setAnonymous()
    {
        FakeAccess::clearAccess();
    }

    public static function provideContainerConfigBeforeClass()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
            'Tests.now' => self::$testNow,
        );
    }
}
