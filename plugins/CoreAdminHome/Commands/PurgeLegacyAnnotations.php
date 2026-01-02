<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Command to purge annotations from option table after migrating them to a separate db table.
 */
class PurgeLegacyAnnotations extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:matomo550-purge-legacy-annotations');
        $this->setDescription(
            'Only needed for Matomo 5.5.0-b2 upgrade. ' .
            'Purge legacy annotations from option table after migrating them to a separate annotations table.'
        );
    }

    protected function doExecute(): int
    {
        self::purge();
        $this->getOutput()->writeln('Done');

        return self::SUCCESS;
    }

    public static function purge(): void
    {
        $migration = StaticContainer::get(MigrationFactory::class);
        $migrations = [];

        // remove legacy option table values
        $migrations[] = $migration->db->sql(self::removeLegacyValuesFromOptionsTableSql());

        $updater = StaticContainer::get(Updater::class);
        $updater->executeMigrations(__FILE__, $migrations);
    }

    private static function removeLegacyValuesFromOptionsTableSql(): string
    {
        return sprintf("DELETE FROM `%s` WHERE `option_name` LIKE '%%_annotations'", Common::prefixTable('option'));
    }
}
