<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Plugins\CoreAdminHome\Commands\MigrateCompliancePolicyEnforcement;
use Piwik\Updater;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updates;

class Updates_6_0_0_b2 extends Updates
{
    public function getMigrations(Updater $updater)
    {
        if (!MigrateCompliancePolicyEnforcement::hasLegacyPolicyFlags()) {
            return [];
        }

        return [
            new CustomMigration(
                [MigrateCompliancePolicyEnforcement::class, 'migrate'],
                './console core:matomo600-migrate-compliance-policy-enforcement'
            ),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
