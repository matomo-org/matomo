<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Dependency;

/**
 * Maps Matomo\\Dependencies\\... class names to just \\... names.
 */
class PrefixRemovingAutoloader
{
    const PREFIX = 'Matomo\\Dependencies\\';

    public function __construct()
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend = false);

        $this->loadFunctionAdapters();
    }

    public function loadClass($class)
    {
        if (self::PREFIX == substr($class, 0, strlen(self::PREFIX)) && !class_exists($class)) {
            $result = class_alias(substr($class, strlen(self::PREFIX)), $class);

            // interfaces are not loaded when checking parameter arguments, so we need to preemptively
            // class_alias them here. otherwise, eg, we might try to pass Monolog\Logger for a parameter
            // marked as Matomo\Dependencies\Psr\Log\LoggerInterface which will simply fail.
            foreach (class_implements($class) as $interface) {
                $reflectionClass = new \ReflectionClass($interface);
                if ($reflectionClass->isUserDefined() && !interface_exists(self::PREFIX . $interface)) {
                    class_alias($interface, self::PREFIX . $interface);
                }
            }

            return $result;
        }

        return null;
    }

    private function loadFunctionAdapters()
    {
        require_once __DIR__ . '/function-adapters/php-di.php';
    }
}
