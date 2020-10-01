<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.0.0-b3.
 */
class Updates_4_0_0_b3 extends PiwikUpdates
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

        $config = Config::getInstance();
        $general = $config->General;
        if (empty($general['login_whitelist_apply_to_reporting_api_requests'])) {
            $migrations[] = $this->migration->config->set('General', 'login_allowlist_apply_to_reporting_api_requests', '0');
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        $config = Config::getInstance();
        $general = $config->General;
        if (!empty($general['login_whitelist_ip'])) {
            // the migration->config->set does not support arrays yet so we do it here.
            $general['login_allowlist_ip'] = $general['login_whitelist_ip'];
            $config->General = $general;
            $config->forceSave();
        }

    }

}
