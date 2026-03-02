<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Settings\Storage\LegacyUserSettingsMigration;

class MigrateUserScopedSettings extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:matomo590-migrate-user-scoped-settings');
        $this->setDescription(
            'Only needed for Matomo 5.9.0-b1 upgrade. '
            . 'Migrates legacy user-scoped settings from option keys to plugin_setting rows and purges legacy option keys.'
        );
    }

    protected function doExecute(): int
    {
        $result = self::migrate();

        $this->getOutput()->writeln('Done');
        foreach ($result as $key => $value) {
            $this->getOutput()->writeln(sprintf('%s: %d', $key, $value));
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    public static function migrate(): array
    {
        $migration = StaticContainer::get(LegacyUserSettingsMigration::class);
        return $migration->migrate();
    }
}
