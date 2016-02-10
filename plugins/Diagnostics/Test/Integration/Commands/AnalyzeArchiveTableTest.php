<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Test\Integration\Commands;

use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;

/**
 * TODO: This could be a unit test if we could inject the ArchiveTableDao in the command
 * @group AnalyzeArchiveTableTest
 */
class AnalyzeArchiveTableTest extends ConsoleCommandTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // make sure archiving is initiated so there is data in the archive tables
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01', 'browserCode==FF');
        VisitsSummaryAPI::getInstance()->get(self::$fixture->idSite, 'month', '2010-03-01', 'daysSinceFirstVisit==2');
    }

    public function test_CommandOutput_IsAsExpected()
    {
        $expected = <<<OUTPUT
Statistics for the archive_numeric_2010_03 and archive_blob_2010_03 tables:

+-------------------------------------------+------------+---------------+-------------+---------+-----------+----------------+-------------+
| Group                                     | # Archives | # Invalidated | # Temporary | # Error | # Segment | # Numeric Rows | # Blob Rows |
+-------------------------------------------+------------+---------------+-------------+---------+-----------+----------------+-------------+
| week[2010-03-01 - 2010-03-07] idSite = 1  | 3          | 0             | 0           | 0       | 2         | 36             | 63          |
| month[2010-03-01 - 2010-03-31] idSite = 1 | 3          | 0             | 0           | 0       | 2         | 36             | 63          |
| day[2010-03-03 - 2010-03-03] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-04 - 2010-03-04] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-05 - 2010-03-05] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-06 - 2010-03-06] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 36             | 51          |
| day[2010-03-07 - 2010-03-07] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-08 - 2010-03-08] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| week[2010-03-08 - 2010-03-14] idSite = 1  | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-09 - 2010-03-09] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-10 - 2010-03-10] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-11 - 2010-03-11] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-12 - 2010-03-12] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-13 - 2010-03-13] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-14 - 2010-03-14] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-15 - 2010-03-15] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| week[2010-03-15 - 2010-03-21] idSite = 1  | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-16 - 2010-03-16] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-17 - 2010-03-17] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-18 - 2010-03-18] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-19 - 2010-03-19] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-20 - 2010-03-20] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-21 - 2010-03-21] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-22 - 2010-03-22] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| week[2010-03-22 - 2010-03-28] idSite = 1  | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-23 - 2010-03-23] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-24 - 2010-03-24] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-25 - 2010-03-25] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-26 - 2010-03-26] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-27 - 2010-03-27] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-28 - 2010-03-28] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-29 - 2010-03-29] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-30 - 2010-03-30] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
| day[2010-03-31 - 2010-03-31] idSite = 1   | 3          | 0             | 0           | 0       | 2         | 0              | 0           |
+-------------------------------------------+------------+---------------+-------------+---------+-----------+----------------+-------------+

Total # Archives: 102
Total # Invalidated Archives: 0
Total # Temporary Archives: 0
Total # Error Archives: 0
Total # Segment Archives: 68


OUTPUT;

        $this->applicationTester->run(array(
            'command' => 'diagnostics:analyze-archive-table',
            'table-date' => '2010_03',
        ));
        $actual = $this->applicationTester->getDisplay();

        $this->assertEquals($expected, $actual);
    }
}

AnalyzeArchiveTableTest::$fixture = new OneVisitorTwoVisits();