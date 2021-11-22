<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyAPI;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.1.0-b1.
 */
class Updates_4_7_0_b1 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * @param Updater $updater
     * @return Migration\Db[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];
        $migrations[] = $this->migration->db->addColumns('user', [
          'invite_status'=>'varchar(100) default NULL',
          'invite_at'=>'datetime NULL']);

        return $migrations;

    }

    /**
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
