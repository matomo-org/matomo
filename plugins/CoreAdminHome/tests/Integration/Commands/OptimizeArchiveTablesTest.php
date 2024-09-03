<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group Core
 */
class OptimizeArchiveTablesTest extends ConsoleCommandTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        ArchiveTableCreator::getNumericTable(Date::factory('2015-01-01'));
        ArchiveTableCreator::getNumericTable(Date::factory('2015-02-02'));
        ArchiveTableCreator::getNumericTable(Date::factory('2015-03-03'));
    }

    /**
     * @dataProvider getDatesToTest
     */
    public function testCommandOptimizesCorrectTables($dates, $expectedOptimizedTableDates)
    {
        $code = $this->applicationTester->run(array(
            'command' => 'database:optimize-archive-tables',
            'dates' => $dates,
            '--dry-run' => true,
        ));

        $this->assertEquals(0, $code, $this->getCommandDisplayOutputErrorMessage());

        $output = $this->applicationTester->getDisplay();

        preg_match_all('/Optimizing table \'([^\']+)\'/', $output, $matches);
        $tablesOptimized = $matches[1];

        $expectedOptimizedTables = array();
        foreach ($expectedOptimizedTableDates as $date) {
            $expectedOptimizedTables[] = 'archive_numeric_' . $date;
            $expectedOptimizedTables[] = 'archive_blob_' . $date;
        }

        $this->assertEquals($expectedOptimizedTables, $tablesOptimized);
    }

    public function getDatesToTest()
    {
        return array(
            array(
                array('all'),
                array('2015_01', '2015_02', '2015_03'),
            ),

            array(
                array('now'),
                array(date('Y_m')),
            ),

            array(
                array('2015-01-01', '2015-02-03', '2014-01-01', '2013-05-12', '2015-05-05'),
                array('2015_01', '2015_02', '2014_01', '2013_05', '2015_05'),
            ),

            array(
                array('last1'),
                array(Date::factory('now')->subMonth(1)->toString('Y_m')),
            ),

            array(
                array('last5'),
                array(
                    Date::factory('now')->subMonth(1)->toString('Y_m'),
                    Date::factory('now')->subMonth(2)->toString('Y_m'),
                    Date::factory('now')->subMonth(3)->toString('Y_m'),
                    Date::factory('now')->subMonth(4)->toString('Y_m'),
                    Date::factory('now')->subMonth(5)->toString('Y_m'),
                ),
            ),

            array(
                array('2015-01-01'),
                array('2015_01'),
            ),
        );
    }
}
