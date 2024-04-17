<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitorInterest\tests\Unit\Columns;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\VisitorInterest\Columns\VisitorSecondsSinceLast;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

class VisitorSecondsSinceLastTest extends TestCase
{
    public function test_onNewVisit_returnsZeroIfVisitorIsUnknown()
    {
        $dim = new VisitorSecondsSinceLast();

        $value = $dim->onNewVisit($this->makeMockRequest(), $this->makeMockVisitor($isKnown = false), null);
        $this->assertEquals(0, $value);
    }

    public function test_onNewVisit_returnsZeroIfPreviousVisitorLastActionTimeIsZero()
    {
        $dim = new VisitorSecondsSinceLast();

        $value = $dim->onNewVisit($this->makeMockRequest(), $this->makeMockVisitor($isKnown = true), null);
        $this->assertEquals(0, $value);
    }

    public function test_onNewVisit_returnsTimeInBetweenIfKnownVisit()
    {
        $dim = new VisitorSecondsSinceLast();

        $currentTime = time();
        $lastTime = $currentTime - 100;

        $value = $dim->onNewVisit($this->makeMockRequest($currentTime), $this->makeMockVisitor($isKnown = true, [], ['visit_first_action_time' => $lastTime]), null);
        $this->assertEquals(100, $value);
    }

    /**
     * @return Request
     */
    private function makeMockRequest($currentTime = null)
    {
        $mockBuilder = $this->getMockBuilder(Request::class)->disableOriginalConstructor();
        if ($currentTime) {
            $mockBuilder->onlyMethods(['getCurrentTimestamp']);
        }
        $mock = $mockBuilder->getMock();
        if ($currentTime) {
            $mock->method('getCurrentTimestamp')->willReturn($currentTime);
        }

        /** @var Request $mock */
        $result = $mock;
        return $result;
    }

    private function makeMockVisitor($isKnown, $visitProps = [], $previousVisitProps = [])
    {
        $visitor = new Visitor(new VisitProperties($visitProps), $isKnown, new VisitProperties($previousVisitProps));
        return $visitor;
    }
}
