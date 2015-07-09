<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker\Visit;


/**
 * TODO
 */
class VisitProperties
{
    /**
     * TODO
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

    public function setRequestMetadata($pluginName, $key, $value)
    {
        $this->requestMetadata[$pluginName][$key] = $value;
    }

    public function getRequestMetadata($pluginName, $key)
    {
        return @$this->requestMetadata[$pluginName][$key];
    }
}
