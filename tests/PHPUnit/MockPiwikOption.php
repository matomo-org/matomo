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

    protected function getValue($name)
    {
        return $this->forcedOptionValue;
    }

    protected function setValue($name, $value, $autoLoad = 0)
    {
        $this->forcedOptionValue = $value;
    }
}
