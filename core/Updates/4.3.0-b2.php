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
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Db;
use Piwik\Common;
use Piwik\Log;

/**
 * Update for version 4.3.0-b2.
 */
class Updates_4_3_0_b2 extends PiwikUpdates
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
        $migrations = [];

        $migrations[] = $this->migration->db->addColumn('segment', 'hash', 'CHAR(32) NULL', 'definition');

        $segmentTable = Common::prefixTable('segment');
        $segments = Db::fetchAll('SELECT idsegment, definition from ' . $segmentTable);
        foreach ($segments as $segment) {
            $hash = md5(urlencode($segment['definition']));
            $migrations[] = $this->migration->db->sql("UPDATE `$segmentTable` SET `hash` = '$hash' WHERE `idsegment` = '{$segment['idsegment']}'");
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
