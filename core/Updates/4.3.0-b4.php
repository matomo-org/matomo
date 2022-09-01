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
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Db;
use Piwik\Common;

/**
 * Update for version 4.3.0-b4.
 */
class Updates_4_3_0_b4 extends PiwikUpdates
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

        $segmentTable = Common::prefixTable('segment');
        $segments = Db::fetchAll("SELECT * FROM $segmentTable");
        foreach ($segments as $segment) {
            if (empty($segment['hash'])) {
                $hash = md5(urldecode($segment['definition']));
                $migrations[] = $this->migration->db->sql("UPDATE `$segmentTable` SET `hash` = '$hash' WHERE `idsegment` = '{$segment['idsegment']}'");
            }
        }
        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
