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

namespace Piwik\ViewDataTable;



/**
 * Proxy object used to get/set visualization properties. Used to check that property
 * names are valid.
 */
class VisualizationPropertiesProxy
{
    /**
     * The visualization class name.
     * 
     * @var string
     */
    private $visualizationClass;

    /**
     * Stores visualization properties.
     * 
     * @var array
     */
    private $visualizationProperties = array();

    /**
     * Constructor.
     * 
     * @param string $visualizationClass The visualization class to get/set properties of.
     */
    public function __construct($visualizationClass)
    {
        $this->visualizationClass = $visualizationClass;
    }

    /**
     * Hack to allow property access in Twig (w/ property name checking).
     */
    public function __call($name, $arguments)
    {
        return $this->$name;
    }

    /**
     * Gets a reference to a visualization property.
     * 
     * @param string $name A valid property name for the current visualization.
     * @return mixed
     * @throws \Exception if the property name is invalid.
     */
    public function &__get($name)
    {
        if ($this->visualizationClass !== null) {
            Properties::checkValidVisualizationProperty($this->visualizationClass, $name);
        }
        
        return $this->visualizationProperties[$name];
    }

    /**
     * Sets a visualization property.
     * 
     * @param string $name A valid property name for the current visualization.
     * @param mixed $value
     * @return mixed Returns $value.
     * @throws \Exception if the property name is invalid.
     */
    public function __set($name, $value)
    {
        if ($this->visualizationClass !== null) {
            Properties::checkValidVisualizationProperty($this->visualizationClass, $name);
        }
        
        return $this->visualizationProperties[$name] = $value;
    }

    /**
     * Sets a visualization property, but only if the visualization is an instance of a
     * certain class.
     * 
     * @param string $forClass The visualization class to check for.
     * @param string $name A valid property name for the current visualization.
     * @param mixed $value
     * @return mixed Returns $value.
     * @throws \Exception if the property name is invalid.
     */
    public function setForVisualization($forClass, $name, $value)
    {
        if ($forClass == $this->visualizationClass
            || is_subclass_of($this->visualizationClass, $forClass)
        ) {
            return $this->__set($name, $value);
        }
    }
}