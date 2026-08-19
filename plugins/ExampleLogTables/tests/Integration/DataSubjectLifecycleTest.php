<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\tests\Integration;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\LogTablesProvider;
use Piwik\Plugins\ExampleLogTables\Dao\CustomGroupLog;
use Piwik\Plugins\ExampleLogTables\Dao\CustomUserLog;
use Piwik\Plugins\ExampleLogTables\tests\Fixtures\VisitsWithUserIdAndCustomData;
use Piwik\Plugins\PrivacyManager\LogDataPurger;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Proves that the plugin's own tables are covered by Matomo's GDPR and retention features.
 *
 * The plugin subscribes to none of the `PrivacyManager.*` events and implements no erasure code.
 * Everything asserted here follows from the two classes in `Tracker/LogTable/`: `LogTablesProvider`
 * discovers them by location, and `PrivacyManager` drives subject export, subject deletion and log
 * retention off that one list.
 *
 * The chain this exercises is two hops long with no `idvisit` column anywhere in it -- the group
 * table joins the user table, which joins `log_visit` on `user_id`. A table core cannot resolve a
 * path for is skipped silently by the export and makes the deletion throw, so this test is what
 * turns "the declarations look right" into "the declarations work".
 *
 * @group ExampleLogTables
 * @group Plugins
 */
class DataSubjectLifecycleTest extends IntegrationTestCase
{
    /**
     * @var VisitsWithUserIdAndCustomData
     */
    public static $fixture; // initialized below class definition

    private DataSubjects $dataSubjects;

