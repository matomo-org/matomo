<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_PLUGINNAME
 */
namespace Piwik\Plugins\PLUGINNAME;

/**
 * API for plugin PLUGINNAME
 *
 * @package Piwik_PLUGINNAME
 */
class API
{
    static private $instance = null;

    /**
     * @return \Piwik\Plugins\PLUGINNAME\API
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get Answer to Life
     * @return integer
     */
    public function getAnswerToLife()
    {
        return 42;
    }
}