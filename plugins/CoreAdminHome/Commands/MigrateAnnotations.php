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
use Piwik\Option;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Command to migrate annotations from option table to a separate db table.
 */
class MigrateAnnotations extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:matomo550-migrate-annotations');
        $this->addOptionalValueOption('chunk-size', null, 'How many annotations to migrate per SQL insert per site', 20);
        $this->setDescription(
            'Only needed for Matomo 5.5.0-b2 upgrade. ' .
            'Migrate annotations from option table to a separate annotations table. ' .
            'By default creates inserts of 20 annotations per SQL insert per site. You can lower this if you have annotations with long content.'
        );
    }

    protected function doExecute(): int
    {
        $chunkSize = $this->getInput()->getOption('chunk-size');
        $migrated = self::migrate($chunkSize);
        $this->getOutput()->writeln($migrated ? 'Done' : 'All existing annotations already migrated.');

        return self::SUCCESS;
    }

    public static function migrate(int $chunkSize = 20): bool
    {
        $migration = StaticContainer::get(MigrationFactory::class);
        $migrations = [];

        // create new annotations table
        $migrations[] = $migration->db->createTable('annotations', [
            'id' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            'idsite' => 'INTEGER UNSIGNED NOT NULL',
            'date' => 'DATETIME NOT NULL',
            'note' => 'TEXT NOT NULL',
            'starred' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'user' => 'VARCHAR(100) NOT NULL',
        ], $primaryKey = 'id');
        $migrations[] = $migration->db->addIndex('annotations', ['idsite', 'date']);

        // insert values from options table
        $migrated = false;
        foreach (self::getAnnotationInsertsAndBindValues($chunkSize) as $migrationEntry) {
            $migrations[] = $migration->db->boundSql($migrationEntry['sql'], $migrationEntry['bind']);
            $migrated = true;
        }

        $updater = StaticContainer::get(Updater::class);
        $updater->executeMigrations(__FILE__, $migrations);

        return $migrated;
    }

    private static function getAnnotationsForSite(int $idSite): array
    {
        $optionName = sprintf('%s_annotations', $idSite);
        $serialized = Option::get($optionName);

        if ($serialized !== false) {
            $result = Common::safe_unserialize($serialized);
            if (!empty($result)) {
                return $result;
            }
        }

        return [];
    }

    private static function getAnnotationInsertsAndBindValues(int $chunkSize): array
    {
        $table = Common::prefixTable('annotations');
        $data = [];

        $model = new Model();
        foreach ($model->getSitesId() as $siteID) {
            $annotations = self::getAnnotationsForSite($siteID);
            $chunks = array_chunk($annotations, $chunkSize);

            foreach ($chunks as $chunk) {
                $bindValues = [];
                $placeholders = [];
                foreach ($chunk as $annotation) {
                    $bindValues[] = $values = [
                        $siteID,
                        $annotation['date'],
                        Common::unsanitizeInputValue($annotation['note']),
                        $annotation['starred'],
                        $annotation['user'],
                    ];
                    $placeholders[] = Common::getSqlStringFieldsArray($values);
                }

                // chunk always has array items, so it's safe to assume we have some bind values and placeholders
                $sql = sprintf(
                    'INSERT INTO `%s` (`idsite`, `date`, `note`, `starred`, `user`) VALUES (%s)',
                    $table,
                    implode('), (', $placeholders)
                );
                $data[] = ['sql' => $sql, 'bind' => Common::flattenArray($bindValues)];
            }
        }

        return $data;
    }
}
