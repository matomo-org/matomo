<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Updater;
use Piwik\Updater\Migration\Custom as CustomMigration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

class Updates_5_3_0_rc1 extends Updates
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
        $commandString = './console featureflags:delete ImprovedAllWebsitesDashboard';

        $deleteFeatureFlag = new CustomMigration(
            [FeatureFlagManager::class, 'deleteFeatureFlag'],
            $commandString,
            ['ImprovedAllWebsitesDashboard']
        );

        return [$deleteFeatureFlag];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
