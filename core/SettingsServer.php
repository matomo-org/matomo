<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

/**
 * Contains helper methods that can be used to get information regarding the
 * server, its settings and currently used PHP settings.
 *
 */
class SettingsServer
{
    /**
     * Returns true if the current script execution was triggered by the cron archiving script.
     *
     * Helpful for error handling: directly throw error without HTML (eg. when DB is down).
     *
     * @return bool
     * @api
     */
    public static function isArchivePhpTriggered()
    {
        return !empty($_GET['trigger'])
                && $_GET['trigger'] == 'archivephp'
                && Piwik::hasUserSuperUserAccess();
    }

    /**
     * Returns true if the current request is a Tracker request.
     *
     * @return bool true if the current request is a Tracking API Request (ie. piwik.php)
     */
    public static function isTrackerApiRequest()
    {
        return !empty($GLOBALS['PIWIK_TRACKER_MODE']);
    }

    /**
     * Mark the current request as a Tracker API request
     */
    public static function setIsTrackerApiRequest()
    {
        $GLOBALS['PIWIK_TRACKER_MODE'] = true;
    }

    /**
     * Set the current request is not a tracker API request
     */
    public static function setIsNotTrackerApiRequest()
    {
        $GLOBALS['PIWIK_TRACKER_MODE'] = false;
    }

    /**
     * Returns `true` if running on Microsoft IIS 7 (or above), `false` if otherwise.
     *
     * @return bool
     * @api
     */
    public static function isIIS()
    {
        $iis = isset($_SERVER['SERVER_SOFTWARE']) &&
            preg_match('/^Microsoft-IIS\/(.+)/', $_SERVER['SERVER_SOFTWARE'], $matches) &&
            version_compare($matches[1], '7') >= 0;

        return $iis;
    }

    /**
     * Returns `true` if running on an Apache web server, `false` if otherwise.
     *
     * @return bool
     * @api
     * @deprecated
     */
    public static function isApache()
    {
        $apache = isset($_SERVER['SERVER_SOFTWARE']) &&
            !strncmp($_SERVER['SERVER_SOFTWARE'], 'Apache', 6);

        return $apache;
    }

    /**
     * Returns `true` if running on a Windows operating system, `false` if otherwise.
     *
     * @since 0.6.5
     * @return bool
     * @api
     */
    public static function isWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    /**
     * Returns `true` if this PHP version/build supports timezone manipulation
     * (e.g., php >= 5.2, or compiled with **EXPERIMENTAL_DATE_SUPPORT=1** for
     * php < 5.2).
     *
     * @return bool
     * @api
     */
    public static function isTimezoneSupportEnabled()
    {
        return
            function_exists('date_create') &&
            function_exists('date_default_timezone_set') &&
            function_exists('timezone_identifiers_list') &&
            function_exists('timezone_open') &&
            function_exists('timezone_offset_get');
    }

    /**
     * Returns `true` if the GD PHP extension is available, `false` if otherwise.
     *
     * _Note: ImageGraph and the sparkline report visualization depend on the GD extension._
     *
     * @return bool
     * @api
     */
    public static function isGdExtensionEnabled()
    {
        static $gd = null;
        if (is_null($gd)) {
            $gd = false;

            $extensions = @get_loaded_extensions();
            if (is_array($extensions)) {
                $gd = in_array('gd', $extensions) && function_exists('imageftbbox');
            }
        }

        return $gd;
    }

    /**
     * Raise PHP memory limit if below the minimum required
     *
     * @return bool  true if set; false otherwise
     */
    public static function raiseMemoryLimitIfNecessary()
    {
        $memoryLimit = self::getMemoryLimitValue();
        if ($memoryLimit === false) {
            return false;
        }
        $minimumMemoryLimit = Config::getInstance()->General['minimum_memory_limit'];

        if (self::isArchivePhpTriggered()) {
            // core:archive command: no time limit, high memory limit
            self::setMaxExecutionTime(0);
            $minimumMemoryLimitWhenArchiving = Config::getInstance()->General['minimum_memory_limit_when_archiving'];
            if ($memoryLimit < $minimumMemoryLimitWhenArchiving) {
                return self::setMemoryLimit($minimumMemoryLimitWhenArchiving);
            }
            return false;
        }
        if ($memoryLimit < $minimumMemoryLimit) {
            return self::setMemoryLimit($minimumMemoryLimit);
        }
        return false;
    }

    /**
     * Set PHP memory limit
     *
     * Note: system settings may prevent scripts from overriding the master value
     *
     * @param int $minimumMemoryLimit
     * @return bool  true if set; false otherwise
     */
    protected static function setMemoryLimit($minimumMemoryLimit)
    {
        // in Megabytes
        $currentValue = self::getMemoryLimitValue();
        if ($currentValue === false
            || ($currentValue < $minimumMemoryLimit && @ini_set('memory_limit', $minimumMemoryLimit . 'M'))
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get php memory_limit (in Megabytes)
     *
     * Prior to PHP 5.2.1, or on Windows, --enable-memory-limit is not a
     * compile-time default, so ini_get('memory_limit') may return false.
     *
     * @see http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     * @return int|bool  memory limit in megabytes, or false if there is no limit
     */
    public static function getMemoryLimitValue()
    {
        if (($memory = ini_get('memory_limit')) > 0) {
            // handle shorthand byte options (case-insensitive)
            $shorthandByteOption = substr($memory, -1);
            switch ($shorthandByteOption) {
                case 'G':
                case 'g':
                    return substr($memory, 0, -1) * 1024;
                case 'M':
                case 'm':
                    return substr($memory, 0, -1);
                case 'K':
                case 'k':
                    return substr($memory, 0, -1) / 1024;
            }
            return $memory / 1048576;
        }

        // no memory limit
        return false;
    }

    /**
     * Set maximum script execution time.
     *
     * @param int $executionTime max execution time in seconds (0 = no limit)
     */
    public static function setMaxExecutionTime($executionTime)
    {
        // in the event one or the other is disabled...
        @ini_set('max_execution_time', $executionTime);
        @set_time_limit($executionTime);
    }
}
