<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Resolution;

class Visitor
{
    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    function getResolution()
    {
        if (!array_key_exists('config_resolution', $this->details)) {
            return null;
        }

        return $this->details['config_resolution'];
    }
}