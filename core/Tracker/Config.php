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
 * Backward compatibility layer
 * DO NOT USE
 *
 * Use this notation to fetch a config file value:
 *    Piwik_Config::getInstance()->General['enable_browser_archiving_triggering']
 *
 * @todo remove this in 2.0
 * @since 1.7
 * @deprecated 1.7
 *
 * @package Piwik
 * @subpackage Piwik_Tracker_Config
 */
class Piwik_Tracker_Config
{
    /**
     * Returns the singleton Piwik_Config
     *
     * @return Piwik_Config
     */
    static public function getInstance()
    {
        return Piwik_Config::getInstance();
    }
}
