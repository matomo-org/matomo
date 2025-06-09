<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics;

use Piwik\Development;
use Matomo\Ini\IniReader;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Settings as PiwikSettings;

/**
 * A diagnostic report contains all the results of all the diagnostics.
 */
class ConfigReader
{
    /**
     * @var GlobalSettingsProvider
     */
    private $settings;

    /**
     * @var IniReader
     */
    private $iniReader;

    public function __construct(GlobalSettingsProvider $settings, IniReader $iniReader)
    {
        $this->settings = $settings;
        $this->iniReader = $iniReader;
    }

    public function getConfigValuesFromFiles()
    {
        $ini = $this->settings->getIniFileChain();
        $descriptions = $this->iniReader->readComments($this->settings->getPathGlobal());

        $copy = array();
        foreach ($ini->getAll() as $category => $values) {
            if ($this->shouldSkipCategory($category)) {
                continue;
            }

            $local = $this->getFromLocalConfig($category);
            if (empty($local)) {
                $local = array();
            }

            $global = $this->getFromGlobalConfig($category);
            if (empty($global)) {
                $global = array();
            }

            $copy[$category] = array();
            foreach ($values as $key => $value) {
                $newValue = $value;
                if ($this->isKeyAPassword($key)) {
                    $newValue = $this->getMaskedPassword();
                }

                $defaultValue = null;
                if (array_key_exists($key, $global)) {
                    $defaultValue = $global[$key];
                }

                $description = '';
                if (!empty($descriptions[$category][$key])) {
                    $description = trim($descriptions[$category][$key]);
                }

                $copy[$category][$key] = array(
                    'value' => $newValue,
                    'description' => $description,
                    'isCustomValue' => array_key_exists($key, $local),
                    'defaultValue' => $defaultValue,
                );
            }
        }

        return $copy;
    }

    private function shouldSkipCategory($category)
    {
        $category = strtolower($category);
        if ($category === 'database' || $category === 'database_reader') {
            return true;
        }

        $developmentOnlySections = array('database_tests', 'tests', 'debugtests');

        return !Development::isEnabled() && in_array($category, $developmentOnlySections);
    }

    public function getFromGlobalConfig($name)
    {
        return $this->settings->getIniFileChain()->getFrom($this->settings->getPathGlobal(), $name);
    }

    public function getFromLocalConfig($name)
    {
        return $this->settings->getIniFileChain()->getFrom($this->settings->getPathLocal(), $name);
    }

    private function getMaskedPassword()
    {
        return '******';
    }

    private function isKeyAPassword($key)
    {
        $key = strtolower($key);
        $passwordFields = array(
            'password', 'secret', 'apikey', 'privatekey', 'admin_pass', 'md5', 'sha1'
        );
        foreach ($passwordFields as $value) {
            if (strpos($key, $value) !== false) {
                return true;
            }
        }

        if ($key === 'salt') {
            return true;
        }

        return false;
    }

    /**
     * Adds config values that can be used to overwrite a plugin system setting and adds a description + default value
     * for already existing configured config values that overwrite a plugin system setting.
     *
     * @param array $configValues
     * @param \Piwik\Settings\Plugin\SystemSettings[] $systemSettings
     * @return array
     */
    public function addConfigValuesFromSystemSettings($configValues, $systemSettings)
    {
        foreach ($systemSettings as $pluginSetting) {
            $pluginName = $pluginSetting->getPluginName();

            if (empty($pluginName)) {
                continue;
            }

            if (!array_key_exists($pluginName, $configValues)) {
                $configValues[$pluginName] = array();
            }

            foreach ($pluginSetting->getSettingsWritableByCurrentUser() as $setting) {
                $name = $setting->getName();

                $configSection = $pluginName;

                if ($setting instanceof PiwikSettings\Plugin\SystemConfigSetting) {
                    $configSection = $setting->getConfigSectionName();

                    if ($this->shouldSkipCategory($configSection)) {
                        continue;
                    }
                }

                $config = $setting->configureField();

                $description = '';
                if (!empty($config->description)) {
                    $description .= $config->description . ' ';
                }

                if (!empty($config->inlineHelp)) {
                    $description .= $config->inlineHelp;
                }

                if (isset($configValues[$configSection][$name])) {
                    $configValues[$configSection][$name]['defaultValue'] = $setting->getDefaultValue();
                    $configValues[$configSection][$name]['description']  = trim($description);
                } else {
                    $configValues[$configSection][$name] = array(
                        'value' => $setting->getValue() != $setting->getDefaultValue() ? $setting->getValue() : null,
                        'description' => trim($description),
                        'isCustomValue' => $setting->getValue() != $setting->getDefaultValue(),
                        'defaultValue' => $setting->getDefaultValue()
                    );
                }

                if (
                    !empty($configValues[$configSection][$name]['value'])
                    && $config->uiControl === PiwikSettings\FieldConfig::UI_CONTROL_PASSWORD
                ) {
                    $configValues[$configSection][$name]['value'] = $this->getMaskedPassword();
                }
            }

            if (empty($configValues[$pluginName])) {
                unset($configValues[$pluginName]);
            }
        }

        return $configValues;
    }
}
