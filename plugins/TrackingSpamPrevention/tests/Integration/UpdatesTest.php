<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\TrackingSpamPrevention\Configuration;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Plugins\TrackingSpamPrevention\Updates_5_1_0;
use Piwik\Plugins\TrackingSpamPrevention\Updates_5_2_0;
use Piwik\Plugins\TrackingSpamPrevention\Updates_6_0_0_b2;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Storage\Factory as StorageFactory;
use Piwik\Settings\Storage\Storage;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;

require_once PIWIK_INCLUDE_PATH . '/plugins/TrackingSpamPrevention/Updates/5.1.0.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/TrackingSpamPrevention/Updates/5.2.0.php';
require_once PIWIK_INCLUDE_PATH . '/plugins/TrackingSpamPrevention/Updates/6.0.0-b2.php';

/**
 * @group TrackingSpamPrevention
 * @group UpdatesTest
 * @group Plugins
 */
class UpdatesTest extends IntegrationTestCase
{
    public function testUpdateMigratesConfigValuesToSystemSetting()
    {
        Config::getInstance()->TrackingSpamPrevention = [
            Configuration::KEY_RANGE_ALLOW_LIST => [' 10.10.0.0/21 ', '', 'foobar', '12.14.15.16', '10.10.0.0/21', 'f::f'],
        ];

        $this->runUpdate();

        $this->assertSame(['10.10.0.0/21', '12.14.15.16', 'f::f'], $this->makeSettings()->getAllowedIpRanges());
        $this->assertArrayNotHasKey(Configuration::KEY_RANGE_ALLOW_LIST, Config::getInstance()->TrackingSpamPrevention);
    }

    public function testUpdateInstallDefaultOnlyDoesNotWriteSettingButRemovesKey()
    {
        Config::getInstance()->TrackingSpamPrevention = [
            Configuration::KEY_RANGE_ALLOW_LIST => Configuration::DEFAULT_RANGE_ALLOW_LIST,
        ];

        $this->runUpdate();

        $this->assertSame([], $this->makeSettings()->getAllowedIpRanges());
        $this->assertArrayNotHasKey(Configuration::KEY_RANGE_ALLOW_LIST, Config::getInstance()->TrackingSpamPrevention);
    }

    public function testUpdateConfigKeyAbsentIsNoOp()
    {
        Config::getInstance()->TrackingSpamPrevention = [];

        $this->runUpdate();

        $this->assertSame([], $this->makeSettings()->getAllowedIpRanges());
    }

    public function testUpdateDoesNotOverwriteExistingSettingValue()
    {
        $settings = $this->makeSettings();
        $settings->ipAllowList->setValue(['20.20.0.0/21']);
        $settings->save();

        Config::getInstance()->TrackingSpamPrevention = [
            Configuration::KEY_RANGE_ALLOW_LIST => ['10.10.0.0/21'],
        ];

        $this->runUpdate();

        $this->assertSame(['20.20.0.0/21'], $this->makeSettings()->getAllowedIpRanges());
        $this->assertArrayNotHasKey(Configuration::KEY_RANGE_ALLOW_LIST, Config::getInstance()->TrackingSpamPrevention);
    }

    public function testUpdate520MigratesCustomOrganisationsToSystemSetting()
    {
        Config::getInstance()->TrackingSpamPrevention = [
            Configuration::KEY_GEOIP_MATCH_PROVIDERS => [' My Custom Org ', '', 'ANOTHER ORG', 'my custom org'],
        ];

        $this->runUpdate520();

        $this->assertSame(['my custom org', 'another org'], $this->makeSettings()->organisationBlockList->getValue());
        $this->assertArrayNotHasKey(Configuration::KEY_GEOIP_MATCH_PROVIDERS, Config::getInstance()->TrackingSpamPrevention);
    }

    public function testUpdate520DefaultListOnlyDoesNotStoreSettingButRemovesKey()
    {
        // older updates merged the defaults into the config in a different order than the constant's
        Config::getInstance()->TrackingSpamPrevention = [
            Configuration::KEY_GEOIP_MATCH_PROVIDERS => array_reverse(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS),
        ];

        $this->runUpdate520();

        // nothing stored, the setting keeps following the default list
        $this->assertSame(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, $this->makeSettings()->organisationBlockList->getValue());
        $this->assertArrayNotHasKey(Configuration::KEY_GEOIP_MATCH_PROVIDERS, Config::getInstance()->TrackingSpamPrevention);
    }

