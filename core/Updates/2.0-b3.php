<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_2_0_b3 extends Updates
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
            $this->migration->db->addColumn('log_visit', 'visit_total_events', 'SMALLINT(5) UNSIGNED NOT NULL', 'visit_total_searches'),
            $this->migration->db->addColumns('log_link_visit_action', array(
                'idaction_event_category' => 'INTEGER(10) UNSIGNED',
                'idaction_event_action' => 'INTEGER(10) UNSIGNED'
            ), $placeAfter = 'idaction_name_ref')
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('Events');
        } catch (\Exception $e) {
        }
    }
}
