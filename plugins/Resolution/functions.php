<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Resolution;

use Piwik\Piwik;

function getConfigurationLabel($str)
{
    if (strpos($str, ';') === false) {
        return $str;
    }
    $values = explode(";", $str);

    $os = \Piwik\Plugins\DevicesDetection\getOsFullName($values[0]);
    $name = $values[1];
    $browser = \Piwik\Plugins\DevicesDetection\getBrowserName($name);
    if ($browser === false) {
        $browser = Piwik::translate('General_Unknown');
    }
    $resolution = $values[2];
    return $os . " / " . $browser . " / " . $resolution;
}