    public function testUpdate520EmptiedListStoresEmptyListToKeepBlockingDisabled()
    {
        Config::getInstance()->TrackingSpamPrevention = [
            Configuration::KEY_GEOIP_MATCH_PROVIDERS => ['', ' '],
        ];

        $this->runUpdate520();

        // stored empty list, not an unset setting falling back to the defaults
        $this->assertSame([], $this->makeSettings()->organisationBlockList->getValue());
        $this->assertArrayNotHasKey(Configuration::KEY_GEOIP_MATCH_PROVIDERS, Config::getInstance()->TrackingSpamPrevention);
    }

    public function testUpdate520ConfigKeyAbsentIsNoOp()
    {
        Config::getInstance()->TrackingSpamPrevention = [];

        $this->runUpdate520();

        $this->assertSame(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, $this->makeSettings()->organisationBlockList->getValue());
    }

    public function testUpdate520ConfigOverrideForNewSettingStoresNothingButRemovesOldKey()
    {
        Config::getInstance()->TrackingSpamPrevention = [
            Configuration::KEY_GEOIP_MATCH_PROVIDERS => ['config org'],
            'organisation_block_list' => ['override org'],
        ];

        // must not throw: an override makes the setting unwritable, which would fail the update
        $this->runUpdate520();

        $config = Config::getInstance()->TrackingSpamPrevention;
        $this->assertArrayNotHasKey(Configuration::KEY_GEOIP_MATCH_PROVIDERS, $config);
        $this->assertSame(['override org'], $config['organisation_block_list']);
        $this->assertSame(['override org'], $this->makeSettings()->getBlockedOrganisations());
    }

    public function testUpdate520DoesNotOverwriteExistingSettingValue()
    {
        $settings = $this->makeSettings();
        $settings->organisationBlockList->setValue(['stored org']);
        $settings->save();

        Config::getInstance()->TrackingSpamPrevention = [
            Configuration::KEY_GEOIP_MATCH_PROVIDERS => ['config org'],
        ];

        $this->runUpdate520();

        $this->assertSame(['stored org'], $this->makeSettings()->organisationBlockList->getValue());
        $this->assertArrayNotHasKey(Configuration::KEY_GEOIP_MATCH_PROVIDERS, Config::getInstance()->TrackingSpamPrevention);
    }

    public function testUpdate600b2NeverSavedBlockCloudsKeepsCloudBlockingOff()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        // nothing stored means the pre-6.0.0-b2 default, which was off
        $this->runUpdate600b2();

