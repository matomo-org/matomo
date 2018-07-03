<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Plugins\VisitsSummary\API;
use Piwik\Tests\Framework\TestCase\BenchmarkTestCase;

/**
 * Runs the archiving process.
 */
class ArchivingProcessBenchmark extends BenchmarkTestCase
{
    public function setUp()
    {
        BenchmarkTestCase::deleteArchiveTables();
    }

    /**
     * @group        Benchmarks
     */
    public function testArchivingProcess()
    {
        API::getInstance()->get(
            self::$fixture->idSite, self::$fixture->period, self::$fixture->date);
    }
}
