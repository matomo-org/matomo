<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\tests\Integration\Commands;

use Piwik\Date;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;

/**
 * @group ArchivingInstanceStatisticsTest
 */
class ArchivingInstanceStatisticsTest extends ConsoleCommandTestCase
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
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-02-01');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-02-01', 'browserCode==FF');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-02-01', 'daysSinceFirstVisit==2');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01', 'browserCode==FF');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01', 'daysSinceFirstVisit==2');

        // Create a segment
        SegmentEditorAPI::getInstance()->add('test segment', 'browserCode==IE', self::$fixture->idSite);

        Date::$now = strtotime('2010-04-07 06:00:00');
    }

    public function test_CommandOutput_IsAsExpected()
    {
        $expected = <<<OUTPUT
+----------------------+------------------------+
| Statistic Name       | Value                  |
+----------------------+------------------------+
| Site Count           | 3                      |
| Segment Count        | 1                      |
| Database Version     | mysql-version-redacted |
| Last full Month Hits | 8                      |
| Last 12 Month Hits   | 8                      |
+----------------------+------------------------+
OUTPUT;

        $this->applicationTester->run([
            'command' => 'diagnostics:archiving-instance-statistics'
        ]);
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }

    public function test_CommandOutput_withJsonOption_IsAsExpected()
    {
        $expected = '[["Site Count",3],["Segment Count",1],["Database Version","mysql-version-redacted"],["Last full Month Hits",8],["Last 12 Month Hits",8]]';
        $this->applicationTester->run([
            'command' => 'diagnostics:archiving-instance-statistics',
            '--json' => true
        ]);
        $actual = $this->applicationTester->getDisplay();

        $this->assertStringMatchesFormat($expected, $actual);
    }
}

ArchivingInstanceStatisticsTest::$fixture = new OneVisitorTwoVisits();
