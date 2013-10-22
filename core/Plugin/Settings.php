<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Plugin;

use Piwik\Option;
use Piwik\Piwik;

class Settings
{
    const TYPE_INT    = 'integer';
    const TYPE_FLOAT  = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOL   = 'boolean';
    const TYPE_ARRAY  = 'array';

    const FIELD_TEXT     = 'text';
    const FIELD_TEXTAREA = 'textarea';
    const FIELD_RADIO    = 'radio';
    const FIELD_CHECKBOX = 'checkbox';
    const FIELD_PASSWORD = 'password';
    const FIELD_MULTI_SELECT   = 'multiselect';
    const FIELD_SINGLE_SELECT  = 'select';

    // what about stuff like date etc?

    protected $defaultTypes   = array();
    protected $defaultFields  = array();
    protected $defaultOptions = array();

    protected $settings       = array();
    protected $settingsValues = array();

    private $pluginName;

    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;

        $this->defaultTypes = array(
            static::FIELD_TEXT     => static::TYPE_STRING,
            static::FIELD_TEXTAREA => static::TYPE_STRING,
            static::FIELD_RADIO    => static::TYPE_STRING,
            static::FIELD_CHECKBOX => static::TYPE_BOOL,
            static::FIELD_MULTI_SELECT  => static::TYPE_ARRAY,
            static::FIELD_SINGLE_SELECT => static::TYPE_STRING,
        );
        $this->defaultFields = array(
            static::TYPE_INT    => static::FIELD_TEXT,
            static::TYPE_FLOAT  => static::FIELD_TEXT,
            static::TYPE_STRING => static::FIELD_TEXT,
            static::TYPE_BOOL   => static::FIELD_CHECKBOX,
            static::TYPE_ARRAY  => static::FIELD_MULTI_SELECT,
        );
        $this->defaultOptions = array(
            'type'         => static::TYPE_STRING,
            'field'        => static::FIELD_TEXT,
            'displayedForCurrentUser' => Piwik::isUserHasSomeAdminAccess(),
            'fieldAttributes' => array(),
            'selectOptions' => array(),
            'description'  => null,
            'inlineHelp'   => null,
            'filter'       => null,
            'validate'     => null,
        );

        $this->init();
        $this->loadSettings();
    }

    protected function init()
    {
    }

    protected function addSetting($name, $title, array $options = array())
    {
        if (array_key_exists('field', $options) && !array_key_exists('type', $options)) {
            $options['type']  = $this->defaultTypes[$options['field']];
        } elseif (array_key_exists('type', $options) && !array_key_exists('field', $options)) {
            $options['field'] = $this->defaultFields[$options['type']];
        }

        $setting          = array_merge($this->defaultOptions, $options);
        $setting['name']  = $name;
        $setting['title'] = $title;

        $this->settings[] = $setting;
    }

    public function getSettingsForCurrentUser()
    {
        return array_values(array_filter($this->getSettings(), function ($setting) {
            return $setting['displayedForCurrentUser'];
        }));
    }

    public function getSettingValue($name)
    {
        $this->checkIsValidSetting($name);

        if (!array_key_exists($name, $this->settingsValues)) {
            $setting = $this->getSetting($name);

            return $setting['defaultValue'];
        }

        return $this->settingsValues[$name];
    }

    public function setSettingValue($name, $value)
    {
        $this->checkIsValidSetting($name);
        $setting = $this->getSetting($name);

        if ($setting['validate'] && $setting['validate'] instanceof \Closure) {
            call_user_func($setting['validate'], $value, $setting);
        }

        if ($setting['filter'] && $setting['filter'] instanceof \Closure) {
            $value = call_user_func($setting['filter'], $value, $setting);
        } else {
            settype($value, $setting['type']);
        }

        $this->settingsValues[$name] = $value;
    }

    public function save()
    {
        Option::set($this->getOptionKey(), serialize($this->settingsValues));
    }

    public function removeAllPluginSettings()
    {
        Option::delete($this->getOptionKey());
    }

    private function getOptionKey()
    {
        return 'Plugin_' . $this->pluginName . '_Settings';
    }

    private function loadSettings()
    {
        $values = Option::get($this->getOptionKey());

        if (!empty($values)) {
            $this->settingsValues = unserialize($values);
        }
    }

    private function checkIsValidSetting($name)
    {
        $setting = $this->getSetting($name);

        if (empty($setting)) {
            // TODO escape $name? or is it automatically escaped?
            throw new \Exception(sprintf('The setting %s does not exist', $name));
        }

        if (!$setting['displayedForCurrentUser']) {
            throw new \Exception('You are not allowed to change the value of this setting');
        }
    }

    private function getSettings()
    {
        return $this->settings;
    }

    private function getSetting($name)
    {
        foreach ($this->settings as $setting) {
            if ($name == $setting['name']) {
                return $setting;
            }
        }
    }

}
