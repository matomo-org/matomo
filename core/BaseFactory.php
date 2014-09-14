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
use Piwik\Common;

/**
 * Base class for all factory types.
 * 
 * Factory types are base classes that contain a **factory** method. This method is used to instantiate
 * concrete instances by a specified string ID. Fatal errors do not occur if a class does not exist.
 * Instead an exception is thrown.
 *
 * Derived classes should override the **getClassNameFromClassId** and **getInvalidClassIdExceptionMessage**
 * static methods. 
 */
abstract class BaseFactory
{
    /**
     * Creates a new instance of a class using a string ID.
     *
     * @param string $classId The ID of the class.
     * @return BaseFactory
     * @throws Exception if $classId is invalid.
     */
    public static function factory($classId)
    {
        $className = static::getClassNameFromClassId($classId);

        if (!class_exists($className)) {
            Common::sendHeader('Content-Type: text/plain; charset=utf-8');
            throw new Exception(static::getInvalidClassIdExceptionMessage($classId));
        }

        return new $className;
    }

    /**
     * Should return a class name based on the class's associated string ID.
     */
    protected static function getClassNameFromClassId($id)
    {
        return $id;
    }

    /**
     * Should return a message to use in an Exception when an invalid class ID is supplied to
     * {@link factory()}.
     */
    protected static function getInvalidClassIdExceptionMessage($id)
    {
        return "Invalid class ID '$id' for " . get_called_class() . "::factory().";
    }
}