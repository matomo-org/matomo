<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
class Updates_1_7_b1 extends Updates
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
            $this->migration->db->addColumn('pdf', 'aggregate_reports_format', 'TINYINT(1) NOT NULL', 'reports'),
            $this->migration->db->sql('UPDATE `' . Common::prefixTable('pdf') . '` SET `aggregate_reports_format` = 1')
        );
    }

    public function doUpdate(Updater $updater)
    {
        try {
            $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
        } catch (\Exception $e) {
        }
    }
}
