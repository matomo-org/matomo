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
use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;
use Piwik\Plugins\TrackingSpamPrevention\Configuration;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Storage\Factory as StorageFactory;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackingSpamPrevention
 * @group SystemSettingsTest
 * @group Plugins
 */
class SystemSettingsTest extends IntegrationTestCase
{
    /**
     * @var SystemSettings
     */
    private $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->settings = new SystemSettings();
    }

    public function testMaxActionsDefault()
    {
        $this->assertSame(0, $this->settings->max_actions->getValue());
    }

    public function testBlockCloudDefault()
    {
        $this->assertSame(true, $this->settings->block_clouds->getValue());
    }

    public function testCloudBlockingModeHasSettingsIntroduction()
    {
        $field = $this->settings->cloudBlockingMode->configureField();

        $this->assertSame(
            'TrackingSpamPrevention_SettingsIntroduction',
            $field->introduction
        );
    }

    public function testBlockCloudEnableGetOldValue()
    {
        $this->settings->block_clouds->setValue(0);// it is on by default
        $this->settings->block_clouds->setValue(1);
        $this->assertSame(true, $this->settings->block_clouds->getValue());
        $this->assertSame(false, $this->settings->block_clouds->getOldValue());
    }

    public function testBlockHeadlessDefault()
    {
        $this->assertSame(true, $this->settings->blockHeadless->getValue());
    }

    public function testBlockHeadlessDisable()
    {
        $this->settings->blockHeadless->setValue(0);
        $this->assertSame(false, $this->settings->blockHeadless->getValue());
    }

    public function testNotificationEmailDefault()
    {
        $this->assertSame('', $this->settings->notification_email->getValue());
    }

    public function testNotificationEmailErrrosWhenNotValidEmail()
    {
        $this->expectException(\Exception::class);
        $this->settings->notification_email->setValue('foo');
    }

    public function testNotificationEmailSetValidEmail()
    {
        $this->settings->notification_email->setValue('foo@matomo.org');
        $this->assertSame('foo@matomo.org', $this->settings->notification_email->getValue());
    }

    public function testNotificationEmailSetEmptyValue()
    {
        $this->settings->notification_email->setValue('');
        $this->assertSame('', $this->settings->notification_email->getValue());
    }

    public function testExcludeCountriesDefault()
    {
        $this->assertSame([], $this->settings->excludedCountries->getValue());
    }

    public function testExcludeGetExcludedCountryCodesDefault()
    {
        $this->assertSame([], $this->settings->getExcludedCountryCodes());
    }

    public function testExcludeCountries()
    {
        $this->settings->excludedCountries->setValue([
            ['country' => 'de'],['country' => 'fr'], ['country' => 'nz'],
        ]);
        $this->assertSame(['de', 'fr', 'nz'], $this->settings->getExcludedCountryCodes());
    }

    public function testExcludeCountriesSetInvalidValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid country code');
        $this->settings->excludedCountries->setValue([
            ['country' => 'de'],['country' => 'foo'],
        ]);
    }

    public function testIncludeCountriesDefault()
    {
        $this->assertSame([], $this->settings->includedCountries->getValue());
    }

    public function testIncludeCountries()
    {
        $this->settings->includedCountries->setValue([
            ['country' => 'de'],['country' => 'fr'], ['country' => 'nz'],
        ]);
        $this->assertSame(['de', 'fr', 'nz'], $this->settings->getIncludedCountryCodes());
    }

    public function testIncludeCountriesSetInvalidValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid country code');
        $this->settings->includedCountries->setValue([
            ['country' => 'de'],['country' => 'foo'],
        ]);
    }

    public function testIncludeGetIncludedCountryCodesDefault()
    {
        $this->assertSame([], $this->settings->getIncludedCountryCodes());
    }

    public function testIpAllowListDefault()
    {
        $this->assertSame([], $this->settings->ipAllowList->getValue());
    }

    public function testGetAllowedIpRangesDefault()
    {
        $this->assertSame([], $this->settings->getAllowedIpRanges());
    }

    public function testIpAllowListTransformTrimsFiltersAndDeduplicates()
    {
        $this->settings->ipAllowList->setValue([' 10.10.0.0/21 ', '', '10.10.0.0/21', '12.14.15.16', '  ', 'f::f']);
        $this->assertSame(['10.10.0.0/21', '12.14.15.16', 'f::f'], $this->settings->ipAllowList->getValue());
    }

    public function testIpAllowListAcceptsValidIpsRangesAndCidrNotations()
    {
        $ranges = ['10.10.0.1', '10.10.0.0/21', '10.10.*.*', 'f::f', '2001:db8::/64'];
        $this->settings->ipAllowList->setValue($ranges);
        $this->assertSame($ranges, $this->settings->ipAllowList->getValue());
    }

    public function testIpAllowListRejectsInvalidEntries()
    {
        $this->expectException(\Exception::class);
        $this->settings->ipAllowList->setValue(['10.10.0.1', 'foobar']);
    }

    public function testGetAllowedIpRangesReturnsCleanedValues()
    {
        $this->settings->ipAllowList->setValue(['10.10.0.0/21', '12.14.15.16']);
        $this->assertSame(['10.10.0.0/21', '12.14.15.16'], $this->settings->getAllowedIpRanges());
    }

    public function testIpBlockListDefault()
    {
        $this->assertSame([], $this->settings->ipBlockList->getValue());
    }

    public function testGetBlockListIpRangesDefault()
    {
        $this->assertSame([], $this->settings->getBlockListIpRanges());
    }

    public function testIpBlockListTransformTrimsFiltersAndDeduplicates()
    {
        $this->settings->ipBlockList->setValue([' 10.10.0.0/21 ', '', '10.10.0.0/21', '12.14.15.16', '  ', 'f::f']);
        $this->assertSame(['10.10.0.0/21', '12.14.15.16', 'f::f'], $this->settings->ipBlockList->getValue());
    }

    public function testIpBlockListRejectsInvalidEntries()
    {
        $this->expectException(\Exception::class);
        $this->settings->ipBlockList->setValue(['10.10.0.1', 'foobar']);
    }

    public function testGetBlockListIpRangesReturnsCleanedValues()
    {
        $this->settings->ipBlockList->setValue(['10.10.0.0/21', '12.14.15.16']);
        $this->assertSame(['10.10.0.0/21', '12.14.15.16'], $this->settings->getBlockListIpRanges());
    }

    public function testOrganisationBlockListDefault()
    {
        $this->assertSame(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, $this->settings->organisationBlockList->getValue());
    }

    public function testGetBlockedOrganisationsDefault()
    {
        $this->assertSame(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, $this->settings->getBlockedOrganisations());
    }

    public function testOrganisationBlockListTransformLowercasesTrimsFiltersAndDeduplicates()
    {
        $this->settings->organisationBlockList->setValue([' ExampleOrg ', '', 'exampleorg', 'Another Org', '  ']);
        $this->assertSame(['exampleorg', 'another org'], $this->settings->organisationBlockList->getValue());
    }

    public function testGetBlockedOrganisationsReturnsCleanedValues()
    {
        $this->settings->cloudBlockingMode->setValue(SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST);
        $this->settings->organisationBlockList->setValue(['ExampleOrg', 'Another Org']);
        $this->assertSame(['exampleorg', 'another org'], $this->settings->getBlockedOrganisations());
    }

    public function testCloudBlockingModeDefault()
    {
        $this->assertSame(SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST, $this->settings->getCloudBlockingMode());
    }

    public function testCloudBlockingModeRejectsUnknownValue()
    {
        $this->expectException(\Exception::class);
        $this->settings->cloudBlockingMode->setValue('somethingelse');
    }

    public function testGetBlockedOrganisationsIsEmptyWhenBlockingIsOff()
    {
        $this->settings->cloudBlockingMode->setValue(SystemSettings::CLOUD_BLOCKING_OFF);
        $this->settings->organisationBlockList->setValue(['exampleorg']);

        $this->assertSame([], $this->settings->getBlockedOrganisations());
    }

    public function testGetBlockedOrganisationsIgnoresCustomListUnderDefaultMode()
    {
        // the custom list is kept while another option is selected, it just is not used
        $this->settings->organisationBlockList->setValue(['exampleorg']);

        $this->assertSame(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, $this->settings->getBlockedOrganisations());
        $this->assertSame(['exampleorg'], $this->settings->organisationBlockList->getValue());
    }

    public function testGetBlockedOrganisationsPrefersConfigOverrideUnderDefaultMode()
    {
        $this->withPluginConfig(['organisation_block_list' => [' Override Org ']], function () {
            $this->assertSame(['override org'], $this->settings->getBlockedOrganisations());
        });
    }

    public function testGetCloudBlockingModeFallsBackToDefaultForUnknownConfigValue()
    {
        $this->withPluginConfig(['cloud_blocking_mode' => 'nonsense'], function () {
            $this->assertSame(SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST, $this->settings->getCloudBlockingMode());
            $this->assertSame(Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, $this->settings->getBlockedOrganisations());
        });
    }

    public function testDefaultOrganisationBlockListReportsTheCurrentDefaultList()
    {
        $this->assertSame(
            Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS,
            $this->settings->defaultOrganisationBlockList->getValue()
        );
    }

    public function testDefaultOrganisationBlockListIsNeverStored()
    {
        // the settings form posts this field like any other, and a stored copy would stop tracking
        // later additions to the default list
        $this->settings->defaultOrganisationBlockList->setValue(['something', 'else']);
        $this->settings->save();

        $storage = StaticContainer::get(StorageFactory::class)->getPluginStorage('TrackingSpamPrevention', '');

        $this->assertNull($storage->getValue('default_organisation_block_list', null, FieldConfig::TYPE_ARRAY));
        $this->assertSame(
            Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS,
            $this->settings->defaultOrganisationBlockList->getValue()
        );
    }

    public function testSaveDoesNotSyncWhenBlockCloudsWasNotChanged()
    {
        $ranges = $this->makeRanges();
        // block_clouds is on by default, so an unrelated save must not read as it being turned on
        $this->settings->max_actions->setValue(10);
        $this->settings->save();

        $this->assertEmpty($ranges->getBlockedRanges());
    }

    public function testSaveShouldSyncWhenEnabled()
    {
        $ranges = $this->makeRanges();
        $this->assertEmpty($ranges->getBlockedRanges());
        $this->settings->block_clouds->setValue(false);// it is on by default, so make it a real change
        $this->settings->block_clouds->setValue(true);
        $this->settings->save();
        $this->assertNotEmpty($ranges->getBlockedRanges());
    }

    public function testSaveShouldEmptyRangesWhenDisabledButNoChange()
    {
        $ranges = $this->makeRanges();
        $this->settings->block_clouds->setValue(false);
        $this->settings->save();

        $ranges->updateBlockedIpRanges();
        $this->assertNotEmpty($ranges->getBlockedRanges());

        // saving again without changing the value must keep IPs banned for exceeding max actions
        $this->settings->block_clouds->setValue(false);
        $this->settings->save();
        $this->assertNotEmpty($ranges->getBlockedRanges());
    }

    public function testSaveShouldEmptyRangesWhenDisabled()
    {
        $ranges = $this->makeRanges();
        $ranges->updateBlockedIpRanges();
        $this->assertNotEmpty($ranges->getBlockedRanges());
        $this->settings->block_clouds->setValue(true);// need to make it think there was a change
        $this->settings->block_clouds->setValue(false);
        $this->settings->save();
        $this->assertEmpty($ranges->getBlockedRanges());
    }

    public function testDefaultListFieldIsHiddenWhenTheOrganisationListIsOverridden()
    {
        $this->withPluginConfig(['organisation_block_list' => ['override org']], function () {
            // showing "the default list" beside an override would name a list that is not in use
            $this->assertNotContains('default_organisation_block_list', $this->writableSettingNames());
        });
    }

    public function testCustomListStaysVisibleWhenTheBlockingModeIsOverridden()
    {
        $this->withPluginConfig(['cloud_blocking_mode' => SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST], function () {
            $settings = new SystemSettings();
            $names = $this->writableSettingNames($settings);

            // the mode is absent from the settings payload, so a client-side condition referring to
            // it would hide the one list still in effect
            $this->assertContains('organisation_block_list', $names);
            $this->assertNotContains('default_organisation_block_list', $names);
            $this->assertNull($settings->organisationBlockList->configureField()->condition);
        });
    }

    public function testDefaultListFieldIsTheOnlyOneShownWhenTheModeIsOverriddenToTheDefaultList()
    {
        $this->withPluginConfig(['cloud_blocking_mode' => SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST], function () {
            $names = $this->writableSettingNames();

            $this->assertContains('default_organisation_block_list', $names);
            $this->assertNotContains('organisation_block_list', $names);
        });
    }

    public function testNeitherListIsShownWhenTheModeIsOverriddenToOff()
    {
        $this->withPluginConfig(['cloud_blocking_mode' => SystemSettings::CLOUD_BLOCKING_OFF], function () {
            $names = $this->writableSettingNames();

            $this->assertNotContains('default_organisation_block_list', $names);
            $this->assertNotContains('organisation_block_list', $names);
        });
    }

    private function writableSettingNames(?SystemSettings $settings = null): array
    {
        // registration is decided in init(), so the settings have to be built under the config override
        $settings = $settings ?? new SystemSettings();

        return array_map(function ($setting) {
            return $setting->getName();
        }, $settings->getSettingsWritableByCurrentUser());
    }

    private function withPluginConfig(array $values, callable $assertions): void
    {
        $sectionBefore = Config::getInstance()->TrackingSpamPrevention;
        $section = is_array($sectionBefore) ? $sectionBefore : [];

        Config::getInstance()->TrackingSpamPrevention = array_merge($section, $values);

        try {
            $assertions();
        } finally {
            // in-memory config changes leak into later tests of this class, so restore the section
            Config::getInstance()->TrackingSpamPrevention = $sectionBefore;
        }
    }

    private function makeRanges()
    {
        $ranges = [
            new BlockedIpRanges\VariableRange([
                '10.10.0.0/21',
            ]),
        ];

        return new BlockedIpRanges($ranges, new Configuration());
    }
}
