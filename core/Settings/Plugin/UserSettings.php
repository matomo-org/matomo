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
 * Base class of all plugin settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link addSetting()} method for each of the plugin's settings.
 *
 * For an example, see {@link Piwik\Plugins\ExampleSettingsPlugin\UserSettings}.
 *
 * $userSettings = new Piwik\Plugins\ExampleSettingsPlugin\UserSettings(); // get instance via dependency injection
 * $userSettings->yourSetting->getValue();
 *
 * @api
 */
abstract class UserSettings extends Settings
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
     * Creates a new user setting.
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
     * @return UserSetting   Returns an instance of the created measurable setting.
     */
    protected function makeSetting($name, $defaultValue, $type, $configureCallback)
    {
        $userLogin = Piwik::getCurrentUserLogin();

        $setting = new UserSetting($name, $defaultValue, $type, $this->pluginName, $userLogin);
        $setting->setConfigureCallback($configureCallback);

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
         * Triggered after user settings have been updated.
         *
         * **Example**
         *
         *     Piwik::addAction('UserSettings.updated', function (UserSettings $settings) {
         *         if ($settings->getPluginName() === 'PluginName') {
         *             $value = $settings->someSetting->getValue();
         *             // Do something with the new setting value
         *         }
         *     });
         *
         * @param Settings $settings The plugin settings object.
         */
        Piwik::postEvent('UserSettings.updated', array($this));
    }
}
