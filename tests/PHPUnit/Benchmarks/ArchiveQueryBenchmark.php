<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/BenchmarkTestCase.php';

/**
 * Runs the archiving process.
 */
class ArchiveQueryBenchmark extends BenchmarkTestCase
{
    private $archivingLaunched = false;
    
    public function setUp()
    {
        $archivingTables = Piwik_DataAccess_ArchiveTableCreator::getTablesArchivesInstalled();
        if (empty($archivingTables)) {
            $this->archivingLaunched = true;
            Piwik_VisitsSummary_API::getInstance()->get(
                self::$fixture->idSite, self::$fixture->period, self::$fixture->date);
        }
    }

    /**
     * @group        Benchmarks
     * @group        ArchivingProcess
     */
    public function testArchivingProcess()
    {
        if ($this->archivingLaunched) {
            echo "NOTE: Had to archive tables, memory results will not be accurate. Run again for better results.";
        }

        Piwik_ArchiveProcessor_Rules::$archivingDisabledByTests = true;
        
        $period = Piwik_Period::factory(self::$fixture->period, Piwik_Date::factory(self::$fixture->date));
        $dateRange = $period->getDateStart().','.$period->getDateEnd();
        
        Piwik_VisitsSummary_API::getInstance()->get(self::$fixture->idSite, 'day', $dateRange);
    }
}
