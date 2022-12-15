<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings\Plugin;

use Piwik\Piwik;
use Piwik\Settings\Settings;

/**
 * Base class of all system settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their system settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link makeSetting()} method to create a system setting for this plugin.
 *
 * For an example, see {@link Piwik\Plugins\ExampleSettingsPlugin\SystemSettings}.
 *
 * $systemSettings = new Piwik\Plugins\ExampleSettingsPlugin\SystemSettings(); // get instance via dependency injection
 * $systemSettings->yourSetting->getValue();
 *
 * @api
 */
abstract class SystemSettings extends Settings
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    /**
     * Creates a new system setting.
     *
     * Settings will be displayed in the UI depending on the order of `makeSetting` calls. This means you can define
     * the order of the displayed settings by calling makeSetting first for more important settings.
     *
     * @param string $name         The name of the setting that shall be created
     * @param mixed  $defaultValue The default value for this setting. Note the value will not be converted to the
     *                             specified type.
     * @param string $type         The PHP internal type the value of this setting should have.
     *                             Use one of FieldConfig::TYPE_* constants
     * @param \Closure $fieldConfigCallback   A callback method to configure the field that shall be displayed in the
     *                             UI to define the value for this setting
     * @return SystemSetting   Returns an instance of the created measurable setting.
     */
    protected function makeSetting($name, $defaultValue, $type, $fieldConfigCallback)
    {
        $setting = new SystemSetting($name, $defaultValue, $type, $this->pluginName);
        $setting->setConfigureCallback($fieldConfigCallback);
        $this->addSetting($setting);
        return $setting;
    }

    /**
     * This is only meant for some core features used by some core plugins that are shipped with Piwik
     * @internal
     * @ignore
     * @param string $configSectionName
     * @param $name
     * @param $defaultValue
     * @param $type
     * @param $fieldConfigCallback
     * @return SystemSetting
     * @throws \Exception
     */
    protected function makeSettingManagedInConfigOnly($configSectionName, $name, $defaultValue, $type, $fieldConfigCallback)
    {
        $setting = new SystemConfigSetting($name, $defaultValue, $type, $this->pluginName, $configSectionName);
        $setting->setConfigureCallback($fieldConfigCallback);
        $this->addSetting($setting);
        return $setting;
    }

    /**
     * Saves (persists) the current setting values in the database.
     *
     * Will trigger an event to notify plugins that a value has been changed.
     */
    public function save()
    {
        parent::save();

        /**
         * Triggered after system settings have been updated.
         *
         * **Example**
         *
         *     Piwik::addAction('SystemSettings.updated', function (SystemSettings $settings) {
         *         if ($settings->getPluginName() === 'PluginName') {
         *             $value = $settings->someSetting->getValue();
         *             // Do something with the new setting value
         *         }
         *     });
         *
         * @param Settings $settings The plugin settings object.
         */
        Piwik::postEvent('SystemSettings.updated', array($this));
    }
}
