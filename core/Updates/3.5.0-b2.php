<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_3_5_0_b2 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $columns = array(
            'idlogdata_anonymization' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            'idsites' => 'TEXT NULL DEFAULT NULL',
            'date_start' => 'DATETIME NOT NULL',
            'date_end' => 'DATETIME NOT NULL',
            'anonymize_ip' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
            'anonymize_location' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
            'anonymize_userid' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
            'unset_visit_columns' => "TEXT NOT NULL DEFAULT ''",
            'unset_link_visit_action_columns' => "TEXT NOT NULL DEFAULT ''",
            'output' => 'MEDIUMTEXT NULL DEFAULT NULL',
            'scheduled_date' => 'DATETIME NULL',
            'job_start_date' => 'DATETIME NULL',
            'job_finish_date' => 'DATETIME NULL',
            'requester' => "VARCHAR(100) NOT NULL DEFAULT ''"
        );
        return array(
            $this->migration->db->createTable(LogDataAnonymizations::getDbTableName(), $columns, $primary = 'idlogdata_anonymization'),
            $this->migration->db->addIndex(LogDataAnonymizations::getDbTableName(), array('job_start_date'))
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
