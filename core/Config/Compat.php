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

/**
 * Backward compatibility ayer
 *
 * @todo remove this in 2.0
 * @since 1.7
 * @deprecated 1.7
 * @see Piwik::createConfigObject()
 *
 * @package Piwik
 * @subpackage Piwik_Config
 */
class Piwik_Config_Compat_Array
{
    private $data;
    /**
     * @var Piwik_Config_Compat
     */
    private $parent;

    /**
     * Constructor
     *
     * @param Piwik_Config_Compat $parent
     * @param array $data configuration section
     */
    public function __construct($parent, array $data)
    {
        $this->parent = $parent;
        $this->data = $data;
    }

    /**
     * Get value by name
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $tmp = isset($this->data[$name]) ? $this->data[$name] : false;
        return is_array($tmp) ? new Piwik_Config_Compat_Array($this, $tmp) : $tmp;
    }

    /**
     * Set name, value pair
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (is_object($value) && get_class($value) == 'Piwik_Config_Compat_Array') {
            $value = $value->toArray();
        }

        $this->data[$name] = $value;
        $this->setDirtyBit();
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Set dirty bit
     */
    public function setDirtyBit()
    {
        $this->parent->setDirtyBit();
    }
}

class Piwik_Config_Compat
{
    private $config;
    private $data;
    private $enabled;
    private $dirty;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = Piwik_Config::getInstance();
        $this->data = array();
        $this->enabled = true;
        $this->dirty = false;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->enabled && $this->dirty) {
            $this->config->forceSave();
        }
        $this->config->clear();
    }

    /**
     * Get value by name
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->data[$name])) {
            $this->data[$name] = $this->config->__get($name);
        }

        $tmp = $this->data[$name];
        return is_array($tmp) ? new Piwik_Config_Compat_Array($this, $tmp) : $tmp;
    }

    /**
     * Set name, value pair
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (is_object($value) && get_class($value) == 'Piwik_Config_Compat_Array') {
            $value = $value->toArray();
        }

        $this->config->__set($name, $value);
        $this->dirty = true;
    }

    /**
     * Set dirty bit
     */
    public function setDirtyBit()
    {
        $this->dirty = true;
    }

    /**
     * Disable saving of configuration changes
     */
    public function disableSavingConfigurationFileUpdates()
    {
        $this->enabled = false;
    }
}
