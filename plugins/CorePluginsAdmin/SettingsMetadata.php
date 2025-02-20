<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Setting;
use Piwik\Settings\Settings;
use Exception;

class SettingsMetadata
{
    public const PASSWORD_PLACEHOLDER = '******';

    public const EMPTY_ARRAY = '__empty__';

    /**
     * @param Settings[]  $settingsInstances
     * @param array $settingValues   array('pluginName' => array('settingName' => 'settingValue'))
     * @throws Exception;
     */
    public function setPluginSettings($settingsInstances, $settingValues)
    {
        try {
            foreach ($settingsInstances as $pluginName => $pluginSetting) {
                foreach ($pluginSetting->getSettingsWritableByCurrentUser() as $setting) {
                    $value = $this->findSettingValueFromRequest($settingValues, $pluginName, $setting->getName());

                    $fieldConfig = $setting->configureField();

                    // empty arrays are sent as __empty__ value, so we need to convert it here back to an array
                    if ($setting->getType() === FieldConfig::TYPE_ARRAY && $value === self::EMPTY_ARRAY) {
                        $value = [];
                    }

                    if (
                        isset($value) && (
                        $fieldConfig->uiControl !== FieldConfig::UI_CONTROL_PASSWORD ||
                        $value !== self::PASSWORD_PLACEHOLDER
                        )
                    ) {
                        $setting->setValue($value);
                    }
                }
            }
        } catch (Exception $e) {
            $message = $e->getMessage();

            if (!empty($setting)) {
                $title = Piwik::translate(strip_tags($setting->configureField()->title));
                if (strpos($message, $title) !== 0) {
                    // only prefix it if not already prefixed
                    $message = $title . ': ' . $message;
                }
                throw new Exception($message);
            }
        }
    }

    private function findSettingValueFromRequest($settingValues, $pluginName, $settingName)
    {
        if (!array_key_exists($pluginName, $settingValues)) {
            return;
        }

        foreach ($settingValues[$pluginName] as $setting) {
            if ($setting['name'] === $settingName) {
                $value = null;
                if (array_key_exists('value', $setting)) {
                    $value = $setting['value'];
                }

                if (is_string($value)) {
                    return Common::unsanitizeInputValue($value);
                }

                return $value;
            }
        }
    }


    /**
     * @param Settings[] $allSettings A list of Settings instead by pluginname
     * @return array
     */
    public function formatSettings($allSettings)
    {
        $metadata = array();
        foreach ($allSettings as $pluginName => $settings) {
            $writableSettings = $settings->getSettingsWritableByCurrentUser();

            if (empty($writableSettings)) {
                continue;
            }

            $plugin = array(
                'pluginName' => $pluginName,
                'title' => $settings->getTitle(),
                'settings' => array()
            );

            foreach ($writableSettings as $writableSetting) {
                $plugin['settings'][] = $this->formatSetting($writableSetting);
            }

            $metadata[] = $plugin;
        }

        return $metadata;
    }

    public function formatSetting(Setting $setting)
    {
        $config = $setting->configureField();

        $availableValues = $config->availableValues;

        if (is_array($availableValues)) {
            $availableValues = (object) $availableValues;
        }

        $value = $setting->getValue();

        if (!empty($value) && $config->uiControl === FieldConfig::UI_CONTROL_PASSWORD) {
            $value = self::PASSWORD_PLACEHOLDER;
        }

        $result = array(
            'name' => $setting->getName(),
            'title' => $config->title,
            'value' => $value,
            'defaultValue' => $setting->getDefaultValue(),
            'type' => $setting->getType(),
            'uiControl' => $config->uiControl,
            'uiControlAttributes' => $config->uiControlAttributes,
            'availableValues' => $availableValues,
            'description' => $config->description,
            'inlineHelp' => $config->inlineHelp,
            'introduction' => $config->introduction,
            'condition' => $config->condition,
            'fullWidth' => $config->fullWidth,
        );

        if ($config->customFieldComponent) {
            $result['component'] = $config->customFieldComponent;
        }

        return $result;
    }
}
