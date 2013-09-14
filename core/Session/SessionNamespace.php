<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Session;

use Piwik\SettingsServer;
use Zend_Session_Namespace;

/**
 * Session namespace.
 *
 * @package Piwik
 * @subpackage Session
 */
class SessionNamespace extends Zend_Session_Namespace
{
    /**
     * @param string $namespace
     * @param bool $singleInstance
     */
    public function __construct($namespace = 'Default', $singleInstance = false)
    {
        if (SettingsServer::isPhpCliMode()) {
            self::$_readable = true;
            return;
        }

        parent::__construct($namespace, $singleInstance);
    }
}
