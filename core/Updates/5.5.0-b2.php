<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Plugins\CoreAdminHome\Commands\MigrateAnnotations;
use Piwik\Plugins\CoreAdminHome\Commands\PurgeLegacyAnnotations;
use Piwik\Updater;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;

/**
 * Update for version 5.5.0-b2
 */
class Updates_5_5_0_b2 extends PiwikUpdates
{
    /**
     * Migrations
     *
     * @param Updater $updater
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];

        $migrateCmd = './console core:matomo550-migrate-annotations';
        $annotationsMigrate = new CustomMigration([MigrateAnnotations::class, 'migrate'], $migrateCmd);
        $migrations[] = $annotationsMigrate;

        $purgeCmd = './console core:matomo550-purge-legacy-annotations';
        $annotationsPurge = new CustomMigration([PurgeLegacyAnnotations::class, 'purge'], $purgeCmd);
        $migrations[] = $annotationsPurge;

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
