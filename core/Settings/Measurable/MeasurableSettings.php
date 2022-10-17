<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings\Measurable;

use Piwik\Piwik;
use Piwik\Settings\Settings;
use Piwik\Site;
use Exception;

/**
 * Base class of all measurable settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their measurable settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link makeSetting()} method for each of the measurable's settings.
 *
 * For an example, see the {@link Piwik\Plugins\ExampleSettingsPlugin\MeasurableSettings} plugin.
 *
 * $settingsProvider   = new Piwik\Plugin\SettingsProvider(); // get this instance via dependency injection
 * $measurableSettings = $settingProvider->getMeasurableSettings($yourPluginName, $idsite, $idType = null);
 * $measurableSettings->yourSetting->getValue();
 *
 * @api
 */
abstract class MeasurableSettings extends Settings
{
    /**
     * @var int
     */
    protected $idSite;

    /**
     * @var string
     */
    protected $idMeasurableType;

    /**
     * Constructor.
     * @param int $idSite If creating settings for a new site that is not created yet, use idSite = 0
     * @param string|null $idMeasurableType If null, idType will be detected from idSite
     * @throws Exception
     */
    public function __construct($idSite, $idMeasurableType = null)
    {
        parent::__construct();

        $this->idSite = (int) $idSite;

        if (!empty($idMeasurableType)) {
            $this->idMeasurableType = $idMeasurableType;
        } elseif (!empty($idSite)) {
            $this->idMeasurableType = Site::getTypeFor($idSite);
        } else {
            throw new Exception('No idType specified for ' . get_class($this));
        }

        $this->init();
    }

    protected function hasMeasurableType($typeId)
    {
        return $typeId === $this->idMeasurableType;
    }

    /**
     * Creates a new measurable setting.
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
     * @return MeasurableSetting   Returns an instance of the created measurable setting.
     * @throws Exception
     */
    protected function makeSetting($name, $defaultValue, $type, $fieldConfigCallback)
    {
        $setting = new MeasurableSetting($name, $defaultValue, $type, $this->pluginName, $this->idSite);
        $setting->setConfigureCallback($fieldConfigCallback);

        $this->addSetting($setting);

        return $setting;
    }

    /**
     * @internal
     * @param $name
     * @param $defaultValue
     * @param $type
     * @param $configureCallback
     * @return MeasurableProperty
     * @throws Exception
     */
    protected function makeProperty($name, $defaultValue, $type, $configureCallback)
    {
        $setting = new MeasurableProperty($name, $defaultValue, $type, $this->pluginName, $this->idSite);
        $setting->setConfigureCallback($configureCallback);

        $this->addSetting($setting);

        return $setting;
    }

    /**
     * Saves (persists) the current measurable setting values in the database.
     *
     * Will trigger an event to notify plugins that a value has been changed.
     */
    public function save()
    {
        parent::save();

        /**
         * Triggered after a plugin settings have been updated.
         *
         * **Example**
         *
         *     Piwik::addAction('MeasurableSettings.updated', function (MeasurableSettings $settings) {
         *         $value = $settings->someSetting->getValue();
         *         // Do something with the new setting value
         *     });
         *
         * @param Settings $settings The plugin settings object.
         */
        Piwik::postEvent('MeasurableSettings.updated', array($this, $this->idSite));
    }
}
