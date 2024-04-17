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
use Piwik\Plugins\CoreHome\Columns\VisitorSecondsSinceFirst;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

class VisitorSecondsSinceFirstTest extends TestCase
{
    public function test_onNewVisit_returnsZeroIfVisitorIsUnknown()
    {
        $request = $this->makeMockRequest();
        $visitor = $this->makeMockVisitor(false);

        $dim = new VisitorSecondsSinceFirst();
        $result = $dim->onNewVisit($request, $visitor, null);

        $this->assertEquals(0, $result);
    }

    /**
     * @dataProvider getInvalidTestDataForOnNewVisit
     */
    public function test_onNewVisit_returnsNullIfPreviousColumnValueOrCurrentColumnValueIsInvalid($currentProps, $oldProps)
    {
        $request = $this->makeMockRequest();
        $visitor = $this->makeMockVisitor(true, $currentProps, $oldProps);

        $dim = new VisitorSecondsSinceFirst();
        $result = $dim->onNewVisit($request, $visitor, null);

        $this->assertNull($result);
    }

    public function getInvalidTestDataForOnNewVisit()
    {
        return [
            [
                ['visit_first_action_time' => ''],
                ['visit_first_action_time' => '2016-02-03 00:00:00', 'visitor_seconds_since_first' => '250'],
            ],

            [
                ['visit_first_action_time' => 0],
                ['visit_first_action_time' => '2016-02-03 00:00:00', 'visitor_seconds_since_first' => '250'],
            ],

            [
                ['visit_first_action_time' => null],
                ['visit_first_action_time' => '2016-02-03 00:00:00', 'visitor_seconds_since_first' => '250'],
            ],

            [
                ['visit_first_action_time' => 'asdlfj alskdjf'],
                ['visit_first_action_time' => '2016-02-03 00:00:00', 'visitor_seconds_since_first' => '250'],
            ],

            [
                ['visit_first_action_time' => '2016-02-03 00:00:00'],
                ['visit_first_action_time' => '', 'visitor_seconds_since_first' => '250'],
            ],

            [
                ['visit_first_action_time' => '2016-02-03 00:00:00'],
                ['visit_first_action_time' => 0, 'visitor_seconds_since_first' => '250'],
            ],

            [
                ['visit_first_action_time' => '2016-02-03 00:00:00'],
                ['visit_first_action_time' => null, 'visitor_seconds_since_first' => '250'],
            ],

            [
                ['visit_first_action_time' => '2016-02-03 00:00:00'],
                ['visit_first_action_time' => 'asdlfj alskdjf', 'visitor_seconds_since_first' => '250'],
            ],

            [
                ['visit_first_action_time' => '2016-02-03 00:00:00'],
                ['visit_first_action_time' => '2016-04-05 00:00:00', 'visitor_seconds_since_first' => ''],
            ],
            [
                ['visit_first_action_time' => '2016-02-03 00:00:00'],
                ['visit_first_action_time' => '2016-04-05 00:00:00', 'visitor_seconds_since_first' => null],
            ],
            [
                ['visit_first_action_time' => '2016-02-03 00:00:00'],
                ['visit_first_action_time' => '2016-04-05 00:00:00', 'visitor_seconds_since_first' => false],
            ],
        ];
    }

    public function test_onNewVisit_returnsCorrectValueWhenVisitorIsKnownAndAllFirstActionTimesPresent()
    {
        $currentTime = time();

        $request = $this->makeMockRequest($currentTime);
        $visitor = $this->makeMockVisitor(true, ['visit_first_action_time' => $currentTime - 200], ['visit_first_action_time' => $currentTime - 300, 'visitor_seconds_since_first' => 500]);

        $dim = new VisitorSecondsSinceFirst();
        $result = $dim->onNewVisit($request, $visitor, null);

        $this->assertEquals($result, 600);
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
