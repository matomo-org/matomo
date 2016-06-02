<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Archive\Chunk;
use Piwik\Common;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoVisitsWithCustomVariables;

/**
 * Tests use of custom variable segments.
 *
 * @group Plugins
 * @group TwoVisitsWithCustomVariablesSegmentMatchVisitorTypeTest
 */
class TwoVisitsWithCustomVariablesSegmentMatchVisitorTypeTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        // Segment matching some
        $segments = array('customVariableName1==VisitorType;customVariableValue1==LoggedIn',
                          'customVariableName1==VisitorType;customVariableValue1=@LoggedI');

        $apiToCall = array('Referrers.getKeywords', 'CustomVariables.getCustomVariables', 'VisitsSummary.get');

        $periods = array('day', 'week');

        // We run it twice just to check that running archiving twice for same input parameters doesn't create more records/overhead
        $result = array();
        for ($i = 1; $i <= 2; $i++) {
            foreach ($segments as $segment) {
                $result[] = array(
                    $apiToCall, array('idSite'       => 'all',
                                      'date'         => self::$fixture->dateTime,
                                      'periods'      => $periods,
                                      'setDateLastN' => true,
                                      'segment'      => $segment)
                );
            }
        }

        return $result;
    }

    /**
     * @depends testApi
     */
    public function testCheck()
    {
        // ----------------------------------------------
        // Implementation Checks
        // ----------------------------------------------
        // Verify that, when a segment is specified, only the requested report is processed
        // In this case, check that only the Custom Variables blobs have been processed

        $tests = array(
            // 1) CHECK 'day' archive stored in January
            // We expect 2 segments
            //   * (1 custom variable name + 2 ref metrics
            //      + 1 subtable chunk for the custom var values + 5 Referrers blob (2 of them subtables)
            //   )
            'archive_blob_2010_01'    => 18,
            // This contains all 'last N' weeks & days,
            // (2 metrics
            //  + 2 referrer metrics
            //  + 3 done flag )
            //  * 2 segments
            // + 1 Done flag per Plugin, for each "Last N" date
            'archive_numeric_2010_01' => 138,

            // 2) CHECK 'week' archive stored in December (week starts the month before)
            // We expect 2 segments * (1 custom variable name + 2 ref metrics + 1 subtable chunk for the values of the name + 5 referrers blob (2 of them subtables))
            'archive_blob_2009_12'    => 18,
            // 7 metrics,
            // 2 Referrer metrics (Referrers_distinctSearchEngines/Referrers_distinctKeywords),
            // 6 done flag (referrers, CustomVar, VisitsSummary), 3 for period = 1 and 3 for period = 2
            // X * 2 segments
            'archive_numeric_2009_12' => (6 + 2 + 3 + 3) * 2,
        );
        foreach ($tests as $table => $expectedRows) {
            $sql = "SELECT count(*) FROM " . Common::prefixTable($table);
            $countBlobs = Db::get()->fetchOne($sql);

            if($expectedRows != $countBlobs) {
                $output = Db::get()->fetchAll("SELECT * FROM " . Common::prefixTable($table) . " ORDER BY name, idarchive ASC");
                var_export('This is debug output from ' . __CLASS__ . ' in case of an error: ');
                var_export($output);
            }
            $this->assertEquals($expectedRows, $countBlobs, "$table: %s");
        }
    }

    /**
     *  Check that it merges all subtables into one blob entry
     *
     * @depends      testApi
     */
    public function test_checkArchiveRecords_shouldMergeSubtablesIntoOneRow()
    {
        $chunk = new Chunk();

        $tests = array(
            'archive_blob_2010_01' => array(
                $chunk->getRecordNameForTableId('CustomVariables_valueByName', 0) => 6,
                $chunk->getRecordNameForTableId('Referrers_keywordBySearchEngine', 0) => 1,
                $chunk->getRecordNameForTableId('Referrers_searchEngineByKeyword', 0) => 1
            ),
            'archive_blob_2009_12' => array(
                $chunk->getRecordNameForTableId('CustomVariables_valueByName', 0) => 6,
                $chunk->getRecordNameForTableId('Referrers_keywordBySearchEngine', 0) => 1,
                $chunk->getRecordNameForTableId('Referrers_searchEngineByKeyword', 0) => 1,
            )
        );
        $numTests = 0;
        foreach ($tests as $table => $expectedSubtables) {
            foreach ($expectedSubtables as $name => $expectedNumSubtables) {
                $sql   = "SELECT `value` FROM " . Common::prefixTable($table) . " WHERE `name` ='$name'";
                $blobs = Db::get()->fetchAll($sql);

                foreach ($blobs as $blob) {
                    $numTests++;
                    $blob = $blob['value'];
                    $blob = gzuncompress($blob);
                    $blob = unserialize($blob);

                    $countSubtables = count($blob);

                    $this->assertEquals($expectedNumSubtables, $countSubtables, "$name in $table expected to contain $expectedNumSubtables subtables, got $countSubtables");
                }
            }
        }

        // 6 _subtables entries + 6 _subtables entries for the segment
        $this->assertEquals(12, $numTests, "$numTests were executed but expected 12");
    }

    public static function getOutputPrefix()
    {
        return 'twoVisitsWithCustomVariables_segmentMatchVisitorType';
    }
}

TwoVisitsWithCustomVariablesSegmentMatchVisitorTypeTest::$fixture = new TwoVisitsWithCustomVariables();
TwoVisitsWithCustomVariablesSegmentMatchVisitorTypeTest::$fixture->doExtraQuoteTests = false;