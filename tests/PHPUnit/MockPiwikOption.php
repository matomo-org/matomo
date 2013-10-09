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
    private static $forcedOptionValue = false;

    function __construct($forcedOptionValue)
    {
        self::$forcedOptionValue = $forcedOptionValue;
    }

    public static function get($name)
    {
        return self::$forcedOptionValue;
    }

    public static function set($name, $value, $autoLoad = 0)
    {
        self::$forcedOptionValue = $value;
    }
}
