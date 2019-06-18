<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ScheduledReports;

use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_3_10_0_b3 extends PiwikUpdates
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
        $formatColumn = $this->migration->db->changeColumnType('report', 'format', 'VARCHAR(16)');
        $typeColumn = $this->migration->db->changeColumnType('report', 'type', 'VARCHAR(16)');

        $tokenColumn = $this->migration->db->addColumn('user', 'notification_token', 'CHAR(32)');

        return array(
            $formatColumn,
            $typeColumn,
            $tokenColumn
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
