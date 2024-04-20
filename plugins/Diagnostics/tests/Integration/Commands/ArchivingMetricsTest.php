<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\tests\Integration\Commands;

use Piwik\Date;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;

/**
 * @group ArchivingMetricsTest
 */
class ArchivingMetricsTest extends ConsoleCommandTestCase
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
+--------------------------------------+---------------------+
| Metric                               | Value               |
+--------------------------------------+---------------------+
| Total Invalidation Count             | 4                   |
| In Progress Invalidation Count       | 0                   |
| Scheduled Invalidation Count         | 4                   |
| Earliest invalidation ts_started     |                     |
| Latest invalidation ts_started       |                     |
| Earliest invalidation ts_invalidated | 2010-03-07 01:00:00 |
| Latest invalidation ts_invalidated   | 2010-03-07 01:00:00 |
| Number of segment invalidations      | 0                   |
| Number of plugin invalidations       | 0                   |
| List of plugins being invalidated    |                     |
+--------------------------------------+---------------------+
OUTPUT;

        $this->applicationTester->run([
            'command' => 'diagnostics:archiving-metrics'
        ]);
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }

    public function test_CommandOutput_withJsonOption_IsAsExpected()
    {
        $expected = '[["Total Invalidation Count","4"],["In Progress Invalidation Count","0"],["Scheduled Invalidation Count","4"],["Earliest invalidation ts_started",""],["Latest invalidation ts_started",""],["Earliest invalidation ts_invalidated","2010-03-07 01:00:00"],["Latest invalidation ts_invalidated","2010-03-07 01:00:00"],["Number of segment invalidations","0"],["Number of plugin invalidations","0"],["List of plugins being invalidated",""]]';
        $this->applicationTester->run([
            'command' => 'diagnostics:archiving-metrics',
            '--json' => true
        ]);
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }
}

ArchivingMetricsTest::$fixture = new OneVisitorTwoVisits();
