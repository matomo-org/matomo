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

/**
 * Session namespace.
 *
 * @package Piwik
 * @subpackage Piwik_Session
 */
class Piwik_Session_Namespace extends Zend_Session_Namespace
{
    /**
     * @param string $namespace
     * @param bool $singleInstance
     */
    public function __construct($namespace = 'Default', $singleInstance = false)
    {
        if (Piwik_Common::isPhpCliMode()) {
            self::$_readable = true;
            return;
        }

        parent::__construct($namespace, $singleInstance);
    }
}