        $settings = $this->makeSettings();
        $this->assertSame(false, $settings->block_clouds->getValue());
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_OFF, $settings->getCloudBlockingMode());
        $this->assertSame([], $settings->getBlockedOrganisations());
    }

    public function testUpdate600b2StoredBlockCloudsOffKeepsCloudBlockingOff()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        $this->storeBlockClouds(false);

        $this->runUpdate600b2();

        $settings = $this->makeSettings();
        $this->assertSame(false, $settings->block_clouds->getValue());
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_OFF, $settings->getCloudBlockingMode());
    }

    public function testUpdate600b2StoredBlockCloudsOffKeepsCustomOrganisationList()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        $this->storeBlockClouds(false);
        $settings = $this->makeSettings();
        $settings->organisationBlockList->setValue(['my custom org']);
        $settings->save();

        $this->runUpdate600b2();

        $settings = $this->makeSettings();
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_OFF, $settings->getCloudBlockingMode());
        // the list is kept so that it is still there when blocking is turned back on
        $this->assertSame(['my custom org'], $settings->organisationBlockList->getValue());
    }

    public function testUpdate600b2BlockCloudsOnWithoutStoredListUsesTheDefaultList()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        $this->storeBlockClouds(true);

        $this->runUpdate600b2();

        $settings = $this->makeSettings();
        $this->assertSame(true, $settings->block_clouds->getValue());
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST, $settings->getCloudBlockingMode());
        $this->assertSame(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, $settings->getBlockedOrganisations());
    }

    public function testUpdate600b2BlockCloudsOnWithReorderedDefaultListUsesTheDefaultList()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        $this->storeBlockClouds(true);
        $settings = $this->makeSettings();
        $settings->organisationBlockList->setValue(array_reverse(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS));
        $settings->save();

        $this->runUpdate600b2();

        $this->assertSame(SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST, $this->makeSettings()->getCloudBlockingMode());
    }

    public function testUpdate600b2BlockCloudsOnWithCustomListUsesTheCustomList()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        $this->storeBlockClouds(true);
        $settings = $this->makeSettings();
        $settings->organisationBlockList->setValue(['my custom org']);
        $settings->save();

        $this->runUpdate600b2();

        $settings = $this->makeSettings();
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST, $settings->getCloudBlockingMode());
        $this->assertSame(['my custom org'], $settings->getBlockedOrganisations());
    }

    public function testUpdate600b2BlockCloudsOnWithEmptiedListKeepsOrganisationBlockingOff()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        $this->storeBlockClouds(true);
        $settings = $this->makeSettings();
        $settings->organisationBlockList->setValue([]);
        $settings->save();

        $this->runUpdate600b2();

        $settings = $this->makeSettings();
        // an emptied list already meant no organisation blocking while IP ranges stayed on
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST, $settings->getCloudBlockingMode());
        $this->assertSame(true, $settings->block_clouds->getValue());
        $this->assertSame([], $settings->getBlockedOrganisations());
    }

    public function testUpdate600b2ConfigOverrideEnablingBlockCloudsKeepsOrganisationBlockingOn()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        Config::getInstance()->TrackingSpamPrevention = ['block_clouds' => 1];

        // must not throw: an override makes the setting unwritable, which would fail the update
        $this->runUpdate600b2();

        $storage = $this->makeStorage();
        $this->assertNull($storage->getValue('block_clouds', null, FieldConfig::TYPE_BOOL));
        // the override is what the install was doing, even though nothing was ever stored
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST, $this->makeSettings()->getCloudBlockingMode());
    }

    public function testUpdate600b2ConfigOverrideDisablingBlockCloudsTurnsOrganisationBlockingOff()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        $this->storeBlockClouds(true);
        Config::getInstance()->TrackingSpamPrevention = ['block_clouds' => 0];

        $this->runUpdate600b2();

        // the override shadows the stored value, so the install was not blocking before the update
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_OFF, $this->makeSettings()->getCloudBlockingMode());
    }

    public function testUpdate600b2DoesNotOverwriteAnAlreadyMigratedMode()
    {
        $this->givenAnInstallFromBeforeTheSplit();

        $this->storeBlockClouds(true);
        $settings = $this->makeSettings();
        $settings->cloudBlockingMode->setValue(SystemSettings::CLOUD_BLOCKING_OFF);
        $settings->save();

        $this->runUpdate600b2();

        $this->assertSame(SystemSettings::CLOUD_BLOCKING_OFF, $this->makeSettings()->getCloudBlockingMode());
    }

    /**
     * The test fixture installs the plugin, and install() now stores the on-by-default values. An
     * install that predates this release has neither, because its install() ran long before, so the
     * migration cases have to start from that state rather than the fixture's.
     */
    private function givenAnInstallFromBeforeTheSplit(): void
    {
        $storage = $this->makeStorage();
        $storage->unsetValue('block_clouds');
        $storage->unsetValue('cloud_blocking_mode');
        $storage->save();
    }

    private function storeBlockClouds(bool $value): void
    {
        $storage = $this->makeStorage();
        $storage->setValue('block_clouds', $value);
        $storage->save();
    }

    private function makeStorage(): Storage
    {
        return StaticContainer::get(StorageFactory::class)->getPluginStorage('TrackingSpamPrevention', '');
    }

    private function runUpdate600b2()
    {
        $update = new Updates_6_0_0_b2();
        $update->doUpdate(new Updater());
    }

    private function runUpdate()
    {
        $update = new Updates_5_1_0();
        $update->doUpdate(new Updater());
    }

    private function runUpdate520()
    {
        $update = new Updates_5_2_0();
        $update->doUpdate(new Updater());
    }

    private function makeSettings(): SystemSettings
    {
        return new SystemSettings();
    }
}
