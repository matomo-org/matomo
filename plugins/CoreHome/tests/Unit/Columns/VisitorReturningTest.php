<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreHome\tests\Unit\Columns;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreHome\Columns\VisitorReturning;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

class VisitorReturningTest extends TestCase
{
    public function test_onNewVisit_returnsReturningCustomerIfVisitHasOrder()
    {
        $dim = new VisitorReturning();
        $result = $dim->onNewVisit($this->makeMockRequest([ 'ec_id' => 'abcdefg' ]), $this->makeMockVisitor(true), null);
        $this->assertEquals(VisitorReturning::IS_RETURNING_CUSTOMER, $result);
    }

    public function test_onNewVisit_returnsReturningCustomerIfVisitHasSecondsSinceLastOrder()
    {
        $dim = new VisitorReturning();
        $result = $dim->onNewVisit($this->makeMockRequest(), $this->makeMockVisitor(true, [ 'visitor_seconds_since_order' => 1234 ]), null);
        $this->assertEquals(VisitorReturning::IS_RETURNING_CUSTOMER, $result);
    }

    public function test_onNewVisit_returnsReturningCustomerIfPrevVisitHasSecondsSinceLastOrder()
    {
        $dim = new VisitorReturning();
        $result = $dim->onNewVisit($this->makeMockRequest(), $this->makeMockVisitor(true, [], [ 'visitor_seconds_since_order' => 5678 ]), null);
        $this->assertEquals(VisitorReturning::IS_RETURNING_CUSTOMER, $result);
    }

    public function test_onNewVisit_returnsReturningVisitorIfVisitorIsKnown()
    {
        $dim = new VisitorReturning();
        $result = $dim->onNewVisit($this->makeMockRequest(), $this->makeMockVisitor(true), null);
        $this->assertEquals(VisitorReturning::IS_RETURNING, $result);
    }

    public function test_onNewVisit_returnsNewVisitIfVisitorIsNotKnown()
    {
        $dim = new VisitorReturning();
        $result = $dim->onNewVisit($this->makeMockRequest(), $this->makeMockVisitor(false), null);
        $this->assertEquals(VisitorReturning::IS_NEW, $result);
    }

    /**
     * @return Request
     */
    private function makeMockRequest($params = [], $currentTime = null)
    {
        $mockBuilder = $this->getMockBuilder(Request::class)->disableOriginalConstructor();
        if ($currentTime) {
            $mockBuilder->onlyMethods(['getCurrentTimestamp']);
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
