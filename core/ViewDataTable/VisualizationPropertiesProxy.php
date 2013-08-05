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

use Piwik\ViewDataTable\Properties;

/**
 * Proxy object used to get/set visualization properties. Used to check that property
 * names are valid.
 */
class VisualizationPropertiesProxy
{
    /**
     * The visualization instance.
     * 
     * @var array
     */
    private $visualization;

    /**
     * Stores visualization properties.
     * 
     * @var array
     */
    private $visualizationProperties = array();

    /**
     * Constructor.
     * 
     * @param \Piwik\Visualization\ $visualization The visualization to get/set properties of.
     */
    public function __construct($visualization)
    {
        $this->visualization = $visualization;
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
        Properties::checkValidVisualizationProperty($this->visualization, $name);
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
        Properties::checkValidVisualizationProperty($this->visualization, $name);
        return $this->visualizationProperties[$name] = $value;
    }
}