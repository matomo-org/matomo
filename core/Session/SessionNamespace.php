<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Session;

use Piwik\Common;
use Zend_Session_Namespace;

/**
 * Session namespace.
 *
 */
class SessionNamespace extends Zend_Session_Namespace
{
    /**
     * @param string $namespace
     * @param bool $singleInstance
     */
    public function __construct($namespace = 'Default', $singleInstance = false)
    {
        if (Common::isPhpCliMode()) {
            self::$_readable = true;
            return;
        }

        parent::__construct($namespace, $singleInstance);
    }
}
