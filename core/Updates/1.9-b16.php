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
class Updates_1_9_b16 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public static function isMajorUpdate()
    {
        return true;
    }

    public function getMigrations(Updater $updater)
    {
        return array(
            $this->migration->db->changeColumnType('log_link_visit_action', 'idaction_url', 'INT( 10 ) UNSIGNED NULL DEFAULT NULL'),
            $this->migration->db->addColumn('log_visit', 'visit_total_searches', 'SMALLINT(5) UNSIGNED NOT NULL', 'visit_total_actions'),
            $this->migration->db->addColumns('site', array(
                'sitesearch' => 'TINYINT DEFAULT 1',
                'sitesearch_keyword_parameters' => 'TEXT NOT NULL',
                'sitesearch_category_parameters' => 'TEXT NOT NULL',
            ), 'excluded_parameters'),

            // enable Site Search for all websites, users can manually disable the setting
            $this->migration->db->sql('UPDATE `' . Common::prefixTable('site') . '` SET `sitesearch` = 1')
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
