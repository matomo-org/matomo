<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Archive\ArchivePurger;
use Piwik\Archive\Chunk;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\VisitsOverSeveralDays;

/**
 * Tests some API using range periods & makes sure the correct amount of blob/numeric
 * archives are created.
 *
 * @group OneVisitorOneWebsiteSeveralDaysDateRangeArchivingTest
 * @group Core
 */
class OneVisitorOneWebsiteSeveralDaysDateRangeArchivingTest extends SystemTestCase
{
    /**
     * @var VisitsOverSeveralDays
     */
    public static $fixture = null; // initialized below test definition

    public static function getOutputPrefix()
    {
        return 'oneVisitor_oneWebsite_severalDays_DateRange';
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;

        $apiToCall = array('Actions.getPageUrls',
                           'VisitsSummary.get',
                           'Resolution.getResolution',
                           'VisitFrequency.get',
                           'VisitTime.getVisitInformationPerServerTime');

        // 2 segments: ALL and another way of expressing ALL but triggering the Segment code path
        // 2 segments: ALL and another way of expressing ALL but triggering the Segment code path
        $segments = array(
            false,
            'countryCode!=aa',
            'pageUrl!=ThisIsNotKnownPageUrl',
        );

        // Running twice just as health check that second call also works
        $result = array();
        for ($i = 0; $i <= 1; $i++) {
            foreach ($segments as $segment) {
                $result[] = array($apiToCall, array('idSite'  => $idSite, 'date' => '2010-12-15,2011-01-15',
                                                    'periods' => array('range'),
                                                    'segment' => $segment,
                                                    'otherRequestParameters' => array(
                                                        'flat'                   => '0',
                                                        'expanded'               => '0'
                                                    ),
                ));
            }
        }

        // Testing Date range in January only
        // Because of flat=1, this test will archive all sub-tables
        $result[] = array('Actions.getPageUrls', array('idSite'  => $idSite, 'date' => '2011-01-01,2011-02-01',
                                                       'periods' => array('range'),
                                                       'otherRequestParameters' => array(
                                                           'flat'                   => '1',
                                                           'expanded'               => '0'
                                                       ),
                                                       'testSuffix' => '_periodIsRange_flattened_')
        );
        // testing the same with expanded=1 should not create new archive records
        $result[] = array('Actions.getPageUrls', array('idSite'  => $idSite, 'date' => '2011-01-01,2011-02-01',
                                                       'periods' => array('range'),
                                                       'otherRequestParameters' => array(
                                                           'flat'                   => '0',
                                                           'expanded'               => '1'
                                                       ),
                                                       'testSuffix' => '_periodIsRange_expanded_')
        );
        return $result;
    }

