<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\tests\Integration\Diagnostic;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugins\Diagnostics\Diagnostic\ArchiveInvalidationsInformational;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Translation\Translator;

class ArchiveInvalidationsInformationalTest extends IntegrationTestCase
{
    public function testExecute()
    {
        $segmentHash = md5('something');
        $anotherSegmentHash = md5('anothersomething');
        $this->insertInvalidations([
            ['idsite' => '1', 'name' => 'done', 'date1' => '2020-01-13', 'date2' => '2020-01-13', 'period' => '1', 'report' => '', 'ts_started' => '2020-01-13 02:00:00', 'ts_invalidated' => '2020-01-12 02:00:00', 'status' => 1],
            ['idsite' => '1', 'name' => 'done.MyPlugin', 'date1' => '2020-01-13', 'date2' => '2020-01-19', 'period' => '2', 'report' => '', 'ts_started' => NULL, 'ts_invalidated' => '2020-01-12 06:00:00', 'status' => 0],
            ['idsite' => '1', 'name' => 'done' . $segmentHash, 'date1' => '2020-01-01', 'date2' => '2020-01-31', 'period' => '3', 'report' => '', 'ts_started' => NULL, 'ts_invalidated' => '2020-01-12 04:00:00', 'status' => 0],
            ['idsite' => '2', 'name' => 'done' . $segmentHash . '.MyPlugin', 'date1' => '2020-01-15', 'date2' => '2020-01-15', 'period' => '1', 'report' => '', 'ts_started' => '2020-01-14 02:00:00', 'ts_invalidated' => '2020-01-12 03:00:00', 'status' => 1],
            ['idsite' => '2', 'name' => 'done.MyOtherPlugin', 'date1' => '2020-01-15', 'date2' => '2020-01-15', 'period' => '1', 'report' => '', 'ts_started' => NULL, 'ts_invalidated' => '2020-01-12 12:00:00', 'status' => 0],
            ['idsite' => '3', 'name' => 'done' . $anotherSegmentHash, 'date1' => '2020-01-06', 'date2' => '2020-01-12', 'period' => '2', 'report' => '', 'ts_started' => NULL, 'ts_invalidated' => '2020-01-12 02:30:00', 'status' => 0],
            ['idsite' => '3', 'name' => 'done' . $anotherSegmentHash . '.AThirdPlugin', 'date1' => '2020-01-01', 'date2' => '2020-01-31', 'period' => '3', 'report' => 'aReport', 'ts_started' => '2020-01-13 07:00:00', 'ts_invalidated' => '2020-01-11 02:00:00', 'status' => 1],
            ['idsite' => '3', 'name' => 'done', 'date1' => '2020-02-06', 'date2' => '2020-02-06', 'period' => '1', 'report' => '', 'ts_started' => NULL, 'ts_invalidated' => '2020-01-10 02:00:00', 'status' => 0],
            ['idsite' => '3', 'name' => 'done', 'date1' => '2020-01-16', 'date2' => '2020-01-16', 'period' => '1', 'report' => '', 'ts_started' => NULL, 'ts_invalidated' => '2020-01-20 02:00:00', 'status' => 0],
            ['idsite' => '3', 'name' => 'done' . $segmentHash, 'date1' => '2020-01-16', 'date2' => '2020-01-16', 'period' => '1', 'report' => '', 'ts_started' => NULL, 'ts_invalidated' => '2020-01-12 02:00:00', 'status' => 0],
        ]);

        $diagnostic = new ArchiveInvalidationsInformational(StaticContainer::get(Translator::class));

        $counts = $diagnostic->getInvalidationCounts();
        $this->assertEquals([
            0 => '7',
            1 => '3',
            'all' => 10,
        ], $counts);

        $minMaxes = $diagnostic->getInvalidationMinMaxes();
        $this->assertEquals([
            'min_ts_started' => '2020-01-13 02:00:00',
            'max_ts_started' => '2020-01-14 02:00:00',
            'min_ts_invalidated' => '2020-01-10 02:00:00',
            'max_ts_invalidated' => '2020-01-20 02:00:00',
        ], $minMaxes);

        $types = $diagnostic->getInvalidationTypes();
        $this->assertEquals([
            'count_segment' => 5,
            'count_plugin' => 4,
            'plugins' => [
                'AThirdPlugin',
                'MyOtherPlugin',
                'MyPlugin',
            ],
        ], $types);
    }


    private function insertInvalidations(array $invalidations)
    {
        $table = Common::prefixTable('archive_invalidations');
        foreach ($invalidations as $invalidation) {
            $sql = "INSERT INTO $table (name, idsite, date1, date2, period, report, status, ts_started, ts_invalidated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            Db::query($sql, [
                $invalidation['name'],
                $invalidation['idsite'],
                $invalidation['date1'],
                $invalidation['date2'],
                $invalidation['period'],
                $invalidation['report'],
                $invalidation['status'],
                $invalidation['ts_started'],
                $invalidation['ts_invalidated'],
            ]);
        }
    }
}
