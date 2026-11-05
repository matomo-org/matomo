<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updates\Updates_6_1_0_b2;

require_once __DIR__ . '/../../../../core/Updates/6.1.0-b2.php';

/**
 * @group Updates
 */
class Updates610b2Test extends IntegrationTestCase
{
    public function testCreatesTheFailureTableWithTheCollationOfArchiveInvalidations(): void
    {
        $invalidationsTable = Common::prefixTable('archive_invalidations');
        $failuresTable = Common::prefixTable('archive_failures');

        $originalColumns = $this->getStringColumns($invalidationsTable);
        $charset = $originalColumns['name']['CHARACTER_SET_NAME'];
        $otherCollation = $originalColumns['name']['COLLATION_NAME'] === $charset . '_bin' ? $charset . '_general_ci' : $charset . '_bin';

        try {
            Db::exec("DROP TABLE `$failuresTable`");
            Db::exec("ALTER TABLE `$invalidationsTable` DROP INDEX index_idsite_status_date1_period");
            $this->changeInvalidationColumnCollations($charset, $otherCollation, $otherCollation);

            $this->runUpdate();
            // running it again changes nothing
            $this->runUpdate();

            $columns = $this->getStringColumns($failuresTable);
            $this->assertSame($otherCollation, $columns['name']['COLLATION_NAME']);
            $this->assertSame($otherCollation, $columns['report']['COLLATION_NAME']);

            $uniqueKeyColumns = array_column(Db::fetchAll("SHOW INDEX FROM `$failuresTable` WHERE Key_name = 'unique_archive'"), 'Column_name');
            $this->assertSame(['idsite', 'archive_hash', 'period', 'date1', 'date2'], $uniqueKeyColumns);

            $statusIndexColumns = array_column(Db::fetchAll("SHOW INDEX FROM `$invalidationsTable` WHERE Key_name = 'index_idsite_status_date1_period'"), 'Column_name');
            $this->assertSame(['idsite', 'status', 'date1', 'period'], $statusIndexColumns);
        } finally {
            $this->changeInvalidationColumnCollations(
                $charset,
                $originalColumns['name']['COLLATION_NAME'],
                $originalColumns['report']['COLLATION_NAME']
            );
            Db::exec("DROP TABLE IF EXISTS `$failuresTable`");
            $this->runUpdate();
        }

        $columns = $this->getStringColumns($failuresTable);
        $this->assertSame($originalColumns['name']['COLLATION_NAME'], $columns['name']['COLLATION_NAME']);
    }

    private function runUpdate(): void
    {
        /** @var Updates_6_1_0_b2 $update */
        $update = StaticContainer::get(Updates_6_1_0_b2::class);
        $update->doUpdate(new Updater());
    }

    private function changeInvalidationColumnCollations(string $charset, string $nameCollation, string $reportCollation): void
    {
        Db::exec("ALTER TABLE `" . Common::prefixTable('archive_invalidations') . "`
            MODIFY `name` VARCHAR(255) CHARACTER SET $charset COLLATE $nameCollation NOT NULL,
            MODIFY `report` VARCHAR(255) CHARACTER SET $charset COLLATE $reportCollation NULL");
    }

    private function getStringColumns(string $table): array
    {
        $columns = Db::fetchAll(
            "SELECT COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN ('name', 'report')",
            [$table]
        );

        return array_column($columns, null, 'COLUMN_NAME');
    }
}
