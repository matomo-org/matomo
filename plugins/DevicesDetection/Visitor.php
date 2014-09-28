<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicesDetection/functions.php';

class Visitor
{
    const DELIMITER_PLUGIN_NAME = ", ";

    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    function getBrowserFamilyDescription()
    {
        return getBrowserEngineName($this->getBrowserFamily());
    }

    function getBrowserFamily()
    {
        return $this->details['config_browser_engine'];
    }
}