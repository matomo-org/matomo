<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AIAgents\tests\Integration;

use MatomoTracker;
use Piwik\Date;
use Piwik\Plugins\AIAgents\Providers\ChatGPT as ChatGPTAgent;
use Piwik\Plugins\Live\API as LiveAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group AIAgents
 * @group Plugins
 */
class ForceNewVisitTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private static $testNow;

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var Date
     */
    private $testDate;

    /**
     * @var MatomoTracker
     */
    private $tracker;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$testNow = strtotime('2025-07-18 12:00:00');
    }

    public static function provideContainerConfigBeforeClass()
    {
        return [
            'Tests.now' => self::$testNow,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createSuperUser();

        $this->testDate = Date::factory(self::$testNow)->subHour(6);

        $this->idSite  = Fixture::createWebsite($this->testDate->subDay(1)->getDatetime());
        $this->tracker = Fixture::getTracker($this->idSite, $this->testDate->getDatetime());
    }

    public function testTrackingWithChangedAgentCausesNewVisits(): void
    {
        $this->trackAgentPageView();
        $this->assertVisits(1, 1);
        $this->moveTimeForward(0.05);
        $this->trackAgentPageView();
        $this->assertVisits(1, 2);

        $this->moveTimeForward(0.1);
        $this->trackHumanPageView();
        $this->assertVisits(2, 3);
        $this->moveTimeForward(0.15);
        $this->trackHumanPageView();
        $this->assertVisits(2, 4);

        $this->moveTimeForward(0.2);
        $this->trackAgentPageView();
        $this->assertVisits(3, 5);
        $this->moveTimeForward(0.25);
        $this->trackAgentPageView();
        $this->assertVisits(3, 6);
    }

    private function assertVisits(int $visitsExpected, int $actionsExpected): void
    {
        $counters = LiveAPI::getInstance()->getCounters(
            $this->idSite,
            2880,
            false,
            ['visits', 'actions']
        );

        self::assertEquals($visitsExpected, $counters[0]['visits']);
        self::assertEquals($actionsExpected, $counters[0]['actions']);
    }

    private function moveTimeForward(float $hoursForward): void
    {
        $this->tracker->setForceVisitDateTime(
            $this->testDate->addHour($hoursForward)->getDatetime()
        );
    }

    private function trackAgentPageView(): void
    {
        $this->tracker->setCustomTrackingParameter(ChatGPTAgent::PARAM_SIGNATURE, 'value irrelevant');
        $this->tracker->setCustomTrackingParameter(ChatGPTAgent::PARAM_SIGNATURE_AGENT, '"https://chatgpt.com"');
        $this->tracker->setCustomTrackingParameter(ChatGPTAgent::PARAM_SIGNATURE_INPUT, 'value irrelevant');

        $this->tracker->setUrl('http://www.example.org/agent');

        Fixture::checkResponse(
            $this->tracker->doTrackPageView('Agent - ' . $this->testDate->getDatetime())
        );
    }

    private function trackHumanPageView(): void
    {
        $this->tracker->setUrl('http://www.example.org/human');

        Fixture::checkResponse(
            $this->tracker->doTrackPageView('Human - ' . $this->testDate->getDatetime())
        );
    }
}
