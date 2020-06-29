<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker\Visit;

/**
 * Holds temporary data for tracking requests.
 */
class VisitProperties
{
    /**
     * Information about the current visit. This array holds the column values that will be inserted or updated
     * in the `log_visit` table, or the values for the last known visit of the current visitor.
     *
     * @var array
     */
    private $visitInfo = array();

    public function __construct(array $visitInfo = [])
    {
        $this->visitInfo = $visitInfo;
    }

    /**
     * Returns a visit property, or `null` if none is set.
     *
     * @param string $name The property name.
     * @return mixed
     */
    public function getProperty($name)
    {
        return isset($this->visitInfo[$name]) ? $this->visitInfo[$name] : null;
    }

    /**
     * Returns all visit properties by reference.
     *
     * @return array
     */
    public function &getProperties()
    {
        return $this->visitInfo;
    }

    /**
     * Sets a visit property.
     *
     * @param string $name The property name.
     * @param mixed $value The property value.
     */
    public function setProperty($name, $value)
    {
        $this->visitInfo[$name] = $value;
    }

    /**
     * Unsets all visit properties.
     */
    public function clearProperties()
    {
        $this->visitInfo = array();
    }

    /**
     * Sets all visit properties.
     *
     * @param array $properties
     */
    public function setProperties($properties)
    {
        $this->visitInfo = $properties;
    }
}
