<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics;

use Piwik\Development;
use Piwik\Ini\IniReader;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Settings as PiwikSettings;
use Piwik\Plugin\Settings as PluginSettings;

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
        if ($category === 'database') {
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
     * @param \Piwik\Plugin\Settings[] $pluginSettings
     * @return array
     */
    public function addConfigValuesFromPluginSettings($configValues, $pluginSettings)
    {
        foreach ($pluginSettings as $pluginSetting) {
            $pluginName = $pluginSetting->getPluginName();

            if (empty($pluginName)) {
                continue;
            }

            $configs[$pluginName] = array();

            foreach ($pluginSetting->getSettings() as $setting) {
                if ($setting instanceof PiwikSettings\SystemSetting && $setting->isReadableByCurrentUser()) {
                    $name = $setting->getName();

                    $description = '';
                    if (!empty($setting->description)) {
                        $description .= $setting->description . ' ';
                    }

                    if (!empty($setting->inlineHelp)) {
                        $description .= $setting->inlineHelp;
                    }

                    if (isset($configValues[$pluginName][$name])) {
                        $configValues[$pluginName][$name]['defaultValue'] = $setting->defaultValue;
                        $configValues[$pluginName][$name]['description']  = trim($description);

                        if ($setting->uiControlType === PluginSettings::CONTROL_PASSWORD) {
                            $value = $configValues[$pluginName][$name]['value'];
                            $configValues[$pluginName][$name]['value'] = $this->getMaskedPassword();
                        }
                    } else {
                        $defaultValue = $setting->getValue();
                        $configValues[$pluginName][$name] = array(
                            'value' => null,
                            'description' => trim($description),
                            'isCustomValue' => false,
                            'defaultValue' => $defaultValue
                        );
                    }
                }
            }

            if (empty($configValues[$pluginName])) {
                unset($configValues[$pluginName]);
            }
        }

        return $configValues;
    }
}
