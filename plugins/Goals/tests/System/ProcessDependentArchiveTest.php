<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Fixtures\ThreeGoalsOnePageview;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @property ThreeGoalsOnePageview $fixture
 */
class ProcessDependentArchiveTest extends SystemTestCase
{
    /**
     * @var ThreeGoalsOnePageview
     */
    public static $fixture = null; // initialized below class definition

    private $archiveTable = 'archive_numeric_2009_01';
    private $requestRange = '2009-01-02,2009-01-26';

    public function tearDown(): void
    {
        Db::query('DELETE from ' . Common::prefixTable($this->archiveTable) . ' WHERE period = 5');
        parent::tearDown();
    }

    public function test_numArchivesCreated_day()
    {
        API::getInstance()->getMetrics(self::$fixture->idSite, 'day', '2009-01-04');
        $this->assertNumRangeArchives(5, 1); // days;
    }

    public function test_numArchivesCreated()
    {
        API::getInstance()->get(self::$fixture->idSite, 'range', $this->requestRange);
        $this->assertNumRangeArchives(6);
    }

    public function test_numArchivesCreatedWithSegment()
    {
        API::getInstance()->get(self::$fixture->idSite, 'range', $this->requestRange,'userId!@%2540matomo.org;userId!=hello%2540matomo.org');
        $this->assertNumRangeArchives(6);
    }

    private function assertNumRangeArchives($expectedArchives,$period = 5)
    {
        $archives = Db::fetchAll('SELECT `name` from ' . Common::prefixTable($this->archiveTable) . ' WHERE period = ' . $period . ' and `name` like "done%"');
        $numArchives = count($archives);
        $message = sprintf('Expected archives: %s, got: %s. These were the archives %s', $expectedArchives, $numArchives, json_encode($archives));
        $this->assertEquals($expectedArchives, $numArchives, $message);
    }

}
ProcessDependentArchiveTest::$fixture = new ThreeGoalsOnePageview();
