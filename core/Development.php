<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Exception;

/**
 * Development related checks and tools. You can enable/disable development using `./console development:enable` and
 * `./console development:disable`. The intention of the development mode and this class is to support the developer
 * as much as possible by doing some additional checks if the development mode is enabled. For instance if a developer
 * has to register any class/method we can make sure whether they actually exist and if not display a useful error
 * message. This helps the user to find for instance simple typos and makes sure it will actually work even if he
 * forgets to test it.
 */
class Development
{
    private static $isEnabled = null;

    /**
     * Returns `true` if development mode is enabled and `false` otherwise.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        if (is_null(self::$isEnabled)) {
            self::$isEnabled = (bool) Config::getInstance()->Development['enabled'];
        }

        return self::$isEnabled;
    }

    /**
     * Verifies whether a className of object implements the given method. It does not check whether the given method
     * is actually callable (public).
     *
     * @param string|object $classOrObject
     * @param string $method
     *
     * @return bool true if the method exists, false otherwise.
     */
    public static function methodExists($classOrObject, $method)
    {
        if (is_string($classOrObject)) {
            return class_exists($classOrObject) && method_exists($classOrObject, $method);
        }

        return method_exists($classOrObject, $method);
    }

    /**
     * Formats a method call depending on the given class/object and method name. It does not perform any checks whether
     * does actually exists.
     *
     * @param string|object $classOrObject
     * @param string $method
     *
     * @return string Formatted method call. Example: "MyNamespace\MyClassname::methodName()"
     */
    public static function formatMethodCall($classOrObject, $method)
    {
        if (is_object($classOrObject)) {
            $classOrObject = get_class($classOrObject);
        }

        return $classOrObject . '::' . $method . '()';
    }

    /**
     * Checks whether the given method is actually callable on the given class/object if the development mode is
     * enabled. En error will be triggered if the method does not exist or is not callable (public) containing a useful
     * error message for the developer.
     *
     * @param string|object $classOrObject
     * @param string $method
     * @param string $prefixMessageIfError You can prepend any string to the error message in case the method is not
     *                                     callable.
     */
    public static function checkMethodIsCallable($classOrObject, $method, $prefixMessageIfError)
    {
        if (!self::isEnabled()) {
            return;
        }

        self::checkMethodExists($classOrObject, $method, $prefixMessageIfError);

        if (!self::isCallableMethod($classOrObject, $method)) {
            self::error($prefixMessageIfError . ' "' . self::formatMethodCall($classOrObject, $method) .  '" is not callable. Please make sure to method is public');
        }
    }

    /**
     * Checks whether the given method is actually callable on the given class/object if the development mode is
     * enabled. En error will be triggered if the method does not exist or is not callable (public) containing a useful
     * error message for the developer.
     *
     * @param string|object $classOrObject
     * @param string $method
     * @param string $prefixMessageIfError You can prepend any string to the error message in case the method is not
     *                                     callable.
     */
    public static function checkMethodExists($classOrObject, $method, $prefixMessageIfError)
    {
        if (!self::isEnabled()) {
            return;
        }

        if (!self::methodExists($classOrObject, $method)) {
            self::error($prefixMessageIfError . ' "' . self::formatMethodCall($classOrObject, $method) .  '" does not exist. Please make sure to define such a method.');
        }
    }

    /**
     * Verify whether the given method actually exists and is callable (public).
     *
     * @param string|object $classOrObject
     * @param string $method
     * @return bool
     */
    public static function isCallableMethod($classOrObject, $method)
    {
        if (!self::methodExists($classOrObject, $method)) {
            return false;
        }

        $reflection = new \ReflectionMethod($classOrObject, $method);
        return $reflection->isPublic();
    }

    /**
     * Triggers an error if the development mode is enabled. Depending on the current environment / mode it will either
     * log the given message or throw an exception to make sure it will be displayed in the Piwik UI.
     *
     * @param  string $message
     * @throws Exception
     */
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

    public static function getMethodSourceCode($className, $methodName)
    {
        $method = new \ReflectionMethod($className, $methodName);

        $file   = new \SplFileObject($method->getFileName());
        $offset = $method->getStartLine() - 1;
        $count  = $method->getEndLine() - $method->getStartLine() + 1;

        $fileIterator = new \LimitIterator($file, $offset, $count);

        $methodCode = "\n    " . $method->getDocComment() . "\n";
        foreach ($fileIterator as $line) {
            $methodCode .= $line;
        }
        $methodCode .= "\n";

        return $methodCode;
    }

    public static function getUseStatements($className)
    {
        $class = new \ReflectionClass($className);

        $file  = new \SplFileObject($class->getFileName());

        $fileIterator = new \LimitIterator($file, 0, $class->getStartLine());

        $uses = array();
        foreach ($fileIterator as $line) {
            if (preg_match('/(\s*)use (.+)/', $line, $match)) {
                $uses[] = trim($match[2]);
            }
        }

        return $uses;
    }
}
