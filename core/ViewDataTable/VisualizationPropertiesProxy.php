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
            static::checkValidVisualizationProperty($this->visualizationClass, $name);
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
            static::checkValidVisualizationProperty($this->visualizationClass, $name);
        }

        return $this->visualizationProperties[$name] = $value;
    }

    /**
     * Checks if a property is a valid visualization property for the given visualization class,
     * and if not, throws an exception.
     *
     * @param string $visualizationClass
     * @param string $name The property name.
     * @throws \Exception
     */
    public static function checkValidVisualizationProperty($visualizationClass, $name)
    {
        if (!self::isValidVisualizationProperty($visualizationClass, $name)) {
            throw new \Exception("Invalid Visualization display property '$name' for '$visualizationClass'.");
        }
    }
    /**
     * Returns true if $name is a valid visualization property for the given visualization class.
     */
    public static function isValidVisualizationProperty($visualizationClass, $name)
    {
        $properties = self::getVisualizationProperties($visualizationClass);
        return isset($properties[$name]);
    }

    /**
     * Returns the set of all valid properties for the given visualization class. The result is an
     * array with property names as keys. Values of the array are undefined.
     *
     * @param string $visualizationClass
     *
     * @return array
     */
    public static function getVisualizationProperties($visualizationClass)
    {
        static $propertiesCache = array();

        if ($visualizationClass === null) {
            return array();
        }

        if (!isset($propertiesCache[$visualizationClass])) {
            $properties = self::getFlippedClassConstantMap($visualizationClass);

            $parentClass = get_parent_class($visualizationClass);
            if ($parentClass != 'Piwik\\ViewDataTable\\Visualization') {
                $properties += self::getVisualizationProperties($parentClass);
            }

            $propertiesCache[$visualizationClass] = $properties;
        }

        return $propertiesCache[$visualizationClass];
    }

    private static function getFlippedClassConstantMap($klass)
    {
        $klass = new \ReflectionClass($klass);
        $constants = $klass->getConstants();

        unset($constants['ID']);
        unset($constants['FOOTER_ICON']);
        unset($constants['FOOTER_ICON_TITLE']);

        foreach ($constants as $name => $value) {
            if (!is_string($value)) {
                unset($constants[$name]);
            }
        }

        return array_flip($constants);
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