<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Plugins\SitesManager\Model;
use Piwik\Tracker\Request;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Common;

/**
 * Update for version 4.4.0-b2.
 */
class Updates_4_4_0_b2 extends PiwikUpdates
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

        $table = Common::prefixTable('option');

        $model = new Model();
        $siteIds = $model->getSitesId();
        foreach ($siteIds as $id) {
            $migrations[] = $this->migration->db->boundSql("INSERT INTO `$table` (option_name, option_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE option_value = '1'",
                [Request::HAS_USED_CDT_WHEN_TRACKING_OPTION_NAME_PREFIX . $id, '1']);
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
