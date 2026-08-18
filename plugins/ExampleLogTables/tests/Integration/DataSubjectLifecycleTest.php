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
use Piwik\Plugins\ExampleLogTables\tests\Fixtures\VisitsWithUserIdAndCustomData;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Proves that the plugin's own tables are covered by Matomo's GDPR features.
 *
 * The plugin subscribes to none of the `PrivacyManager.*` events and implements no erasure code.
 * Everything asserted here follows from the two classes in `Tracker/LogTable/`: `LogTablesProvider`
 * discovers them by location, and `PrivacyManager` drives subject export and subject deletion off
 * that one list.
 *
 * The chain this exercises is two hops long with no `idvisit` column anywhere in it --
 * `log_group` joins `log_custom`, which joins `log_visit` on `user_id`. A table core cannot resolve
 * a path for is skipped silently by the export and makes the deletion throw, so this test is what
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
                ['user_id' => 'user1', 'gender' => 'men', 'group' => 'admin'],
                ['user_id' => 'user2', 'gender' => 'women', 'group' => 'user'],
                ['user_id' => 'user3', 'gender' => 'women', 'group' => 'admin'],
                ['user_id' => 'user4', 'gender' => 'men', 'group' => ''],
            ],
            $this->getRows('log_custom', 'user_id')
        );

        $this->assertSame(
            [
                ['group' => 'admin', 'is_admin' => 1],
                ['group' => 'user', 'is_admin' => 0],
            ],
            $this->getRows('log_group', '`group`')
        );
    }

    public function testExportIncludesTheCustomTableRows(): void
    {
        $export = $this->dataSubjects->exportDataSubjects($this->getVisitsOf('user1'));

        $this->assertArrayHasKey('log_custom', $export, 'log_custom was skipped, which means core could not join it');
        $this->assertArrayHasKey('log_group', $export, 'log_group was skipped, which means the second hop does not resolve');

        // Two things to note. The export joins each table to the visits being exported and does not
        // deduplicate, so a table holding one row per user appears once per visit of that user; and
        // it formats each column through the Dimension that declares it, which is why is_admin
        // reads "Yes" rather than 1. Columns come out sorted by name.
        $this->assertSame([['gender' => 'men', 'group' => 'admin', 'user_id' => 'user1']], $this->distinctRows($export['log_custom']));
        $this->assertSame([['group' => 'admin', 'is_admin' => Piwik::translate('General_Yes')]], $this->distinctRows($export['log_group']));
    }

    public function testExportDoesNotLeakAnotherSubjectsRows(): void
    {
        $export = $this->dataSubjects->exportDataSubjects($this->getVisitsOf('user2'));

        $this->assertSame(['user2'], array_column($this->distinctRows($export['log_custom']), 'user_id'));
        $this->assertSame(['user'], array_column($this->distinctRows($export['log_group']), 'group'));
    }

    public function testDeleteRemovesTheSubjectsRowsFromBothCustomTables(): void
    {
        $deleted = $this->dataSubjects->deleteDataSubjects($this->getVisitsOf('user1'));

        $this->assertSame(1, $deleted['log_custom']);
        $this->assertSame(1, $deleted['log_group']);

        $this->assertSame(
            [
                ['user_id' => 'user2', 'gender' => 'women', 'group' => 'user'],
                ['user_id' => 'user3', 'gender' => 'women', 'group' => 'admin'],
                ['user_id' => 'user4', 'gender' => 'men', 'group' => ''],
            ],
            $this->getRows('log_custom', 'user_id')
        );

        // The admin group row goes with the subject, even though user3 also belongs to that group.
        // Core deletes every row it can reach from the visits being erased, which is the safe
        // default for a compliance feature: it never leaves personal data behind. It also means a
        // table whose rows are shared between subjects should not declare a join into the subject
        // chain. Here that is acceptable because the row is reference data the tracker rewrites on
        // user3's next request -- see the README.
        $this->assertSame([['group' => 'user', 'is_admin' => 0]], $this->getRows('log_group', '`group`'));
    }

    public function testDeleteLeavesTheOtherSubjectsRowsAlone(): void
    {
        $this->dataSubjects->deleteDataSubjects($this->getVisitsOf('user4'));

        $this->assertSame(
            [
                ['user_id' => 'user1', 'gender' => 'men', 'group' => 'admin'],
                ['user_id' => 'user2', 'gender' => 'women', 'group' => 'user'],
                ['user_id' => 'user3', 'gender' => 'women', 'group' => 'admin'],
            ],
            $this->getRows('log_custom', 'user_id')
        );

        // user4 has no group, so nothing links them to log_group and nothing there is touched.
        $this->assertCount(2, $this->getRows('log_group', '`group`'));
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
