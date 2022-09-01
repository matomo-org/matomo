<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_1_14 extends Updates
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
            $this->migration->db->changeColumnTypes('log_visit', array(
                'config_os_version' => 'VARCHAR( 100 ) DEFAULT NULL',
                'config_device_type' => 'VARCHAR( 100 ) DEFAULT NULL'
            ))
        );
    }

    static function isMajorUpdate()
    {
        return true;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

}