    /**
     * The fixture backdates its visits, which the tracker only accepts with an authenticated
     * token, so this test needs the super user IntegrationTestCase otherwise skips.
     *
     * @param \Piwik\Tests\Framework\Fixture $fixture
     */
    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->createSuperUser = true;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->dataSubjects = new DataSubjects(StaticContainer::get(LogTablesProvider::class));
    }

    public function testTrackingFillsTheCustomLogTables(): void
    {
        $this->assertSame(
            [
                ['user_id' => 'user1', 'gender' => 'men', 'group_name' => 'admin'],
                ['user_id' => 'user2', 'gender' => 'women', 'group_name' => 'user'],
                ['user_id' => 'user3', 'gender' => 'women', 'group_name' => 'admin'],
                ['user_id' => 'user4', 'gender' => 'men', 'group_name' => ''],
            ],
            $this->getRows(CustomUserLog::TABLE_NAME, 'user_id')
        );

        $this->assertSame(
            [
                ['group_name' => 'admin', 'is_admin' => 1],
                ['group_name' => 'user', 'is_admin' => 0],
            ],
            $this->getRows(CustomGroupLog::TABLE_NAME, 'group_name')
        );
    }

    public function testExportIncludesTheCustomTableRows(): void
    {
        $export = $this->dataSubjects->exportDataSubjects($this->getVisitsOf('user1'));

        $this->assertArrayHasKey(
            CustomUserLog::TABLE_NAME,
            $export,
            'the user table was skipped, so core could not join it'
        );
        $this->assertArrayHasKey(
            CustomGroupLog::TABLE_NAME,
            $export,
            'the group table was skipped, so the second hop does not resolve'
        );

        // Two things to note. The export joins each table to the visits being exported and does not
        // deduplicate, so a table holding one row per user appears once per visit of that user; and
        // it formats each column through the Dimension that declares it, which is why is_admin
        // reads "Yes" rather than 1. Columns come out sorted by name.
        $this->assertSame(
            [['gender' => 'men', 'group_name' => 'admin', 'user_id' => 'user1']],
            $this->distinctRows($export[CustomUserLog::TABLE_NAME])
        );
        $this->assertSame(
            [['group_name' => 'admin', 'is_admin' => Piwik::translate('General_Yes')]],
            $this->distinctRows($export[CustomGroupLog::TABLE_NAME])
        );
    }

    public function testExportDoesNotLeakAnotherSubjectsRows(): void
    {
        $export = $this->dataSubjects->exportDataSubjects($this->getVisitsOf('user2'));

        $this->assertSame(
            ['user2'],
            array_column($this->distinctRows($export[CustomUserLog::TABLE_NAME]), 'user_id')
        );
        $this->assertSame(
            ['user'],
            array_column($this->distinctRows($export[CustomGroupLog::TABLE_NAME]), 'group_name')
        );
    }

    public function testDeleteRemovesTheSubjectsRowsFromBothCustomTables(): void
    {
        $deleted = $this->dataSubjects->deleteDataSubjects($this->getVisitsOf('user1'));

        $this->assertSame(1, $deleted[CustomUserLog::TABLE_NAME]);
        $this->assertSame(1, $deleted[CustomGroupLog::TABLE_NAME]);

        $this->assertSame(
            [
                ['user_id' => 'user2', 'gender' => 'women', 'group_name' => 'user'],
                ['user_id' => 'user3', 'gender' => 'women', 'group_name' => 'admin'],
                ['user_id' => 'user4', 'gender' => 'men', 'group_name' => ''],
            ],
            $this->getRows(CustomUserLog::TABLE_NAME, 'user_id')
        );

        // The admin group row goes with the subject, even though user3 also belongs to that group.
        // Core deletes every row it can reach from the visits being erased, which is the safe
        // default for a compliance feature: it never leaves personal data behind. It also means a
        // table whose rows are shared between subjects should not declare a join into the subject
        // chain. Here that is acceptable because the row is reference data the tracker rewrites on
        // user3's next request -- see the README.
        $this->assertSame(
            [['group_name' => 'user', 'is_admin' => 0]],
            $this->getRows(CustomGroupLog::TABLE_NAME, 'group_name')
        );
    }

    public function testDeleteLeavesTheOtherSubjectsRowsAlone(): void
    {
        $this->dataSubjects->deleteDataSubjects($this->getVisitsOf('user4'));

        $this->assertSame(
            [
                ['user_id' => 'user1', 'gender' => 'men', 'group_name' => 'admin'],
                ['user_id' => 'user2', 'gender' => 'women', 'group_name' => 'user'],
                ['user_id' => 'user3', 'gender' => 'women', 'group_name' => 'admin'],
            ],
            $this->getRows(CustomUserLog::TABLE_NAME, 'user_id')
        );

        // user4 has no group, so nothing links them to the group table and nothing there is touched.
        $this->assertCount(2, $this->getRows(CustomGroupLog::TABLE_NAME, 'group_name'));
    }

    /**
     * Log retention is the fourth feature the declarations buy, and the one whose failure would be
     * least visible: it deletes through the same code as subject deletion, so the rows do go -- but
     * the purge also asks every declared log table for its id column and interpolates that name into
     * SQL without quoting it. A table whose id column collides with a reserved word therefore breaks
     * the whole site's raw log purge, not just this plugin's rows.
     */
    public function testRetentionPurgeReachesBothCustomTablesAndDoesNotBreakOnTheirColumnNames(): void
    {
        if (!Db::isLockPrivilegeGranted()) {
            self::markTestSkipped('deleting unused log actions requires the LOCK TABLES privilege');
        }

        // The fixture's visits are years old, so a one-day retention window covers all of them.
        StaticContainer::get(LogDataPurger::class)->purgeData(1, true);

        $this->assertSame([], $this->getRows(CustomUserLog::TABLE_NAME, 'user_id'));
        $this->assertSame([], $this->getRows(CustomGroupLog::TABLE_NAME, 'group_name'));
    }

    /**
     * @return array<array{idsite: string, idvisit: string}>
     */
    private function getVisitsOf(string $userId): array
    {
        $visits = Db::fetchAll(
            'SELECT idsite, idvisit FROM ' . Common::prefixTable('log_visit') . ' WHERE user_id = ?',
            [$userId]
        );

        $this->assertNotEmpty($visits, 'no visits tracked for ' . $userId);

        return $visits;
    }

    /**
     * Drops the idsite column the export adds to tables that have none, and collapses the
     * duplicates the visit join produces.
     *
     * @param array<array<string, mixed>> $rows
     * @return array<array<string, mixed>>
     */
    private function distinctRows(array $rows): array
    {
        $rows = array_map(function (array $row): array {
            unset($row['idsite']);

            return $row;
        }, $rows);

        return array_values(array_unique($rows, SORT_REGULAR));
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getRows(string $table, string $orderBy): array
    {
        return Db::fetchAll('SELECT * FROM ' . Common::prefixTable($table) . ' ORDER BY ' . $orderBy);
    }
}

DataSubjectLifecycleTest::$fixture = new VisitsWithUserIdAndCustomData();
