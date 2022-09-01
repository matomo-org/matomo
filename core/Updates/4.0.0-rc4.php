<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\DbHelper;
use Piwik\Plugin\ReleaseChannels;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.0.0-rc4.
 */
class Updates_4_0_0_rc4 extends PiwikUpdates
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

        $migrations[] = $this->migration->plugin->deactivate('ExampleTheme');
      
        $channel = StaticContainer::get(ReleaseChannels::class)->getActiveReleaseChannel()->getId();
        $isBeta = stripos($channel, 'beta') !== false;

        if ($isBeta) {
            $dates = ['2020-01-01', '2020-11-01', '2020-10-01'];
            foreach ($dates as $date) {
                $date = Date::factory($date);
                $numericTable = ArchiveTableCreator::getBlobTable($date);
                $blobTable = ArchiveTableCreator::getNumericTable($date);

                if (DbHelper::tableExists($blobTable) && DbHelper::tableExists($numericTable)) {
                    $migrations[] = $this->migration->db->sql(
                        "DELETE FROM `$blobTable` WHERE idarchive NOT IN (SELECT idarchive FROM `$numericTable`)", []);
                }
            }
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

}
