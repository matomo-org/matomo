<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration\Model;

use Piwik\DbHelper;
use Piwik\Plugins\PrivacyManager\Dao\LogDataAnonymizer;
use Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Class LogDataAnonymizationsTest
 *
 * @group Plugins
 */
class LogDataAnonymizationsTest extends IntegrationTestCase
{
    /**
     * @var LogDataAnonymizations
     */
    private $dao;

    private $tableName;

    public function setUp(): void
    {
        parent::setUp();

        $this->tableName = LogDataAnonymizations::getDbTableName();
        $this->dao = new LogDataAnonymizations(new LogDataAnonymizer());
    }

    public function test_shouldInstallTable()
    {
        $columns = DbHelper::getTableColumns($this->tableName);
        $columns = array_keys($columns);
        $this->assertEquals(array('idlogdata_anonymization', 'idsites', 'date_start', 'date_end', 'anonymize_ip', 'anonymize_location', 'anonymize_userid', 'unset_visit_columns', 'unset_link_visit_action_columns', 'output', 'scheduled_date', 'job_start_date', 'job_finish_date', 'requester'), $columns);
    }

    public function test_shouldBeAbleToUninstallTable()
    {
        $this->expectException(\Zend_Db_Statement_Exception::class);
        $this->expectExceptionMessage('privacy_logdata_anonymizations');

        $this->dao->uninstall();

        try {
            DbHelper::getTableColumns($this->tableName);
            $this->fail('Did not uninstall privacy_logdata_anonymizations table');
        } catch (\Zend_Db_Statement_Exception $e) {
            $this->dao->install();
            throw $e;
        }

        $this->dao->install();
    }

    public function test_scheduleEntry_fails_whenNoDateGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorEmptyValue');

