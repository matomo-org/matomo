<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\Archive as PiwikArchive;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Archive\Chunk;

class Archive extends PiwikArchive
{
    public function get($archiveNames, $archiveDataType, $idSubtable = null)
    {
        return parent::get($archiveNames, $archiveDataType, $idSubtable);
    }

}

/**
 * @group Core
 */
class ArchiveTest extends IntegrationTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture;

    public function tearDown()
    {
        parent::tearDown();

        unset($_GET['trigger']);
    }

    protected static function configureFixture($fixture)
    {
        $fixture->createSuperUser = true;
    }

    protected static function beforeTableDataCached()
    {
        $date = Date::factory('2010-03-01');

        $archiveTableCreator = new ArchiveTableCreator();
        $archiveTableCreator->getBlobTable($date);
        $archiveTableCreator->getNumericTable($date);
    }

    public function getForceOptionsForForceArchivingOnBrowserRequest()
    {
        return array(
            array(1),
            array(null)
        );
    }

    /**
     * @dataProvider getForceOptionsForForceArchivingOnBrowserRequest
     */
    public function test_ArchivingIsLaunchedForRanges_WhenForceOnBrowserRequest_IsTruthy($optionValue)
    {
        $this->archiveDataForIndividualDays();

        Config::getInstance()->General['archiving_range_force_on_browser_request'] = $optionValue;
        Rules::setBrowserTriggerArchiving(false);

        $data = $this->initiateArchivingForRange();

        $this->assertNotEmpty($data);
        $this->assertArchiveTablesAreNotEmpty('2010_03');
    }

    public function test_ArchivingIsNotLaunchedForRanges_WhenForceOnBrowserRequest_IsFalse()
    {
        $this->archiveDataForIndividualDays();

        Config::getInstance()->General['archiving_range_force_on_browser_request'] = 0;
        Rules::setBrowserTriggerArchiving(false);

        $data = $this->initiateArchivingForRange();

        $this->assertEmpty($data);
        $this->assertArchiveTablesAreEmpty('2010_03');
    }

    public function test_ArchiveIsLaunched_WhenForceOnBrowserRequest_IsFalse_AndArchivePhpTriggered()
    {
        $this->archiveDataForIndividualDays();

        Config::getInstance()->General['archiving_range_force_on_browser_request'] = 0;
        $_GET['trigger'] = 'archivephp';
        Rules::setBrowserTriggerArchiving(false);

        $data = $this->initiateArchivingForRange();

        $this->assertNotEmpty($data);
        $this->assertArchiveTablesAreNotEmpty('2010_03');
    }

    public function test_ArchiveBlob_ShouldBeAbleToLoadFirstLevelDataArrays()
    {
        $this->createManyDifferentArchiveBlobs();

        $archive = $this->getArchive('day', '2013-01-01,2013-01-05');
        $dataArrays = $archive->get(array('Actions_Actionsurl'), 'blob');

        $this->assertArchiveBlob($dataArrays, '2013-01-01', array('Actions_Actionsurl' => 'test01'));
        $this->assertArchiveBlob($dataArrays, '2013-01-02', array('Actions_Actionsurl' => 'test02'));
        $this->assertArchiveBlob($dataArrays, '2013-01-03', array('Actions_Actionsurl' => 'test03'));
        $this->assertArchiveBlob($dataArrays, '2013-01-04', array('Actions_Actionsurl' => 'test04'));
        $this->assertArchiveBlob($dataArrays, '2013-01-05', array('Actions_Actionsurl' => 0));
    }

    public function test_ArchiveBlob_ShouldBeAbleToLoadOneSubtable_NoMatterWhetherTheyAreStoredSeparatelyOrInACombinedSubtableEntry()
    {
        $this->createManyDifferentArchiveBlobs();

        $archive = $this->getArchive('day', '2013-01-01,2013-01-05');
        $dataArrays = $archive->get(array('Actions_Actionsurl'), 'blob', 2);

        $this->assertArchiveBlob($dataArrays, '2013-01-01', array('Actions_Actionsurl_2' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-02', array('Actions_Actionsurl_2' => 'test2'));
        $this->assertArchiveBlob($dataArrays, '2013-01-03', array('Actions_Actionsurl_2' => 'subtable2'));
        $this->assertArchiveBlob($dataArrays, '2013-01-04', array('Actions_Actionsurl_2' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-05', array('Actions_Actionsurl_2' => 0));

        // test another one
        $dataArrays = $archive->get(array('Actions_Actionsurl'), 'blob', 5);

        $this->assertArchiveBlob($dataArrays, '2013-01-01', array('Actions_Actionsurl_5' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-02', array('Actions_Actionsurl_5' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-03', array('Actions_Actionsurl_5' => 'subtable5'));
        $this->assertArchiveBlob($dataArrays, '2013-01-04', array('Actions_Actionsurl_5' => 'subtable45'));
        $this->assertArchiveBlob($dataArrays, '2013-01-05', array('Actions_Actionsurl_5' => 0));

        // test one that does not exist
        $dataArrays = $archive->get(array('Actions_Actionsurl'), 'blob', 999);

        $this->assertArchiveBlob($dataArrays, '2013-01-01', array('Actions_Actionsurl_999' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-02', array('Actions_Actionsurl_999' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-03', array('Actions_Actionsurl_999' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-04', array('Actions_Actionsurl_999' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-05', array('Actions_Actionsurl_999' => 0));
    }

    public function test_ArchiveBlob_ShouldBeAbleToLoadAllSubtables_NoMatterWhetherTheyAreStoredSeparatelyOrInACombinedSubtableEntry()
    {
        $this->createManyDifferentArchiveBlobs();

        $archive = $this->getArchive('day', '2013-01-01,2013-01-06');
        $dataArrays = $archive->get(array('Actions_Actionsurl'), 'blob', Archive::ID_SUBTABLE_LOAD_ALL_SUBTABLES);

        $this->assertArchiveBlob($dataArrays, '2013-01-01', array('Actions_Actionsurl' => 'test01'));
        $this->assertArchiveBlob($dataArrays, '2013-01-02', array('Actions_Actionsurl' => 'test02', 'Actions_Actionsurl_1' => 'test1', 'Actions_Actionsurl_2' => 'test2'));
        $this->assertArchiveBlob($dataArrays, '2013-01-03', array('Actions_Actionsurl' => 'test03', 'Actions_Actionsurl_1' => 'subtable1', 'Actions_Actionsurl_2' => 'subtable2', 'Actions_Actionsurl_5' => 'subtable5'));
        $this->assertArchiveBlob($dataArrays, '2013-01-04', array('Actions_Actionsurl' => 'test04', 'Actions_Actionsurl_5' => 'subtable45', 'Actions_Actionsurl_6' => 'subtable6'));
        $this->assertArchiveBlob($dataArrays, '2013-01-05', array('Actions_Actionsurl' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-06', array('Actions_Actionsurl' => 'test06'));
    }

    public function test_ArchiveBlob_ShouldBeAbleToLoadDifferentArchives_NoMatterWhetherTheyAreStoredSeparatelyOrInACombinedSubtableEntry()
    {
        $this->createManyDifferentArchiveBlobs();

        $archive = $this->getArchive('day', '2013-01-01,2013-01-06');
        $dataArrays = $archive->get(array('Actions_Actionsurl', 'Actions_Actions'), 'blob', 2);

        $this->assertArchiveBlob($dataArrays, '2013-01-01', array('Actions_Actionsurl_2' => 0, 'Actions_Actions_2' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-02', array('Actions_Actions_2' => 'actionsSubtable2', 'Actions_Actionsurl_2' => 'test2'));
        $this->assertArchiveBlob($dataArrays, '2013-01-03', array('Actions_Actions_2' => 'actionsTest2', 'Actions_Actionsurl_2' => 'subtable2'));
        $this->assertArchiveBlob($dataArrays, '2013-01-04', array('Actions_Actionsurl_2' => 0, 'Actions_Actions_2' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-05', array('Actions_Actionsurl_2' => 0, 'Actions_Actions_2' => 0));
        $this->assertArchiveBlob($dataArrays, '2013-01-06', array('Actions_Actionsurl_2' => 0, 'Actions_Actions_2' => 0));
    }

    /**
     * @dataProvider findBlobsWithinDifferentChunksDataProvider
     */
    public function test_ArchiveBlob_ShouldBeFindBlobs_WithinDifferentChunks($idSubtable, $expectedBlob)
    {
        $recordName = 'Actions_Actions';

        $chunk    = new Chunk();
        $chunk5   = $chunk->getRecordNameForTableId($recordName, $subtableId = 5);
        $chunk152 = $chunk->getRecordNameForTableId($recordName, $subtableId = 152);
        $chunk399 = $chunk->getRecordNameForTableId($recordName, $subtableId = 399);

        $this->createArchiveBlobEntry('2013-01-02', array(
            $recordName => 'actions_02',
            $chunk5     => serialize(array(1 => 'actionsSubtable1', 2 => 'actionsSubtable2', 5 => 'actionsSubtable5')),
            $chunk152   => serialize(array(151 => 'actionsSubtable151', 152 => 'actionsSubtable152')),
            $chunk399   => serialize(array(399 => 'actionsSubtable399'))
        ));

        $archive = $this->getArchive('day', '2013-01-02,2013-01-02');

        $dataArrays = $archive->get(array('Actions_Actions'), 'blob', $idSubtable);
        $this->assertArchiveBlob($dataArrays, '2013-01-02', $expectedBlob);
    }

    public function findBlobsWithinDifferentChunksDataProvider()
    {
        return array(
            array($idSubtable = 2, $expectedBlobs = array('Actions_Actions_2' => 'actionsSubtable2')),
            array(5, array('Actions_Actions_5' => 'actionsSubtable5')),
            array(151, array('Actions_Actions_151' => 'actionsSubtable151')),
            array(152, array('Actions_Actions_152' => 'actionsSubtable152')),
            array(399, array('Actions_Actions_399' => 'actionsSubtable399')),
            // this one does not exist
            array(404, array('Actions_Actions_404' => 0)),
        );
    }

    private function createManyDifferentArchiveBlobs()
    {
        $recordName1 = 'Actions_Actions';
        $recordName2 = 'Actions_Actionsurl';

        $chunk = new Chunk();
        $chunk0_1 = $chunk->getRecordNameForTableId($recordName1, 0);
        $chunk0_2 = $chunk->getRecordNameForTableId($recordName2, 0);

        $this->createArchiveBlobEntry('2013-01-01', array(
            $recordName2 => 'test01'
        ));
        $this->createArchiveBlobEntry('2013-01-02', array(
            $recordName2        => 'test02',
            $recordName2 . '_1' => 'test1', // testing BC where each subtable was stored seperately
            $recordName2 . '_2' => 'test2', // testing BC
            $recordName1        => 'actions_02',
            $chunk0_1 => serialize(array(1 => 'actionsSubtable1', 2 => 'actionsSubtable2', 5 => 'actionsSubtable5'))
        ));
        $this->createArchiveBlobEntry('2013-01-03', array(
            $recordName2 => 'test03',
            $chunk0_2 => serialize(array(1 => 'subtable1', 2 => 'subtable2', 5 => 'subtable5')),
            $recordName1 => 'actions_03',
            $recordName1 . '_1' => 'actionsTest1',
            $recordName1 . '_2' => 'actionsTest2'
        ));
        $this->createArchiveBlobEntry('2013-01-04', array(
            $recordName2 => 'test04',
            $recordName2 . '_5' => 'subtable45',
            $recordName2 . '_6' => 'subtable6'
        ));
        $this->createArchiveBlobEntry('2013-01-06', array(
            $recordName2 => 'test06',
            $chunk0_2 => serialize(array())
        ));
    }

    private function assertArchiveBlob(PiwikArchive\DataCollection $dataCollection, $date, $expectedBlob)
    {
        $dateIndex  = $date . ',' . $date;
        $dataArrays = $dataCollection->get(1, $dateIndex);

        if (!empty($expectedBlob) && 0 !== reset($expectedBlob)) {
            $this->assertNotEmpty($dataArrays['_metadata']['ts_archived']);
            $dataArrays['_metadata']['ts_archived'] = true;
            unset($dataArrays['_metadata']);
        }

        $this->assertEquals($expectedBlob, $dataArrays);
    }

    private function createArchiveBlobEntry($date, $blobs)
    {
        $oPeriod = PeriodFactory::makePeriodFromQueryParams('UTC', 'day', $date);

        $segment = new Segment(false, array(1));
        $params  = new Parameters(new Site(1), $oPeriod, $segment);
        $writer  = new ArchiveWriter($params, false);
        $writer->initNewArchive();
        foreach ($blobs as $name => $blob) {
            $writer->insertBlobRecord($name, $blob);
        }
        $writer->finalizeArchive();
    }

    private function assertArchiveTablesAreNotEmpty($tableMonth)
    {
        $this->assertNotEquals(0, $this->getRangeArchiveTableCount('archive_numeric', $tableMonth));
    }

    private function assertArchiveTablesAreEmpty($tableMonth)
    {
        $this->assertEquals(0, $this->getRangeArchiveTableCount('archive_numeric', $tableMonth));
        $this->assertEquals(0, $this->getRangeArchiveTableCount('archive_blob', $tableMonth));
    }

    private function getRangeArchiveTableCount($tableType, $tableMonth)
    {
        $table = Common::prefixTable($tableType . '_' . $tableMonth);
        return Db::fetchOne("SELECT COUNT(*) FROM $table WHERE period = " . Piwik::$idPeriods['range']);
    }

    private function initiateArchivingForRange()
    {
        $archive = $this->getArchive('range');
        return $archive->getNumeric('nb_visits');
    }

    private function archiveDataForIndividualDays()
    {
        $archive = $this->getArchive('day');
        return $archive->getNumeric('nb_visits');
    }

    private function getArchive($period, $day = '2010-03-04,2010-03-07')
    {
        return Archive::build(self::$fixture->idSite, $period, $day);
    }
}

ArchiveTest::$fixture = new OneVisitorTwoVisits();