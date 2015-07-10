<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker\Visit;

/**
 * Holds temporary data for tracking requests.
 *
 * RequestProcessors
 */
class VisitProperties
{
    /**
     * Information about the current visit. This array holds the column values that will be inserted or updated
     * in the `log_visit` table, or the values for the last known visit of the current visitor.
     *
     * @var array
     */
    public $visitorInfo = array();

    /**
     * Stores plugin specific tracking request metadata. RequestProcessors can store
     * whatever they want in this array, and other RequestProcessors can modify these
     * values to change tracker behavior.
     *
     * @var string[][]
     */
    private $requestMetadata = array();

    /**
     * Set a request metadata value.
     *
     * @param string $pluginName eg, `'Actions'`, `'Goals'`, `'YourPlugin'`
     * @param string $key
     * @param mixed $value
     */
    public function setRequestMetadata($pluginName, $key, $value)
    {
        $this->requestMetadata[$pluginName][$key] = $value;
    }

    /**
     * Get a request metadata value. Returns `null` if none exists.
     *
     * @param string $pluginName eg, `'Actions'`, `'Goals'`, `'YourPlugin'`
     * @param string $key
     * @return mixed
     */
    public function getRequestMetadata($pluginName, $key)
    {
        return @$this->requestMetadata[$pluginName][$key];
    }
}
