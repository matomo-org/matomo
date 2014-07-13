<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use \Exception;

/**
 * Development related checks and tools
 */
class Development
{
    private static $isEnabled = null;

    /**
     * Returns `true` if segmentation is allowed for this user, `false` if otherwise.
     *
     * @return bool
     * @api
     */
    public static function isEnabled()
    {
        if (is_null(self::$isEnabled)) {
            self::$isEnabled = (bool) Config::getInstance()->Development['enabled'];
        }

        return self::$isEnabled;
    }

    public static function methodExists($classOrInstance, $method)
    {
        if (is_string($classOrInstance)) {
            return class_exists($classOrInstance) && method_exists($classOrInstance, $method);
        }

        return method_exists($classOrInstance, $method);
    }

    public static function formatMethodCall($classOrInstance, $method)
    {
        if (is_object($classOrInstance)) {
            $classOrInstance = get_class($classOrInstance);
        }

        return $classOrInstance . '::' . $method . '()';
    }

    public static function checkMethodIsCallable($classOrInstance, $method, $prefixMessageIfError)
    {
        if (!self::isEnabled()) {
            return;
        }

        if (!self::methodExists($classOrInstance, $method)) {
            self::error($prefixMessageIfError . ' "' . self::formatMethodCall($classOrInstance, $method) .  '" does not exist. Please make sure to define such a method.');
        }

        if (!self::isCallableMethod($classOrInstance, $method)) {
            self::error($prefixMessageIfError . ' "' . self::formatMethodCall($classOrInstance, $method) .  '" is not callable. Please make sure to method is public');

        }
    }

    public static function isCallableMethod($classOrInstance, $method)
    {
        if (!self::methodExists($classOrInstance, $method)) {
            return false;
        }

        $reflection = new \ReflectionMethod($classOrInstance, $method);
        return $reflection->isPublic();
    }

    public static function error($message)
    {
        if (!self::isEnabled()) {
            return;
        }

        $message .= ' (This error is only shown in development mode)';

        if (SettingsServer::isTrackerApiRequest()
            || Common::isPhpCliMode()) {
            Log::error($message);
        } else {
            throw new Exception($message);
        }

    }
}
