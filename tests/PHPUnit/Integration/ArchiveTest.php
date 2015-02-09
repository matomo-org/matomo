<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\Archive;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

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
        $archive = Archive::build(self::$fixture->idSite, 'range', '2010-03-04,2010-03-07');
        return $archive->getNumeric('nb_visits');
    }

    private function archiveDataForIndividualDays()
    {
        $archive = Archive::build(self::$fixture->idSite, 'day', '2010-03-04,2010-03-07');
        return $archive->getNumeric('nb_visits');
    }
}

ArchiveTest::$fixture = new OneVisitorTwoVisits();