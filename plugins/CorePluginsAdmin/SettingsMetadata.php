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
use Piwik\Policy\Exceptions\CompliancePolicyViolationException;
use Piwik\Policy\PolicyManager;
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
     * @param int|null $idSite  site the settings belong to, so that website-level compliance
     *                          policies are taken into account. Null for instance-wide settings.
     * @throws Exception;
     */
    public function setPluginSettings($settingsInstances, $settingValues, ?int $idSite = null)
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
                        if (!$this->mayBeStoredUnderCompliancePolicies($setting, $value, $idSite)) {
                            continue;
                        }

                        $setting->setValue($value);
                    }
                }
            }
        } catch (CompliancePolicyViolationException $e) {
            // already names the setting and the policy, and callers rely on the type to keep the
            // message rather than replacing it with a generic "could not save" one
            throw $e;
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

    /**
     * @param mixed $value
     * @return bool whether the value may be stored for the given setting
     * @throws \Piwik\Policy\Exceptions\CompliancePolicyViolationException when it breaks an enforced policy
     * @throws Exception
     */
    private function mayBeStoredUnderCompliancePolicies(Setting $setting, $value, ?int $idSite): bool
    {
        return PolicyManager::checkSettingValueAgainstPolicies(
            $setting->getName(),
            $value,
            $idSite,
            PolicyManager::getSettingTypeFromSettingClass($setting)
        );
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
    public function formatSettings(array $allSettings, ?int $idSite = null)
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
                'settings' => array(),
            );

            foreach ($writableSettings as $writableSetting) {
                $plugin['settings'][] = $this->formatSetting($writableSetting, $idSite);
            }

            $metadata[] = $plugin;
        }

        return $metadata;
    }

    public function formatSetting(Setting $setting, ?int $idSite = null)
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

        $result = [
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
        ];

        if ($config->customFieldComponent) {
            $result['component'] = $config->customFieldComponent;
        }

        $settingType = PolicyManager::getSettingTypeFromSettingClass($setting);
        $compliancePolicyControlled = PolicyManager::getCompliancePoliciesControllingASetting($setting->getName(), $idSite, $settingType);
        if (!empty($compliancePolicyControlled)) {
            $result = $this->applyCompliancePolicies($result, $compliancePolicyControlled, $setting->getName(), $idSite, $settingType);
            $result['extraMetadata'] = [
                'compliancePolicyControlled' => $compliancePolicyControlled,
                'idSite' => $idSite,
            ];
        }

        return $result;
    }

    /**
     * Presents a field as the compliance policies controlling it actually behave: the value in
     * effect replaces the stored one, and the control either stops being editable or stops
     * offering the values that are no longer compliant.
     *
     * @param array<string, mixed> $result
     * @param array<string, array<string, mixed>> $compliancePolicyControlled
     * @return array<string, mixed>
     * @throws Exception
     */
    private function applyCompliancePolicies(array $result, array $compliancePolicyControlled, string $settingName, ?int $idSite, ?string $settingType): array
    {
        $result['value'] = PolicyManager::getPolicyEnforcedValue($compliancePolicyControlled, $result['value']);

        if (PolicyManager::isFieldLockedByPolicies($compliancePolicyControlled)) {
            $result['uiControlAttributes']['disabled'] = 'disabled';

            return $result;
        }

        if (is_object($result['availableValues'])) {
            // availableValues maps each selectable value to its label
            $availableValues = (array) $result['availableValues'];
            $allowedValues = PolicyManager::filterValuesAllowedByPolicies(
                array_keys($availableValues),
                $settingName,
                $idSite,
                $settingType
            );

            $result['availableValues'] = (object) array_intersect_key($availableValues, array_flip($allowedValues));
        }

        return $result;
    }
}
