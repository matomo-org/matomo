<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;

/**
 * Tests some API using range periods & makes sure the correct amount of blob/numeric
 * archives are created.
 */
class Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange_ArchivingTests extends IntegrationTestCase
{
    public static $fixture = null; // initialized below test definition

    public static function getOutputPrefix()
    {
        return 'oneVisitor_oneWebsite_severalDays_DateRange';
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * *
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
                           'UserSettings.getResolution',
                           'VisitFrequency.get',
                           'VisitTime.getVisitInformationPerServerTime');

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
                                                    'periods' => array('range'), 'segment' => $segment));
            }
        }

        return $result;
    }

    /**
     * @depends      testApi
     * @group        Integration
     * *
     */
    public function testCheck()
    {
        // Check that requesting period "Range" means
        // only processing the requested Plugin blob (Actions in this case), not all Plugins blobs
        $tests = array(
            // 5 blobs for the Actions plugin, 7 blobs for UserSettings, 2 blobs VisitTime
            'archive_blob_2010_12'    => (5 + 8 + 2) * 3,

            /**
             *  In Each "Period=range" Archive, we expect following non zero numeric entries:
             *                 5 metrics + 1 flag  //VisitsSummary
             *               + 2 metrics + 1 flag //Actions
             *               + 1 flag // UserSettings
             *               + 1 flag //VisitTime
             *               = 11
             *
             *   because we call VisitFrequency.get, this creates an archive for the visitorType==returning segment.
             *          -> There are two archives for each segment (one for "countryCode!=aa"
             *                      and VisitFrequency creates one for "countryCode!=aa;visitorType==returning")
             *
             * So each period=range will have = 11 records + (5 metrics + 1flag // VisitsSummary)
             *                                = 17
             *
             * Total expected records = count unique archives * records per archive
             *                        = 3 * 17
             *                        = 51
             */
            'archive_numeric_2010_12' => 17 * 3,

            // all "Range" records are in December
            'archive_blob_2011_01'    => 0,
            'archive_numeric_2011_01' => 0,
        );
        foreach ($tests as $table => $expectedRows) {
            $sql = "SELECT count(*) FROM " . Common::prefixTable($table) . " WHERE period = " . Piwik::$idPeriods['range'];
            $countBlobs = Db::get()->fetchOne($sql);

            if($expectedRows != $countBlobs) {
                var_export(Db::get()->fetchAll("SELECT * FROM " . Common::prefixTable($table). " WHERE period = " . Piwik::$idPeriods['range'] . " ORDER BY idarchive ASC"));
            }
            $this->assertEquals($expectedRows, $countBlobs, "$table expected $expectedRows, got $countBlobs");
        }
    }

}

Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange_ArchivingTests::$fixture
    = new Test_Piwik_Fixture_VisitsOverSeveralDays();

