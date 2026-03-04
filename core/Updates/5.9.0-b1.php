<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Plugins\CoreAdminHome\Commands\MigrateUserScopedSettings;
use Piwik\Updater;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updates;

class Updates_5_9_0_b1 extends Updates
{
    public function getMigrations(Updater $updater)
    {
        return [
            new CustomMigration(
                [MigrateUserScopedSettings::class, 'migrate'],
                './console core:matomo590-migrate-user-scoped-settings'
            ),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
