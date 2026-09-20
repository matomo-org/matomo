<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration\Tracker;

use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;
use Piwik\Plugins\TrackingSpamPrevention\Configuration;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Plugins\TrackingSpamPrevention\Tracker\RequestProcessor;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * @group TrackingSpamPrevention
 * @group BlockedIpRangesTest
 * @group Plugins
 */
class RequestProcessorTest extends IntegrationTestCase
{
    /** @var RequestProcessor */
    private $processor;

    private $settings;
    private $ranges;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2020-12-12 00:00:00');
        Fixture::createSuperUser();
        $this->settings = new SystemSettings();

        $ranges = [
            new BlockedIpRanges\VariableRange([
                '10.10.0.0/21',
            ]),
        ];

        $this->ranges = new BlockedIpRanges($ranges, new Configuration());
        $this->processor = new RequestProcessor($this->settings, $this->ranges);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testUpdateBlockedIpRangesMaxActionsDisabledShouldNeverBlock()
    {
        $this->settings->max_actions->setValue(0);
        $this->assertFalse($this->processor->afterRequestProcessed($this->makeVisit(100000), $this->makeRequest()));
        $this->assertSame([], $this->ranges->getBlockedRanges());
    }

    public function testUpdateBlockedIpRangesMaxActionsEnabledLimitNotReachedShouldNotBanIpAndNotStopTracking()
    {
        $this->settings->max_actions->setValue(200);

        $this->assertFalse($this->processor->afterRequestProcessed($this->makeVisit(199), $this->makeRequest()));
        $this->assertSame([], $this->ranges->getBlockedRanges());
    }

    public function testUpdateBlockedIpRangesMaxActionsEnabledLimitReachedShouldStopRequestAndBanIP()
    {
        $this->settings->max_actions->setValue(200);

        $this->assertTrue($this->processor->afterRequestProcessed($this->makeVisit(200), $this->makeRequest()));
        $this->assertSame(['11.' => ['11.12.13.14/32']], $this->ranges->getBlockedRanges());
    }

    public function testUpdateBlockedIpRangesMaxActionsEnabledLimitReachedShouldIgnoreAllowedIp()
    {
        $this->settings->ipAllowList->setValue(['10.12.13.14/32', 'f::f/52', '', '11.12.13.14/21', '12.14.15.16', 'f::f']);
        $this->settings->max_actions->setValue(200);

        $this->assertFalse($this->processor->afterRequestProcessed($this->makeVisit(200), $this->makeRequest()));
        $this->assertSame([], $this->ranges->getBlockedRanges());
    }

    public function testUpdateBlockedIpRangesMaxActionsEnabledLimitReachedShouldIgnoreAllowedIpHigherActions()
    {
        $this->settings->ipAllowList->setValue(['10.12.13.14/32', 'f::f/52', '', '11.12.13.14/21', '12.14.15.16', 'f::f']);
        $this->settings->max_actions->setValue(200);

        $this->assertFalse($this->processor->afterRequestProcessed($this->makeVisit(800), $this->makeRequest()));
        $this->assertSame([], $this->ranges->getBlockedRanges());
    }

    private function makeVisit($actions)
    {
        $visit = new VisitProperties();
        $visit->setProperty('visit_total_actions', $actions);
        return $visit;
    }

    private function makeRequest()
    {
        $req = new Request(array('idsite' => 1, 'cip' => '11.12.13.14', 'token_auth' => Fixture::getTokenAuth()));
        $req->isAuthenticated();
        return $req;
    }
}