        $this->scheduleEntry(null, null);
    }

    public function test_scheduleEntry_fails_whenInvalidDateGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionInvalidDateFormat');

        $this->scheduleEntry(null, 'foobar');
    }

    public function test_scheduleEntry_fails_whenInvalidVisitColumnsGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The column "visitor_foobar_Baz" seems to not exist in log_visit');

        $this->scheduleEntry(null, '2018-01-02', false, false, false, ['visitor_localtime', 'visitor_foobar_Baz', 'config_device_type']);
    }

    public function test_scheduleEntry_fails_whenInvalidLinkVisitActionColumnsGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The column "idaction_foobar_baz" seems to not exist in log_link_visit_action');

        $this->scheduleEntry(null, '2018-01-02', false, false, false, [], ['idaction_event_category', 'idaction_foobar_baz']);
    }

    public function test_scheduleEntry_fails_whenNoWorkScheduled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Nothing is selected to be anonymized');

        $this->scheduleEntry(null, '2018-01-02', false, false, false, [], []);
    }

    public function test_scheduleEntry_success()
    {
        $id = $this->scheduleEntry(null, '2017-01-03', true, true, true, ['visitor_localtime', 'config_device_type'], ['idaction_event_category'], 'mylogin');
        $this->assertSame(1, $id);

        $entry = $this->dao->getEntry($id);
        $this->assertNotEmpty($entry['scheduled_date']);
        unset($entry['scheduled_date']);
        $this->assertSame(array(
            'idlogdata_anonymization' => 1,
            'idsites' => null,
            'date_start' => '2017-01-03 00:00:00',
            'date_end' => '2017-01-03 23:59:59',
            'anonymize_ip' => true,
            'anonymize_location' => true,
            'anonymize_userid' => true,
            'unset_visit_columns' => array ('visitor_localtime','config_device_type'),
            'unset_link_visit_action_columns' => array('idaction_event_category'),
            'output' => null,
            'job_start_date' => null,
            'job_finish_date' => null,
            'requester' => 'mylogin',
            'sites' => array ('All Websites')
        ), $entry);
    }

    public function test_scheduleEntry_successStartDirectlySetsDateAndAlsoTestingDifferentSettings()
    {
        $id = $this->scheduleStartedEntry(null, '2017-03-01,2018-04-06', false, true, false, [], false, 'mylogin2');

        $entry = $this->dao->getEntry($id);
        $this->assertNotEmpty($entry['scheduled_date']);
        $this->assertNotEmpty($entry['job_start_date']);
        $this->assertSame($entry['job_start_date'], $entry['scheduled_date']);
        unset($entry['scheduled_date']);
        unset($entry['job_start_date']);
        $this->assertSame(array(
            'idlogdata_anonymization' => 1,
            'idsites' => null,
            'date_start' => '2017-03-01 00:00:00',
            'date_end' => '2018-04-06 23:59:59',
            'anonymize_ip' => false,
            'anonymize_location' => true,
            'anonymize_userid' => false,
            'unset_visit_columns' => array (),
            'unset_link_visit_action_columns' => array(),
            'output' => null,
            'job_finish_date' => null,
            'requester' => 'mylogin2',
            'sites' => array ('All Websites')
        ), $entry);
    }

    public function test_scheduleEntry_failsInvalidDateRange()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Specified date range is invalid.');

        $this->scheduleStartedEntry(null, '2018-04-06,2017-03-01', false, true, [], false, 'mylogin2');
    }

    public function test_scheduleEntry_successIdSites()
    {
        $id = $this->scheduleEntry(['4', 5, 10, '402foo']);

        $entry = $this->dao->getEntry($id);
        $this->assertSame([4,5,10,402], $entry['idsites']);
        $this->assertSame(['Site ID: 4', 'Site ID: 5', 'Site ID: 10', 'Site ID: 402'], $entry['sites']);
    }

    public function test_scheduleEntry_success_increasesId()
    {
        $id = $this->scheduleEntry();
        $this->assertSame(1, $id);

        $id = $this->scheduleEntry();
        $this->assertSame(2, $id);
    }

    public function test_getAllEntries_returnsEmptyArrayWhenNoEntriesExist()
    {
        $this->assertSame(array(), $this->dao->getAllEntries());
    }

    public function test_getAllEntries_returnsAllEntriesThatExist()
    {
        $id1 = $this->scheduleEntry();
        $id2 = $this->scheduleEntry();
        $entries = $this->dao->getAllEntries();
        $this->assertCount(2, $entries);
        $this->assertDefaultEntry($entries[0], $id1);
        $this->assertDefaultEntry($entries[1], $id2);
    }

    public function test_getEntry_doesNotFindEntryWhenNoEntryExists()
    {
        $this->assertFalse($this->dao->getEntry(5));
    }

    public function test_getEntry_doesNotFindEntryWhenNotExists()
    {
        $this->scheduleEntry();
        $this->assertFalse($this->dao->getEntry(999));
    }

    public function test_getEntry_success_findsEntryAndFormatsIt()
    {
        $id = $this->scheduleEntry();
        $entry = $this->dao->getEntry($id);

        $this->assertDefaultEntry($entry, 1);
    }

    public function test_getNextScheduledAnonymizationId_returnsNoEntryWhenNoEntryExists()
    {
        $this->assertFalse($this->dao->getNextScheduledAnonymizationId());
    }

    public function test_getNextScheduledAnonymizationId_returnsNoEntryWhenOnlyStartedEntriesExist()
    {
        $this->scheduleStartedEntry();
        $this->scheduleStartedEntry();
        $this->assertFalse($this->dao->getNextScheduledAnonymizationId());
    }

    public function test_getNextScheduledAnonymizationId_returnsNextUnstartedEntry()
    {
        $this->scheduleStartedEntry();
        $id1 = $this->scheduleEntry();
        $this->scheduleStartedEntry();
        $id2 = $this->scheduleEntry();
        $this->assertSame($id1, $this->dao->getNextScheduledAnonymizationId());
        // returns it again when calling it again
        $this->assertSame($id1, $this->dao->getNextScheduledAnonymizationId());

        // now we execute the task
        $this->dao->executeScheduledEntry($id1);

        // should return next id
        $this->assertSame($id2, $this->dao->getNextScheduledAnonymizationId());
    }

    public function test_executeScheduledEntry_SimpleExecutionWithAllFeaturesEnabledWhenNoTrackedData_OtherTestsBeDoneInSystemTests()
    {
        $id = $this->scheduleEntry(null, '2017-01-03', true, true, true, ['visitor_localtime', 'config_device_type'], ['idaction_event_category'], 'mylogin');

        // now we execute the task
        $this->dao->executeScheduledEntry($id);

        $entry = $this->dao->getEntry($id);
        $this->assertNotEmpty($entry['scheduled_date']);
        $this->assertNotEmpty($entry['job_start_date']);
        $this->assertNotEmpty($entry['job_finish_date']);
        $this->assertSame($entry['job_start_date'], $entry['scheduled_date']);
        unset($entry['scheduled_date']);
        unset($entry['job_start_date']);
        unset($entry['job_finish_date']);
        $this->assertSame(array(
            'idlogdata_anonymization' => 1,
            'idsites' => null,
            'date_start' => '2017-01-03 00:00:00',
            'date_end' => '2017-01-03 23:59:59',
            'anonymize_ip' => true,
            'anonymize_location' => true,
            'anonymize_userid' => true,
            'unset_visit_columns' => array ('visitor_localtime','config_device_type'),
            'unset_link_visit_action_columns' => array('idaction_event_category'),
            'output' => "Running behaviour on all sites.
Applying this to visits between '2017-01-03 00:00:00' and '2017-01-03 23:59:59'.
Starting to anonymize visit information.
Number of anonymized IP and/or location and/or User ID: 0
Starting to unset log_visit table entries.
Number of unset log_visit table entries: 0
Starting to unset log_conversion table entries (if possible).
Number of unset log_conversion table entries: 0
Starting to unset log_link_visit_action table entries.
Number of unset log_link_visit_action table entries: 0
",
            'requester' => 'mylogin',
            'sites' => array ('All Websites')
        ), $entry);
    }

    private function assertDefaultEntry($entry, $expectedId)
    {
        $this->assertNotEmpty($entry['scheduled_date']);
        unset($entry['scheduled_date']);
        $this->assertSame(array(
            'idlogdata_anonymization' => $expectedId,
            'idsites' => null,
            'date_start' => '2018-01-01 00:00:00',
            'date_end' => '2018-01-01 23:59:59',
            'anonymize_ip' => true,
            'anonymize_location' => false,
            'anonymize_userid' => false,
            'unset_visit_columns' => array (),
            'unset_link_visit_action_columns' => array(),
            'output' => null,
            'job_start_date' => null,
            'job_finish_date' => null,
            'requester' => 'CLI',
            'sites' => array ('All Websites')
        ), $entry);
    }

    private function scheduleEntry($idSites = null, $dateString = '2018-01-01', $anonymizeIp = true, $anonymizeLocation = false, $anonymizeUserId = false, $unsetVisitColumns = [], $unsetLinkVisitActionColumns = [], $requester = 'CLI')
    {
        return $this->dao->scheduleEntry($requester, $idSites, $dateString, $anonymizeIp, $anonymizeLocation, $anonymizeUserId, $unsetVisitColumns, $unsetLinkVisitActionColumns, $start = false);
    }

    private function scheduleStartedEntry($idSites = null, $dateString = '2018-01-01', $anonymizeIp = true, $anonymizeLocation = false, $anonymizeUserId = false, $unsetVisitColumns = [], $unsetLinkVisitActionColumns = [], $requester = 'CLI')
    {
        return $this->dao->scheduleEntry($requester, $idSites, $dateString, $anonymizeIp, $anonymizeLocation, $anonymizeUserId, $unsetVisitColumns, $unsetLinkVisitActionColumns, $start = true);
    }
}
