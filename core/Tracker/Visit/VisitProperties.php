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
     * in the `log_visit` table, or the values for the last known visit of the current visitor. These properties
     * can be modified during request processing.
     *
     * @var array
     */
    private $visitInfo = [];

    /**
     * Holds the initial visit properties information about the current visit, this data is not changed during request processing.
     *
     * @var array
     */
    private $visitInfoImmutableProperties = [];


    public function __construct(array $visitInfo = [])
    {
        $this->visitInfo = $visitInfo;
        $this->visitInfoImmutableProperties = $visitInfo;
    }

    /**
     * Returns a visit property, or `null` if none is set.
     *
     * @param string $name The property name.
     *
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
    public function &getProperties(): array
    {
        return $this->visitInfo;
    }

    /**
     * Sets a visit property.
     *
     * @param string $name The property name.
     * @param mixed $value The property value.
     *
     * @return void
     */
    public function setProperty($name, $value): void
    {
        $this->visitInfo[$name] = $value;
    }

    /**
     * Unsets all visit properties.
     *
     * @return void
     */
    public function clearProperties(): void
    {
        $this->visitInfo = [];
    }

    /**
     * Sets all visit properties.
     *
     * @param array $properties
     *
     * @return void
     */
    public function setProperties(array $properties): void
    {
        $this->visitInfo = $properties;
    }

    /**
     * Set the initial values of a property.
     * The immutable value remains unchanged throughout request processing and can be access with getImmutableProperty()
     * The mutable value can be updated at any time with setProperty() and accessed via getProperty()
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function initializeProperty(string $name, $value): void
    {
        if (isset($this->visitInfoImmutableProperties[$name])) {
            throw new \Exception(sprintf('The property %s has already been initialized', $name));
        }
        $this->visitInfoImmutableProperties[$name] = $value;
        $this->setProperty($name, $value);
    }

    /**
     * Returns a visit property, unmodified by request processors. Returns `null` if not set.
     *
     * @param string $name The property name.
     *
     * @return mixed|null
     */
    public function getImmutableProperty(string $name)
    {
        return $this->visitInfoImmutableProperties[$name] ?? null;
    }

    /**
     * Returns all immutable visit properties
     *
     * @return array
     */
    public function getImmutableProperties(): array
    {
        return $this->visitInfoImmutableProperties;
    }
}
