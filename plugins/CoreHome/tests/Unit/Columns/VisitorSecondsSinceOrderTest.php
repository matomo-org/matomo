<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Unit\Columns;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreHome\Columns\VisitorSecondsSinceOrder;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

class VisitorSecondsSinceOrderTest extends TestCase
{
    public function test_onExistingVisit_returnsZeroIfTheVisitHasAnOrder()
    {
        $dim = new VisitorSecondsSinceOrder();
        $result = $dim->onExistingVisit($this->makeMockRequest([ 'ec_id' => 'abljasdf' ]), $this->makeMockVisitor(true), null);
        $this->assertEquals(0, $result);
    }

    public function test_onExistingVisit_returnsTheExistingValueIfThereIsOne()
    {
        $dim = new VisitorSecondsSinceOrder();
        $result = $dim->onExistingVisit($this->makeMockRequest(), $this->makeMockVisitor(true, [ 'visitor_seconds_since_order' => 20 ]), null);
        $this->assertEquals(20, $result);
    }

    public function test_onExistingVisit_returnsNullIfDimensionValueIsNotSetForPreviousVisit()
    {
        $dim = new VisitorSecondsSinceOrder();
        $result = $dim->onExistingVisit($this->makeMockRequest(), $this->makeMockVisitor(true), null);
        $this->assertEquals(null, $result);
    }

    public function test_onExistingVisit_returnsCorrectValueIfPreviousValueWasForOrder()
    {
        $dim = new VisitorSecondsSinceOrder();
        $result = $dim->onExistingVisit($this->makeMockRequest([], '2020-03-04 03:04:28'), $this->makeMockVisitor(true, [], [ 'visitor_seconds_since_order' => 0, 'visit_first_action_time' => '2020-03-04 03:04:20' ]), null);
        $this->assertEquals(8, $result);
    }

    public function test_onExistingVisit_returnsCorrectValueIfPreviousValueWasAfterOrder()
    {
        $dim = new VisitorSecondsSinceOrder();
        $result = $dim->onExistingVisit($this->makeMockRequest([], '2020-03-04 03:04:28'), $this->makeMockVisitor(true, [], [ 'visitor_seconds_since_order' => 45, 'visit_first_action_time' => '2020-03-04 03:04:20' ]), null);
        $this->assertEquals(53, $result);
    }

    /**
     * @return Request
     */
    private function makeMockRequest($params = [], $currentTime = null)
    {
        if (is_string($currentTime)) {
            $currentTime = strtotime($currentTime);
        }

        $mockBuilder = $this->getMockBuilder(Request::class)->disableOriginalConstructor();
        if ($currentTime) {
            $mockBuilder->onlyMethods(['getCurrentTimestamp', 'getParam']);
        }
        $mock = $mockBuilder->getMock();
        if ($currentTime) {
            $mock->method('getCurrentTimestamp')->willReturn($currentTime);
        }
        $mock->method('getParam')->willReturnCallback(function ($name) use ($params) {
            return isset($params[$name]) ? $params[$name] : false;
        });

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
