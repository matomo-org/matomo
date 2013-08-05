<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Option;

class MockPiwikOption extends Option
{
    private $forcedOptionValue = false;

    function __construct($forcedOptionValue)
    {
        $this->forcedOptionValue = $forcedOptionValue;
    }

    public function get($name)
    {
        return $this->forcedOptionValue;
    }

    public function set($name, $value, $autoLoad = 0)
    {
        $this->forcedOptionValue = $value;
    }
}
