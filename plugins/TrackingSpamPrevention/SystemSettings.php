<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Piwik;
use Piwik\Plugins\TrackingSpamPrevention\Settings\BlockCloudsSetting;
use Piwik\Plugins\TrackingSpamPrevention\Settings\DefaultOrganisationListSetting;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\SettingsPiwik;
use Piwik\Tracker\Cache;
use Piwik\Validators\Email;
use Piwik\Validators\IpRanges;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    public const CLOUD_BLOCKING_OFF = 'off';
    public const CLOUD_BLOCKING_DEFAULT_LIST = 'default';
    public const CLOUD_BLOCKING_CUSTOM_LIST = 'custom';

    /** @var Setting */
    public $max_actions;

    /** @var Setting */
    public $notification_email;

    /** @var BlockCloudsSetting */
    public $block_clouds;

    /** @var Setting */
    public $excludedCountries;

    /** @var Setting */
    public $includedCountries;

    /** @var Setting */
    public $blockHeadless;

    /** @var Setting */
    public $blockServerSideLibraries;

    /** @var Setting */
    public $ipAllowList;

    /** @var Setting */
    public $ipBlockList;

    /** @var Setting */
    public $cloudBlockingMode;

    /** @var DefaultOrganisationListSetting */
    public $defaultOrganisationBlockList;

    /** @var Setting */
    public $organisationBlockList;

    protected function init()
    {
        $this->cloudBlockingMode = $this->makeCloudBlockingModeSetting();
        $this->defaultOrganisationBlockList = $this->makeDefaultOrganisationBlockListSetting();
        $this->organisationBlockList = $this->makeOrganisationBlockListSetting();
        $this->registerOrganisationListSettings();
        $this->block_clouds = $this->createBlockCloudsSetting();
        $this->blockHeadless = $this->createBlockHeadlessSettings();
        $this->blockServerSideLibraries = $this->createBlockServerSideLibrariesSetting();
        $this->max_actions = $this->createMaxActionsSetting();
        $this->notification_email = $this->createNotificationEmail();

        $this->excludedCountries = $this->createExcludedCountriesSetting();
        $this->includedCountries = $this->createIncludedCountriesSetting();

        $this->ipAllowList = $this->makeIpRangeListSetting(
            'ip_allow_list',
            'TrackingSpamPrevention_SettingIpAllowListTitle',
            'TrackingSpamPrevention_SettingIpAllowListHelp'
        );
        $this->ipBlockList = $this->makeIpRangeListSetting(
            'ip_block_list',
            'TrackingSpamPrevention_SettingIpBlockListTitle',
            'TrackingSpamPrevention_SettingIpBlockListHelp'
        );
    }

    private function createBlockCloudsSetting()
    {
        $setting = new BlockCloudsSetting('block_clouds', true, FieldConfig::TYPE_BOOL, $this->pluginName);
        $setting->setConfigureCallback(function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingBlockCloudIpRangesTitle');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            $field->inlineHelp = Piwik::translate('TrackingSpamPrevention_SettingBlockCloudIpRangesHelp');
            if (!SettingsPiwik::isInternetEnabled()) {
                $field->inlineHelp = Piwik::translate('TrackingSpamPrevention_BlockCloudNoteInternetDisabled') . $field->inlineHelp;
            }
        });
        $this->addSetting($setting);
        return $setting;
    }

    private function makeCloudBlockingModeSetting(): Setting
    {
        return $this->makeSetting('cloud_blocking_mode', self::CLOUD_BLOCKING_DEFAULT_LIST, FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingCloudBlockingModeTitle');
            $field->introduction = Piwik::translate('TrackingSpamPrevention_SettingsIntroduction');
            $field->inlineHelp = Piwik::translate('TrackingSpamPrevention_SettingCloudBlockingModeHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_RADIO;
            $field->availableValues = [
                self::CLOUD_BLOCKING_OFF => Piwik::translate('TrackingSpamPrevention_SettingCloudBlockingModeOptionOff'),
                self::CLOUD_BLOCKING_DEFAULT_LIST => Piwik::translate('TrackingSpamPrevention_SettingCloudBlockingModeOptionDefaultList'),
                self::CLOUD_BLOCKING_CUSTOM_LIST => Piwik::translate('TrackingSpamPrevention_SettingCloudBlockingModeOptionCustomList'),
            ];
        });
    }

    private function makeDefaultOrganisationBlockListSetting(): DefaultOrganisationListSetting
    {
        // the default value is never read: DefaultOrganisationListSetting::getValue() always reports
        // the constant, and nothing is ever stored for this field
        $setting = new DefaultOrganisationListSetting('default_organisation_block_list', [], FieldConfig::TYPE_ARRAY, $this->pluginName);
        $setting->setConfigureCallback(function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingDefaultOrganisationBlockListTitle');
            $field->inlineHelp = Piwik::translate('TrackingSpamPrevention_SettingDefaultOrganisationBlockListHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
            $field->uiControlAttributes['disabled'] = 'disabled';
            if ($this->isBlockingModeSelectable()) {
                $field->condition = 'cloud_blocking_mode=="' . self::CLOUD_BLOCKING_DEFAULT_LIST . '"';
            }
        });
        return $setting;
    }

    /**
     * Which of the two organisation lists is shown is normally decided client side from the blocking
     * mode. A `cloud_blocking_mode` config override keeps that setting out of the settings payload,
     * leaving the condition unresolved and hiding both lists - including the custom one, which would
     * then be the only list in effect and still writable. So when the mode cannot be chosen in the UI
     * the choice is made here instead, the same way
     * Piwik\Plugins\Live\SystemSettings::makeAggregatedRealtimeReportsSetting() does.
     */
    private function registerOrganisationListSettings(): void
    {
        // an overridden list is matched whatever the mode says, so showing the default list next to
        // it would report a list that is not the one in use
        $listIsOverridden = $this->hasConfigOverride($this->organisationBlockList);

        if ($this->isBlockingModeSelectable()) {
            if (!$listIsOverridden) {
                $this->addSetting($this->defaultOrganisationBlockList);
            }
            $this->addSetting($this->organisationBlockList);
            return;
        }

        $mode = $this->getCloudBlockingMode();

        if ($mode === self::CLOUD_BLOCKING_DEFAULT_LIST && !$listIsOverridden) {
            $this->addSetting($this->defaultOrganisationBlockList);
        } elseif ($mode === self::CLOUD_BLOCKING_CUSTOM_LIST) {
            $this->addSetting($this->organisationBlockList);
        }
    }

    private function isBlockingModeSelectable(): bool
    {
        return !$this->hasConfigOverride($this->cloudBlockingMode);
    }

    private function createBlockHeadlessSettings()
    {
        return $this->makeSetting('block_headless', $default = true, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingBlockHeadlessTitle');
            $field->description = Piwik::translate('TrackingSpamPrevention_SettingBlockHeadlessDescription');
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
    }

    private function createMaxActionsSetting()
    {
        return $this->makeSetting('max_actions_allowed', $default = 0, FieldConfig::TYPE_INT, function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingMaxActionsTitle');
            $field->description = Piwik::translate('TrackingSpamPrevention_SettingMaxActionsDescription');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
        });
    }

    private function createNotificationEmail()
    {
        return $this->makeSetting('notification_email', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingNotificationEmailTitle');
            $field->description = Piwik::translate('TrackingSpamPrevention_SettingNotificationEmailDescription');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->condition = 'max_actions_allowed>0';
            $field->validators[] = new Email();
        });
    }

    public function save()
    {
        parent::save();

        $ranges = StaticContainer::get(BlockedIpRanges::class);

        if ($this->block_clouds->hasValueChanged()) {
            if ($this->block_clouds->getValue()) {
                // is now enabled, lets sync ip ranges
                $ranges->updateBlockedIpRanges();
            } else {
                // we also unset any IP that was banned recently
                $ranges->unsetAllIpRanges();
            }
            Cache::clearCacheGeneral();
        }
    }

    private function createExcludedCountriesSetting()
    {
        return $this->makeSetting('excluded_countries', [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingExcludedCountriesTitle');
            $field->description = Piwik::translate('TrackingSpamPrevention_SettingExcludedCountriesDescription');
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_TUPLE;
            $field1 = new FieldConfig\MultiPair("Country", 'country', FieldConfig::UI_CONTROL_SINGLE_SELECT);
            $field1->availableValues = $this->listCountries();
            $field->uiControlAttributes['field1'] = $field1->toArray();

            $self = $this;
            $field->transform = function ($value) use ($self) {
                return $self->transformCountryList($value);
            };

            $field->validate = function ($value) use ($field1) {
                foreach ($value as $country) {
                    if (empty($country['country'])) {
                        continue;
                    }
                    if ($country['country'] === 'xx') {
                        continue; // valid,  country not detected
                    }
                    if (!isset($field1->availableValues[$country['country']])) {
                        throw new \Exception('Invalid country code');
                    }
                }
            };
        });
    }

    public function transformCountryList($value)
    {
        if (!empty($value) && is_array($value)) {
            $newVal = [];
            foreach ($value as $index => $val) {
                if (empty($val['country'])) {
                    continue;
                }
                $newVal[] = ['country' => $val['country']];
            }
            return $newVal;
        }
        return $value;
    }

    private function createIncludedCountriesSetting()
    {
        return $this->makeSetting('included_countries', [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingIncludedCountriesTitle');
            $field->description = Piwik::translate('TrackingSpamPrevention_SettingIncludedCountriesDescription');
            $field->uiControl = FieldConfig::UI_CONTROL_MULTI_TUPLE;
            $field1 = new FieldConfig\MultiPair("Country", 'country', FieldConfig::UI_CONTROL_SINGLE_SELECT);
            $field1->availableValues = $this->listCountries();
            $field->uiControlAttributes['field1'] = $field1->toArray();

            $self = $this;
            $field->transform = function ($value) use ($self) {
                return $self->transformCountryList($value);
            };
            $field->validate = function ($value) use ($field1) {
                foreach ($value as $country) {
                    if (empty($country['country'])) {
                        continue;
                    }
                    if ($country['country'] === 'xx') {
                        continue; // valid,  country not detected
                    }
                    if (!isset($field1->availableValues[$country['country']])) {
                        throw new \Exception('Invalid country code');
                    }
                }
            };
        });
    }

    private function createBlockServerSideLibrariesSetting()
    {
        return $this->makeSetting('blockServerSideLibraries', false, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingBlockSdksAndLibrariesTitle');
            $field->inlineHelp = Piwik::translate('TrackingSpamPrevention_SettingBlockSdksAndLibrariesHelp', ['<strong>', '</strong>', '<br>']);
            $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
        });
    }


    private function makeIpRangeListSetting(string $name, string $titleKey, string $inlineHelpKey): Setting
    {
        return $this->makeSetting($name, [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) use ($titleKey, $inlineHelpKey) {
            $field->title = Piwik::translate($titleKey);
            $field->inlineHelp = Piwik::translate($inlineHelpKey);
            $field->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
            $field->uiControlAttributes['placeholder'] = "192.0.2.15\n198.51.100.0/24\n2001:db8::/32";
            $field->validators[] = new IpRanges();
            $field->transform = function ($value) {
                if (empty($value) || !is_array($value)) {
                    return [];
                }
                $ips = array_map('trim', $value);
                $ips = array_filter($ips, function ($ip) {
                    return $ip !== '';
                });
                return array_values(array_unique($ips));
            };
        });
    }

    private function makeOrganisationBlockListSetting(): Setting
    {
        $setting = new SystemSetting('organisation_block_list', Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS, FieldConfig::TYPE_ARRAY, $this->pluginName);
        $setting->setConfigureCallback(function (FieldConfig $field) {
            $field->title = Piwik::translate('TrackingSpamPrevention_SettingOrganisationBlockListTitle');
            $field->inlineHelp = Piwik::translate('TrackingSpamPrevention_SettingCustomOrganisationBlockListHelp');
            $field->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
            $field->uiControlAttributes['placeholder'] = Piwik::translate('TrackingSpamPrevention_SettingOrganisationBlockListPlaceholder', ["\n"]);
            if ($this->isBlockingModeSelectable()) {
                $field->condition = 'cloud_blocking_mode=="' . self::CLOUD_BLOCKING_CUSTOM_LIST . '"';
            }
            $field->transform = function ($value) {
                if (empty($value) || !is_array($value)) {
                    return [];
                }
                $organisations = array_map(function ($organisation) {
                    return mb_strtolower(trim((string) $organisation));
                }, $value);
                $organisations = array_filter($organisations, function ($organisation) {
                    return $organisation !== '';
                });
                return array_values(array_unique($organisations));
            };
        });

        return $setting;
    }

    private function listCountries()
    {
        $regionDataProvider = StaticContainer::get(RegionDataProvider::class);
        $countryList = $regionDataProvider->getCountryList();
        array_walk($countryList, function (&$item, $key) {
            $item = Piwik::translate('Intl_Country_' . strtoupper($key));
        });
        asort($countryList); //order by localized name
        return $countryList;
    }

    public function getAllowedIpRanges(): array
    {
        return $this->settingToIpRanges($this->ipAllowList);
    }

    public function getBlockListIpRanges(): array
    {
        return $this->settingToIpRanges($this->ipBlockList);
    }

    private function settingToIpRanges(Setting $setting): array
    {
        $value = $setting->getValue();

        if (empty($value) || !is_array($value)) {
            return [];
        }

        // values set through a config file override skip the setting's transform, so clean them up here too
        return array_values(array_filter(array_map('trim', $value), function ($range) {
            return $range !== '';
        }));
    }

    public function getCloudBlockingMode(): string
    {
        $mode = $this->cloudBlockingMode->getValue();

        $known = [self::CLOUD_BLOCKING_OFF, self::CLOUD_BLOCKING_DEFAULT_LIST, self::CLOUD_BLOCKING_CUSTOM_LIST];

        if (!in_array($mode, $known, true)) {
            // the available values are only enforced when a value is saved, so a config file override
            // can hold anything. Falling back to the default keeps an unreadable value from silently
            // turning blocking off.
            return self::CLOUD_BLOCKING_DEFAULT_LIST;
        }

        return $mode;
    }

    private function hasConfigOverride(Setting $setting): bool
    {
        $pluginConfig = Config::getInstance()->{$this->pluginName};

        return is_array($pluginConfig) && array_key_exists($setting->getName(), $pluginConfig);
    }

    public function getBlockedOrganisations(): array
    {
        $mode = $this->getCloudBlockingMode();

        if ($mode === self::CLOUD_BLOCKING_OFF) {
            return [];
        }

        // a config file override is the only way to configure the list where the setting is not
        // writable, so it keeps winning over the default list
        if ($mode === self::CLOUD_BLOCKING_DEFAULT_LIST && !$this->hasConfigOverride($this->organisationBlockList)) {
            return Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS;
        }

        $value = $this->organisationBlockList->getValue();

        if (empty($value) || !is_array($value)) {
            return [];
        }

        // values set through a config file override skip the setting's transform, so clean them up here too
        $organisations = array_map(function ($organisation) {
            return mb_strtolower(trim((string) $organisation));
        }, $value);

        return array_values(array_filter($organisations, function ($organisation) {
            return $organisation !== '';
        }));
    }

    public function getExcludedCountryCodes()
    {
        return $this->settingToCountryCodes($this->excludedCountries);
    }

    public function getIncludedCountryCodes()
    {
        return $this->settingToCountryCodes($this->includedCountries);
    }

    private function settingToCountryCodes(Setting $setting)
    {
        $val = $setting->getValue();

        if (empty($val) || !is_array($val)) {
            return [];
        }

        $codes = [];
        foreach ($val as $value) {
            if (!empty($value['country'])) {
                $codes[] = $value['country'];
            }
        }
        return $codes;
    }
}
