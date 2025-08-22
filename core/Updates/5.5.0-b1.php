<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Option;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 5.5.0-b1
 */
class Updates_5_5_0_b1 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    /**
     * How many annotations in one insert per site id
     *
     * @var int
     */
    private $chunkSize = 50;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Migrations
     *
     * @param Updater $updater
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];

        // create new annotations table
        $migrations[] = $this->migration->db->createTable('annotations', [
                'id' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
                'idsite' => 'INTEGER UNSIGNED NOT NULL',
                'date' => 'DATETIME NOT NULL',
                'note' => 'TEXT NOT NULL',
                'starred' => 'TINYINT(1) NOT NULL DEFAULT 0',
                'user' => 'VARCHAR(100) NOT NULL',
            ], $primaryKey = 'id');
        $migrations[] = $this->migration->db->addIndex('annotations', ['idsite', 'date']);

        // insert values from options table
        foreach ($this->getAnnotationInsertsAndBindValues() as $migrationEntry) {
            $migrations[] = $this->migration->db->boundSql($migrationEntry['sql'], $migrationEntry['bind']);
        }

        // delete legacy options
        // TODO: uncomment when we've updated the annotations mechanism to also read and write using the new table
        // $migrations[] = $this->migration->db->sql($this->removeLegacyValuesFromOptionsTableSql());

        return $migrations;
    }

    private function getAnnotationsForSite(int $idSite): array
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

    private function getAnnotationInsertsAndBindValues(): array
    {
        $table = Common::prefixTable('annotations');
        $data = [];

        $model = new Model();
        foreach ($model->getSitesId() as $siteID) {
            $annotations = $this->getAnnotationsForSite($siteID);
            $chunks = array_chunk($annotations, $this->chunkSize);

            foreach ($chunks as $chunk) {
                $bindValues = [];
                $placeholders = [];
                foreach ($chunk as $annotation) {
                    $bindValues[] = $values = [
                        $siteID,
                        $annotation['date'],
                        $annotation['note'],
                        $annotation['starred'],
                        $annotation['user'],
                    ];
                    $placeholders[] = Common::getSqlStringFieldsArray($values);
                }

                // chunk always has aray items, so it's safe to assume we have some bind values and placeholders
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

    private function removeLegacyValuesFromOptionsTableSql(): string
    {
        return sprintf("DELETE FROM `%s` WHERE `option_name` LIKE '%%_annotations'", Common::prefixTable('option'));
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
