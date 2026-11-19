<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updates\Updates_6_1_0_b3;

require_once __DIR__ . '/../../../../core/Updates/6.1.0-b3.php';

/**
 * @group Updates
 */
class Updates610b3Test extends IntegrationTestCase
{
    public function testAddsTheStarredColumnLikeAFreshInstallHasIt(): void
    {
        $table = Common::prefixTable('goal');
        $columnOfFreshInstall = $this->getStarredColumn($table);
        self::assertNotNull($columnOfFreshInstall);

        Fixture::createSuperUser();
        $idSite = Fixture::createWebsite('2015-01-01 00:00:00');

        try {
            Db::exec("ALTER TABLE `$table` DROP COLUMN `starred`");

            // a goal created before the update
            Db::query(
                "INSERT INTO `$table` (idsite, idgoal, name, match_attribute, pattern, pattern_type, case_sensitive,
                                       allow_multiple, revenue, deleted)
                 VALUES (?, 1, 'Existing goal', 'url', 'thank-you', 'contains', 0, 0, 0, 0)",
                [$idSite]
            );

            $this->runUpdate();
            // running it again changes nothing
            $this->runUpdate();

            self::assertSame($columnOfFreshInstall, $this->getStarredColumn($table));

            $columns = array_column(Db::fetchAll("SHOW COLUMNS FROM `$table`"), 'Field');
            self::assertSame('starred', end($columns));
        } finally {
            if (null === $this->getStarredColumn($table)) {
                $this->runUpdate();
            }
        }

        // existing goals are not starred
        $goal = GoalsApi::getInstance()->getGoal($idSite, 1);
        self::assertIsArray($goal);
        self::assertFalse($goal['starred']);
    }

    private function runUpdate(): void
    {
        /** @var Updates_6_1_0_b3 $update */
        $update = StaticContainer::get(Updates_6_1_0_b3::class);
        $update->doUpdate(new Updater());
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getStarredColumn(string $table): ?array
    {
        $column = Db::fetchRow("SHOW COLUMNS FROM `$table` LIKE 'starred'");

        return empty($column) ? null : $column;
    }
}
