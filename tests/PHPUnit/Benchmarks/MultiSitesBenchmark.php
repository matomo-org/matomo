<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\ArchiveProcessor\Rules;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Plugins\MultiSites\API as APIMultiSites;
use Piwik\Plugins\VisitsSummary\API as APIVisitsSummary;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/BenchmarkTestCase.php';

/**
 * Tests MultiSites API. Should be used with ManyThousandSitesOneVisitEach benchmark fixture.
 */
class MultiSitesBenchmark extends BenchmarkTestCase
{
    private $archivingLaunched = false;
    
    public function setUp()
    {
        $archivingTables = ArchiveTableCreator::getTablesArchivesInstalled();
        if (empty($archivingTables)) {
            $this->archivingLaunched = true;
            APIVisitsSummary::getInstance()->get(
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
        APIMultiSites::getInstance()->getAll(self::$fixture->period, self::$fixture->date);
    }
}
