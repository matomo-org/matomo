<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\ArchiveProcessor\Rules;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Period;
use Piwik\Plugins\VisitsSummary\API;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/BenchmarkTestCase.php';

/**
 * Runs the archiving process.
 */
class ArchiveQueryBenchmark extends BenchmarkTestCase
{
    private $archivingLaunched = false;
    
    public function setUp()
    {
        $archivingTables = ArchiveTableCreator::getTablesArchivesInstalled();
        if (empty($archivingTables)) {
            $this->archivingLaunched = true;
            API::getInstance()->get(
                self::$fixture->idSite, self::$fixture->period, self::$fixture->date);
        }
    }

    /**
     * @group        Benchmarks
     */
    public function testArchivingProcess()
    {
        if ($this->archivingLaunched) {
            echo "NOTE: Had to archive data, memory results will not be accurate. Run again for better results.";
        }

        Rules::$archivingDisabledByTests = true;
        
        $period = Period::factory(self::$fixture->period, Date::factory(self::$fixture->date));
        $dateRange = $period->getDateStart().','.$period->getDateEnd();
        
        API::getInstance()->get(self::$fixture->idSite, 'day', $dateRange);
    }
}
