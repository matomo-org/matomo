<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Policy\PolicyManager;
use Piwik\Tracker\Cache;

class MigrateCompliancePolicyEnforcement extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:matomo600-migrate-compliance-policy-enforcement');
        $this->setDescription(
            'Only needed for Matomo 6.0.0-b2 upgrade. '
            . 'Converts the whole-policy compliance enforcement flags into per-setting enforcement state '
            . 'and removes the legacy flags (a JSON backup is kept in the option table for one release).'
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
     * Whether any policy still stores the legacy whole-policy enforcement flag.
     */
    public static function hasLegacyPolicyFlags(): bool
    {
        foreach (PolicyManager::getAllPolicies() as $policyClass) {
            [$systemValue, $siteRows] = self::fetchLegacyPolicyFlags($policyClass);
            if ($systemValue !== false || !empty($siteRows)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, int>
     */
    public static function migrate(): array
    {
        $stats = ['policiesMigrated' => 0, 'instanceFlagsConverted' => 0, 'siteFlagsConverted' => 0];

        foreach (PolicyManager::getAllPolicies() as $policyClass) {
            [$systemValue, $siteRows] = self::fetchLegacyPolicyFlags($policyClass);

            if ($systemValue === false && empty($siteRows)) {
                continue;
            }

            $toggleableSettings = [];
            foreach (PolicyManager::getAllControlledSettings($policyClass) as $settingClass) {
                if (!$settingClass::isExternallyManagedByPolicyPage()) {
                    $toggleableSettings[] = $settingClass;
                }
            }

            if (!empty($systemValue)) {
                $existingSystemState = self::fetchExistingEnforcementSettingNames($policyClass);
                foreach ($toggleableSettings as $settingClass) {
                    // per-setting choices made before the migration keep their effective state
                    if (in_array($policyClass::getSettingEnforcementName($settingClass), $existingSystemState, true)) {
                        continue;
                    }
                    self::insertEnforcementRow($policyClass, $settingClass, null);
                }
                $stats['instanceFlagsConverted']++;
            }

            $affectedSites = [];
            foreach ($siteRows as $row) {
                $idSite = (int) $row['idsite'];
                $affectedSites[] = $idSite;

                if (empty($row['setting_value'])) {
                    continue;
                }

                $existingSiteState = self::fetchExistingEnforcementSettingNames($policyClass, $idSite);
                foreach ($toggleableSettings as $settingClass) {
                    if (in_array($policyClass::getSettingEnforcementName($settingClass), $existingSiteState, true)) {
                        continue;
                    }
                    self::insertEnforcementRow($policyClass, $settingClass, $idSite);
                }
                $stats['siteFlagsConverted']++;
            }

            self::backupAndDeleteLegacyPolicyFlags($policyClass, $systemValue, $siteRows);

            foreach (array_unique($affectedSites) as $idSite) {
                Cache::deleteCacheWebsiteAttributes($idSite);
            }
            Cache::deleteTrackerCache();
            \Piwik\Settings\Storage\Backend\Cache::clearCache();

            $stats['policiesMigrated']++;
        }

        return $stats;
    }

    /**
     * Reads the legacy flag rows directly from the database: the setting objects
     * fold in config file values, and config-controlled instances must not have
     * per-setting state written for them.
     *
     * @param class-string<\Piwik\Policy\CompliancePolicy> $policyClass
     * @return array{0: string|false, 1: array<int, array<string, string>>}
     */
    private static function fetchLegacyPolicyFlags(string $policyClass): array
    {
        $pluginName = Piwik::getPluginNameOfMatomoClass($policyClass);
        $flagName = $policyClass::getSystemSettingShortName();

        $systemValue = Db::fetchOne(
            'SELECT setting_value FROM ' . Common::prefixTable('plugin_setting')
            . ' WHERE plugin_name = ? AND setting_name = ? AND user_login = ?',
            [$pluginName, $flagName, '']
        );

        $siteRows = Db::fetchAll(
            'SELECT idsite, setting_value FROM ' . Common::prefixTable('site_setting')
            . ' WHERE plugin_name = ? AND setting_name = ?',
            [$pluginName, $flagName]
        );

        return [$systemValue, $siteRows];
    }

    /**
     * Rows are written directly: the migration must not run plugin event listeners
     * or setting permission checks while the instance is half-upgraded.
     *
     * @param class-string<\Piwik\Policy\CompliancePolicy> $policyClass
     * @param class-string<\Piwik\Settings\Interfaces\PolicyComparisonInterface<mixed>> $settingClass
     */
    private static function insertEnforcementRow(string $policyClass, string $settingClass, ?int $idSite): void
    {
        $pluginName = Piwik::getPluginNameOfMatomoClass($policyClass);
        $settingName = $policyClass::getSettingEnforcementName($settingClass);

        if (is_null($idSite)) {
            Db::query(
                'INSERT INTO ' . Common::prefixTable('plugin_setting')
                . ' (plugin_name, setting_name, setting_value, json_encoded, user_login) VALUES (?, ?, ?, 0, ?)',
                [$pluginName, $settingName, '1', '']
            );
        } else {
            Db::query(
                'INSERT INTO ' . Common::prefixTable('site_setting')
                . ' (idsite, plugin_name, setting_name, setting_value, json_encoded) VALUES (?, ?, ?, ?, 0)',
                [$idSite, $pluginName, $settingName, '1']
            );
        }
    }

    /**
     * Removes the migration backup for the given policy. Called when the
     * whole-policy state changes after the migration; the backup is only kept
     * as a one-release safety net for support and for reconciling plugins that
     * were deactivated while the migration ran.
     *
     * Deliberately removes the ENTIRE backup on any whole-policy change, even a
     * site-scoped one: once an admin has actively managed the policy after
     * upgrading, replaying any part of the pre-migration state would be more
     * surprising than asking them to toggle a reactivated plugin's settings.
     *
     * @param class-string<\Piwik\Policy\CompliancePolicy> $policyClass
     */
    public static function deleteLegacyBackup(string $policyClass): void
    {
        Option::delete(Piwik::getPluginNameOfMatomoClass($policyClass) . '_legacy_policy_flag_backup');
    }

    /**
     * Writes the enforcement state a (re)activated plugin's settings would have
     * received from the migration, based on the legacy-flag backup. Settings of
     * plugins that were deactivated while the migration ran are not discoverable
     * then, so their enforcement is reconciled here instead of being lost.
     */
    public static function reconcileEnforcementForPlugin(string $pluginName): void
    {
        foreach (PolicyManager::getAllPolicies() as $policyClass) {
            $backupJson = Option::get(Piwik::getPluginNameOfMatomoClass($policyClass) . '_legacy_policy_flag_backup');
            if (empty($backupJson)) {
                continue;
            }

            $backup = json_decode($backupJson, true);
            if (empty($backup)) {
                continue;
            }

            $existingSystemState = self::fetchExistingEnforcementSettingNames($policyClass);

            foreach (PolicyManager::getAllControlledSettings($policyClass) as $settingClass) {
                if (
                    $settingClass::isExternallyManagedByPolicyPage()
                    || Piwik::getPluginNameOfMatomoClass($settingClass) !== $pluginName
                ) {
                    continue;
                }

                $settingName = $policyClass::getSettingEnforcementName($settingClass);

                if (!empty($backup['system']) && !in_array($settingName, $existingSystemState, true)) {
                    self::insertEnforcementRow($policyClass, $settingClass, null);
                }

                foreach ($backup['sites'] ?? [] as $idSite => $value) {
                    if (
                        !empty($value)
                        && !in_array($settingName, self::fetchExistingEnforcementSettingNames($policyClass, (int) $idSite), true)
                    ) {
                        self::insertEnforcementRow($policyClass, $settingClass, (int) $idSite);
                    }
                }
            }
        }

        Cache::deleteTrackerCache();
        \Piwik\Settings\Storage\Backend\Cache::clearCache();
    }

    /**
     * Names of the per-setting enforcement rows the given policy already stores.
     *
     * @param class-string<\Piwik\Policy\CompliancePolicy> $policyClass
     * @return array<int, string>
     */
    private static function fetchExistingEnforcementSettingNames(string $policyClass, ?int $idSite = null): array
    {
        $pluginName = Piwik::getPluginNameOfMatomoClass($policyClass);

        if (is_null($idSite)) {
            $rows = Db::fetchAll(
                'SELECT setting_name FROM ' . Common::prefixTable('plugin_setting')
                . ' WHERE plugin_name = ? AND setting_name LIKE ? AND user_login = ?',
                [$pluginName, '%\_enforce\_\_%', '']
            );
        } else {
            $rows = Db::fetchAll(
                'SELECT setting_name FROM ' . Common::prefixTable('site_setting')
                . ' WHERE idsite = ? AND plugin_name = ? AND setting_name LIKE ?',
                [$idSite, $pluginName, '%\_enforce\_\_%']
            );
        }

        return array_column($rows, 'setting_name');
    }

    /**
     * @param class-string<\Piwik\Policy\CompliancePolicy> $policyClass
     * @param string|false $systemValue
     * @param array<int, array<string, string>> $siteRows
     */
    private static function backupAndDeleteLegacyPolicyFlags(string $policyClass, $systemValue, array $siteRows): void
    {
        $pluginName = Piwik::getPluginNameOfMatomoClass($policyClass);
        $flagName = $policyClass::getSystemSettingShortName();

        $backup = [
            'system' => $systemValue === false ? null : $systemValue,
            'sites' => [],
        ];
        foreach ($siteRows as $row) {
            $backup['sites'][(int) $row['idsite']] = $row['setting_value'];
        }

        Option::set($pluginName . '_legacy_policy_flag_backup', json_encode($backup));

        Db::query(
            'DELETE FROM ' . Common::prefixTable('plugin_setting')
            . ' WHERE plugin_name = ? AND setting_name = ?',
            [$pluginName, $flagName]
        );
        Db::query(
            'DELETE FROM ' . Common::prefixTable('site_setting')
            . ' WHERE plugin_name = ? AND setting_name = ?',
            [$pluginName, $flagName]
        );
    }
}
