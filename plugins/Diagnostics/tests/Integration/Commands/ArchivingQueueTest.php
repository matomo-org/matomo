<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\tests\Integration\Commands;

use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;
use Piwik\Date;

/**
 * @group ArchivingQueueTest
 */
class ArchivingQueueTest extends ConsoleCommandTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Date::$now = strtotime('2010-03-07 01:00:00');

        // make sure archiving is initiated so there is data in the archive tables
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01', 'browserCode==FF');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01', 'daysSinceFirstVisit==2');

        Date::$now = strtotime('2010-03-07 06:00:00');
    }

    public function test_CommandOutput_IsAsExpected()
    {
        $expected = <<<OUTPUT
+--------------+---------+------+--------+-------------------------+---------------------+---------------+---------+------------+--------+
| Invalidation | Segment | Site | Period | Date                    | Time Queued         | Waiting       | Started | Processing | Status |
+--------------+---------+------+--------+-------------------------+---------------------+---------------+---------+------------+--------+
| 1            |         | 1    | Day    | 2010-03-06              | 2010-03-07 01:00:00 | 5 hours 0 min |         |            | Queued |
| 2            |         | 1    | Week   | 2010-03-01 - 2010-03-07 | 2010-03-07 01:00:00 | 5 hours 0 min |         |            | Queued |
| 3            |         | 1    | Month  | 2010-03                 | 2010-03-07 01:00:00 | 5 hours 0 min |         |            | Queued |
| 4            |         | 1    | Year   | 2010                    | 2010-03-07 01:00:00 | 5 hours 0 min |         |            | Queued |
+--------------+---------+------+--------+-------------------------+---------------------+---------------+---------+------------+--------+
OUTPUT;

        $this->applicationTester->run([
            'command' => 'diagnostics:archiving-queue'
        ]);
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }

    public function test_CommandOutput_withJsonOption_IsAsExpected()
    {
        $expected = '[{"Invalidation":"1","Segment":null,"Site":"1","Period":"Day","Date":"2010-03-06","TimeQueued":"2010-03-07 01:00:00","Waiting":"18000","Started":null,"Processing":"","Status":"Queued"},{"Invalidation":"2","Segment":null,"Site":"1","Period":"Week","Date":"2010-03-01 - 2010-03-07","TimeQueued":"2010-03-07 01:00:00","Waiting":"18000","Started":null,"Processing":"","Status":"Queued"},{"Invalidation":"3","Segment":null,"Site":"1","Period":"Month","Date":"2010-03","TimeQueued":"2010-03-07 01:00:00","Waiting":"18000","Started":null,"Processing":"","Status":"Queued"},{"Invalidation":"4","Segment":null,"Site":"1","Period":"Year","Date":"2010","TimeQueued":"2010-03-07 01:00:00","Waiting":"18000","Started":null,"Processing":"","Status":"Queued"}]';
        $this->applicationTester->run(array(
            'command' => 'diagnostics:archiving-queue',
            '--json' => true
        ));
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }
}

ArchivingQueueTest::$fixture = new OneVisitorTwoVisits();
