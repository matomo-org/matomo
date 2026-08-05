<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\CoreAdminHome\Commands\MigrateCompliancePolicyEnforcement;
use Piwik\Plugins\DevicesDetection\Settings\DeviceModelDetectionDisabled;
use Piwik\Plugins\PrivacyManager\Settings\DataRoundingEnabled;
use Piwik\Plugins\PrivacyManager\Settings\IPAnonymisation;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Config\ThirdPartyCookies;

/**
 * @group CoreAdminHome
 * @group Commands
 * @group MigrateCompliancePolicyEnforcement
 */
class MigrateCompliancePolicyEnforcementTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $siteId;

    /**
     * @var int
     */
    private $otherSiteId;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->siteId = Fixture::createWebsite('2024-01-01 01:02:03');
        $this->otherSiteId = Fixture::createWebsite('2024-01-01 01:02:03');
    }

    public function testHasLegacyPolicyFlagsIsFalseWithoutFlags(): void
    {
        $this->assertFalse(MigrateCompliancePolicyEnforcement::hasLegacyPolicyFlags());
    }

    public function testMigrateIsANoOpWithoutLegacyFlags(): void
    {
        $stats = MigrateCompliancePolicyEnforcement::migrate();
        $this->clearSettingsCaches();

        $this->assertSame(0, $stats['policiesMigrated']);
        $this->assertFalse(CnilPolicy::isEnforcedForSetting(IPAnonymisation::class));
    }

    public function testMigrateConvertsInstanceWideLegacyFlag(): void
    {
        $this->insertLegacySystemFlag('1');

        $this->assertTrue(MigrateCompliancePolicyEnforcement::hasLegacyPolicyFlags());

        $stats = MigrateCompliancePolicyEnforcement::migrate();
        $this->clearSettingsCaches();

        $this->assertSame(1, $stats['policiesMigrated']);
        $this->assertSame(1, $stats['instanceFlagsConverted']);
        $this->assertSame(0, $stats['siteFlagsConverted']);

        // enforcement carries over for every toggleable setting, for all sites
        $this->assertTrue(CnilPolicy::isEnforcedForSetting(IPAnonymisation::class));
        $this->assertTrue(CnilPolicy::isEnforcedForSetting(DataRoundingEnabled::class, $this->siteId));

        // externally managed settings do not get enforcement state
        $this->assertFalse(CnilPolicy::isEnforcedForSetting(ThirdPartyCookies::class));

        // the legacy flag is gone, a backup remains
        $this->assertFalse(MigrateCompliancePolicyEnforcement::hasLegacyPolicyFlags());
        $backup = json_decode(Option::get('CnilPolicy_legacy_policy_flag_backup'), true);
        $this->assertSame('1', $backup['system']);
        $this->assertSame([], $backup['sites']);
    }

    public function testMigrateConvertsPerSiteLegacyFlags(): void
    {
        $this->insertLegacySiteFlag($this->siteId, '1');
        $this->insertLegacySiteFlag($this->otherSiteId, '0');

        $stats = MigrateCompliancePolicyEnforcement::migrate();
        $this->clearSettingsCaches();

        $this->assertSame(1, $stats['policiesMigrated']);
        $this->assertSame(0, $stats['instanceFlagsConverted']);
        $this->assertSame(1, $stats['siteFlagsConverted']);

        $this->assertTrue(CnilPolicy::isEnforcedForSetting(IPAnonymisation::class, $this->siteId));
        $this->assertFalse(CnilPolicy::isEnforcedForSetting(IPAnonymisation::class, $this->otherSiteId));
        $this->assertFalse(CnilPolicy::isEnforcedForSetting(IPAnonymisation::class));

        $backup = json_decode(Option::get('CnilPolicy_legacy_policy_flag_backup'), true);
        $this->assertNull($backup['system']);
        $this->assertSame(['1', '0'], [
            $backup['sites'][$this->siteId],
            $backup['sites'][$this->otherSiteId],
        ]);
    }

    public function testMigrateIsIdempotent(): void
    {
        $this->insertLegacySystemFlag('1');

        MigrateCompliancePolicyEnforcement::migrate();
        $secondRun = MigrateCompliancePolicyEnforcement::migrate();
        $this->clearSettingsCaches();

        $this->assertSame(0, $secondRun['policiesMigrated']);
        $this->assertTrue(CnilPolicy::isEnforcedForSetting(IPAnonymisation::class));
    }

    public function testMigrateKeepsExplicitPerSettingStateWrittenBeforehand(): void
    {
        $this->insertLegacySystemFlag('1');

        // the user already disabled one setting individually before updating
        CnilPolicy::setEnforcedForSetting(IPAnonymisation::class, false);

        MigrateCompliancePolicyEnforcement::migrate();
        $this->clearSettingsCaches();

        // the explicit choice keeps its effective state, everything else is enforced
        $this->assertFalse(CnilPolicy::isEnforcedForSetting(IPAnonymisation::class));
        $this->assertTrue(CnilPolicy::isEnforcedForSetting(DataRoundingEnabled::class));
    }

    public function testReconcileEnforcementForPluginRestoresStateOfPluginsDeactivatedDuringMigration(): void
    {
        $this->insertLegacySystemFlag('1');
        MigrateCompliancePolicyEnforcement::migrate();
        $this->clearSettingsCaches();

        // simulate a plugin that was deactivated while the migration ran: its rows are missing
        Db::query(
            'DELETE FROM ' . Common::prefixTable('plugin_setting')
            . " WHERE plugin_name = 'CnilPolicy' AND setting_name LIKE '%DevicesDetection%'"
        );
        $this->clearSettingsCaches();
        $this->assertFalse(CnilPolicy::isEnforcedForSetting(DeviceModelDetectionDisabled::class));

        MigrateCompliancePolicyEnforcement::reconcileEnforcementForPlugin('DevicesDetection');
        $this->clearSettingsCaches();

        $this->assertTrue(CnilPolicy::isEnforcedForSetting(DeviceModelDetectionDisabled::class));
        // settings of other plugins keep their state, no duplicates are created
        $this->assertTrue(CnilPolicy::isEnforcedForSetting(IPAnonymisation::class));
        MigrateCompliancePolicyEnforcement::reconcileEnforcementForPlugin('DevicesDetection');
        $count = Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('plugin_setting')
            . " WHERE plugin_name = 'CnilPolicy' AND setting_name = ?",
            [CnilPolicy::getSettingEnforcementName(DeviceModelDetectionDisabled::class)]
        );
        $this->assertEquals(1, $count);
    }

    public function testReconcileDoesNotReplayBackupAfterWholePolicyWasChanged(): void
    {
        $this->insertLegacySystemFlag('1');
        MigrateCompliancePolicyEnforcement::migrate();
        $this->clearSettingsCaches();

        // simulate a plugin deactivated during migration
        Db::query(
            'DELETE FROM ' . Common::prefixTable('plugin_setting')
            . " WHERE plugin_name = 'CnilPolicy' AND setting_name LIKE '%DevicesDetection%'"
        );
        $this->clearSettingsCaches();

        // the user deliberately disables the whole policy after migrating;
        // the event listener invalidates the backup
        CnilPolicy::setActiveStatus(null, false);
        $this->assertFalse((bool) Option::get('CnilPolicy_legacy_policy_flag_backup'));

        MigrateCompliancePolicyEnforcement::reconcileEnforcementForPlugin('DevicesDetection');
        $this->clearSettingsCaches();

        $this->assertFalse(CnilPolicy::isEnforcedForSetting(DeviceModelDetectionDisabled::class));
    }

    private function insertLegacySystemFlag(string $value): void
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('plugin_setting')
            . ' (plugin_name, setting_name, setting_value, json_encoded, user_login)'
            . ' VALUES (?, ?, ?, 0, ?)',
            ['CnilPolicy', 'cnil_v1_policy_enabled', $value, '']
        );

        $this->clearSettingsCaches();
    }

    private function insertLegacySiteFlag(int $idSite, string $value): void
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('site_setting')
            . ' (idsite, plugin_name, setting_name, setting_value, json_encoded)'
            . ' VALUES (?, ?, ?, ?, 0)',
            [$idSite, 'CnilPolicy', 'cnil_v1_policy_enabled', $value]
        );

        $this->clearSettingsCaches();
    }

    /**
     * Setting storages cache loaded values; inserting rows directly requires
     * clearing those caches so the next settings write does not persist a stale
     * state without the inserted rows.
     */
    private function clearSettingsCaches(): void
    {
        \Piwik\Settings\Storage\Backend\Cache::clearCache();

        $factory = StaticContainer::get('Piwik\Settings\Storage\Factory');
        $cacheProperty = new \ReflectionProperty($factory, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheProperty->setValue($factory, []);
    }
}
