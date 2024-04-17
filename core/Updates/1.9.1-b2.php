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
class Updates_1_9_1_b2 extends Updates
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
            $this->migration->db->dropColumn('site', 'feedburnerName')
        );
    }

    public function doUpdate(Updater $updater)
    {
        // manually remove ExampleFeedburner column
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        // remove ExampleFeedburner plugin
        $pluginToDelete = 'ExampleFeedburner';
        self::deletePluginFromConfigFile($pluginToDelete);
    }
}
