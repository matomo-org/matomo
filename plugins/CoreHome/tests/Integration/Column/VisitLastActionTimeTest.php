<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration\Column;

use Piwik\Cache;
use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics;
use Piwik\Plugins\CoreHome\Columns\UserId;
use Piwik\Plugins\CoreHome\Columns\VisitLastActionTime;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\DataTable;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\VisitorRecognizer;

/**
 * @group CoreHome
 * @group VisitLastActionTimeTest
 * @group Plugins
 * @group Column
 */
class VisitLastActionTimeTest extends IntegrationTestCase
{
    /**
     * @var VisitLastActionTime
     */
    private $lastAction;

    public function setUp()
    {
        parent::setUp();
        $this->lastAction = new VisitLastActionTime();
    }

    public function tearDown()
    {
        parent::tearDown();
    }


    private function makeRequest($request)
    {
        $request['idsite'] = 1;

        return new Request($request);
    }

    private function getVisitor()
    {
        $visit = new VisitProperties();
        $visit->setProperty('idvisit', '321');
        $visit->setProperty('idvisitor', Common::hex2bin('1234567890234567'));
        $visitor = new Visitor($visit, $isKnown = false);

        return $visitor;
    }

    public function test_onExistingVisit_whenPing()
    {
        $request = $this->makeRequest(array('ping' => 1));
        $visitor = $this->getVisitor();
        $this->assertFalse($this->lastAction->onExistingVisit($request, $visitor, $action = null));
    }

    public function test_onExistingVisit_whenNewVisitReturnsTimeFromRequest()
    {
        $now = time() - 5; // -5 so we make sure this time is used and not actually now
        $request = $this->makeRequest(array('cdt' => $now));
        $this->assertEquals($now, $request->getCurrentTimestamp());

        $visitor = $this->getVisitor();

        $expected = Date::factory($now)->getDatetime();
        $this->assertSame($expected, $this->lastAction->onExistingVisit($request, $visitor, $action = null));
    }

    public function test_onExistingVisit_whenKnownVisitRequestTimeIsNewer()
    {
        $now = time() - 5; // -5 so we make sure this time is used and not actually now
        $previousTime = $now - 10; // is older
        $request = $this->makeRequest(array('cdt' => $now));
        $this->assertEquals($now, $request->getCurrentTimestamp());

        $visitor = $this->getVisitor();
        $visitor->setVisitorColumn(VisitorRecognizer::KEY_ORIGINAL_VISIT_ROW,
            array('visit_last_action_time' => Date::factory($previousTime)->getDatetime())
        );

        $expected = Date::factory($now)->getDatetime();
        $this->assertSame($expected, $this->lastAction->onExistingVisit($request, $visitor, $action = null));
    }

    public function test_onExistingVisit_whenKnownVisitAndPreviousVisitTimeIsNewer()
    {
        $now = time() - 5; // -5 so we make sure this time is used and not actually now
        $previousTime = $now + 10; // is newer
        $request = $this->makeRequest(array('cdt' => $now));
        $this->assertEquals($now, $request->getCurrentTimestamp());

        $visitor = $this->getVisitor();
        $visitor->setVisitorColumn(VisitorRecognizer::KEY_ORIGINAL_VISIT_ROW,
            array('visit_last_action_time' => Date::factory($previousTime)->getDatetime())
        );

        $expected = Date::factory($previousTime)->getDatetime();
        // should keep existing visit last action time
        $this->assertSame($expected, $this->lastAction->onExistingVisit($request, $visitor, $action = null));
    }
}
