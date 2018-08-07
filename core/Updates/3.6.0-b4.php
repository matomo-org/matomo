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
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;
use Piwik\Updater;

/**
 * Update for version 3.6.0-b4.
 */
class Updates_3_6_0_b4 extends Updates
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
        // use php date since mysql date/time might be different and might lock users out for a while. and subtract days just to be safe.
        $passwordModified = Date::factory('now')->subDay(14);
        return array(
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('user') . ' SET ts_password_modified = ' . $passwordModified),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