    /**
     *  Check that requesting period "Range" means only processing
     *  the requested Plugin blob (Actions in this case), not all Plugins blobs
     *
     * @depends      testApi
     */
    public function test_checkArchiveRecords_whenPeriodIsRange()
    {
        $archivePurger = StaticContainer::get(ArchivePurger::class);
        foreach (self::$fixture->dateTimes as $date) {
            $archivePurger->purgeInvalidatedArchivesFrom(Date::factory($date));
        }

        // we expect 1 blobs for Actions plugins, because we query for only one Actions report and flat=1 or expanded=1 was not set
        // so we only archived the parent table
        $expectedActionsBlobs = 1;

        // When flat=1, Actions plugin will process 5 + 1 extra chunk blobs (URL = 'http://example.org/sub1/sub2/sub3/news')
        $expectedActionsBlobsWhenFlattened = $expectedActionsBlobs + 1;

        $tests = array(
            'archive_blob_2010_12'    => ( ($expectedActionsBlobs + 1) /*Actions*/
                    + 1 /* Resolution */
                    + 1 /* VisitTime */) * 3,

            /**
             * segments: 9 (including all visits)
             * plugins: 4 different plugins
             *   VisitsSummary: 9 archives (8 segments + all visits) (4 metrics in each + 3 bounce_counts across 3 archives)
             *   Actions: 3 archives (2 segments + all visits) (0 metrics in each, since no metrics are requested for period=range)
             *   Resolution: 3 archives (2 segments + all visits) (0 metrics in each)
             *   VisitTime: 3 archives (2 segments + all visits) (0 metrics in each)
             *
             * Total: 9 VisitsSummary done flags + ((4 * 9) + 3) VisitsSummary metrics
             *   + 3 Actions done flags
             *   + 3 Resolution done flags
             *   + 3 VisitTime done flags
             * = 63
             */
            'archive_numeric_2010_12' => 57,

            /**
             * In the January date range,
             * we archive only Actions plugins.
             * It is flattened so all 3 sub-tables should be archived.
             */
            'archive_blob_2011_01'    => $expectedActionsBlobsWhenFlattened,

            /**
             *   5 metrics + 1 flag // VisitsSummary
             * + 1 flag // Actions (no metrics, just blobs)
             */
            'archive_numeric_2011_01' => (6 + 1),

            // nothing in Feb
            'archive_blob_2011_02'    => 0,
            'archive_numeric_2011_02' => 0,
        );
        foreach ($tests as $table => $expectedRows) {
            if ($expectedRows === 0 && !$this->tableExists($table)) {
                continue;
            }

            $sql = "SELECT count(*) FROM " . Common::prefixTable($table) . " WHERE period = " . Piwik::$idPeriods['range'];
            $countBlobs = Db::get()->fetchOne($sql);

            if ($expectedRows != $countBlobs) {
                $this->printDebugWhenTestFails($table);
            }
            $this->assertEquals($expectedRows, $countBlobs, "$table expected $expectedRows, got $countBlobs");
        }
    }

    /**
     *  Check that requesting period "Range" means only processing
     *  the requested Plugin blob (Actions in this case), not all Plugins blobs
     *
     * @depends      testApi
     */
    public function test_checkArchiveRecords_shouldMergeSubtablesIntoOneRow()
    {
        $tests = array(
            'archive_blob_2010_12' => 3,

            /**
             * In the January date range,
             * we archive only Actions plugins.
             * It is flattened so all 3 sub-tables should be archived.
             */
            'archive_blob_2011_01'    => 3,
        );
        $chunk = new Chunk();
        $chunkName = $chunk->getRecordNameForTableId('Actions_actions_url', 0);

        foreach ($tests as $table => $expectedNumSubtables) {
            $sql = "SELECT value FROM " . Common::prefixTable($table) . " WHERE period = " . Piwik::$idPeriods['range'] . " and `name` ='$chunkName'";
            $blob = Db::get()->fetchOne($sql);
            $blob = gzuncompress($blob);
            $blob = unserialize($blob);
            $countSubtables = count($blob);

            $this->assertEquals($expectedNumSubtables, $countSubtables, "Actions_actions_url_chunk_0_99 in $table expected to contain $expectedNumSubtables subtables, got $countSubtables");
        }
    }

    /**
     * @param $table
     */
    protected function printDebugWhenTestFails($table)
    {
        $data = Db::get()->fetchAll("SELECT * FROM " . Common::prefixTable($table) . " WHERE period = " . Piwik::$idPeriods['range'] . " ORDER BY idarchive ASC");
        if (strpos($table, 'blob') !== false) {
            $data = array_map(function ($r) {
                unset($r['value']);
                return $r;
            }, $data);
        }
        var_export($data);

        $idArchives = array();
        foreach ($data as $row) {
            $idArchives[] = $row['idarchive'];
        }
        $idArchives = array_unique($idArchives);
        foreach ($idArchives as $idArchive) {
            $numericTable = str_replace("blob", "numeric", Common::prefixTable($table));
            var_export(Db::get()->fetchAll("SELECT idarchive, name FROM " . $numericTable . " WHERE idarchive = ? AND name LIKE 'done%' LIMIT 1 ", $idArchive));
        }
    }

    private function tableExists($table)
    {
        return (bool) Db::fetchOne("SHOW TABLES LIKE '$table'");
    }
}

OneVisitorOneWebsiteSeveralDaysDateRangeArchivingTest::$fixture = new VisitsOverSeveralDays();
