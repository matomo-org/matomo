<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings;

/**
 * Base class of all settings providers.
 *
 * @api
 */
abstract class Settings
{
    /**
     * An array containing all available settings: Array ( [setting-name] => [setting] )
     *
     * @var Setting[]
     */
    private $settings = array();

    protected $pluginName;

    /**
     * By default the plugin name is shown in the UI when managing plugin settings. However, you can overwrite
     *  the displayed title by specifying a title.
     * @var string
     */
    protected $title = '';

    public function __construct()
    {
        if (!isset($this->pluginName)) {
            $classname = get_class($this);
            $parts     = explode('\\', $classname);

            if (count($parts) >= 3) {
                $this->pluginName = $parts[2];
            } else {
                throw new \Exception(sprintf('Plugin Settings must have a plugin name specified in %s, could not detect plugin name', $classname));
            }
        }
    }

    public function getTitle()
    {
        if (!empty($this->title)) {
            return $this->title;
        }

        return $this->pluginName;
    }

    /**
     * @ignore
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * @ignore
     * @return Setting
     */
    public function getSetting($name)
    {
        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }
    }

    /**
     * Implemented by descendants. This method should define plugin settings (via the
     * {@link addSetting()}) method and set the introduction text (via the
     * {@link setIntroduction()}).
     */
    abstract protected function init();

    /**
     * Returns the settings that can be displayed for the current user.
     *
     * @return Setting[]
     */
    public function getSettingsWritableByCurrentUser()
    {
        return array_filter($this->settings, function (Setting $setting) {
            return $setting->isWritableByCurrentUser();
        });
    }

    /**
     * Adds a new setting to the settings container.
     *
     * @param Setting $setting
     * @throws \Exception       If there is a setting with the same name that already exists.
     *                          If the name contains non-alphanumeric characters.
     */
    public function addSetting(Setting $setting)
    {
        $name = $setting->getName();

        if (isset($this->settings[$name])) {
            throw new \Exception(sprintf('A setting with name "%s" does already exist for plugin "%s"', $name, $this->pluginName));
        }

        $this->settings[$name] = $setting;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
        foreach ($this->settings as $setting) {
            $setting->save();
        }
    }

}
