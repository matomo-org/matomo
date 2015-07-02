<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Container\StaticContainer;

/**
 * The singleton base class restricts the instantiation of derived classes to one object only.
 *
 * All plugin APIs are singletons and thus extend this class.
 *
 * @deprecated
 */
class Singleton
{
    public function __construct()
    {
    }

    final private function __clone()
    {
    }

    /**
     * Returns the singleton instance for the derived class. If the singleton instance
     * has not been created, this method will create it.
     *
     * @return Singleton
     */
    public static function getInstance()
    {
        $class = get_called_class();
        return StaticContainer::get($class);
    }
}
