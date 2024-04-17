<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_2_5_rc1 extends Updates
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
        return array(
            $this->migration->db->addColumn('goal', 'allow_multiple', 'TINYINT(4) NOT NULL', 'case_sensitive'),
            $this->migration->db->sql(
                'ALTER TABLE `' . Common::prefixTable('log_conversion') . '`
                    ADD buster int unsigned NOT NULL AFTER revenue,
                    DROP PRIMARY KEY,
                    ADD PRIMARY KEY (idvisit, idgoal, buster)',
                Updater\Migration\Db::ERROR_CODE_DUPLICATE_COLUMN
            ),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
