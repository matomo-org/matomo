<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package DevicesDetection
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Piwik;
use UserAgentParserEnhanced;

function getBrandLogo($label)
{
    $label = str_replace(" ", "_", $label);
    $path = dirname(__FILE__) . '/images/brand/' . $label . '.ico';
    if (file_exists($path)) {
        return 'plugins/DevicesDetection/images/brand/' . $label . '.ico';
    } else {
        return 'plugins/DevicesDetection/images/brand/unknown.ico';
    }
}

function getBrowserFamilyFullNameExtended($label)
{
    foreach (UserAgentParserEnhanced::$browserFamilies as $name => $family) {
        if (in_array($label, $family)) {
            return $name;
        }
    }
    return Piwik::translate('General_Unknown');
}

function getBrowserFamilyLogoExtended($label)
{
    if (array_key_exists($label, UserAgentParserEnhanced::$browserFamilies)) {
        $path = 'plugins/UserSettings/images/browsers/' . UserAgentParserEnhanced::$browserFamilies[$label][0] . '.gif';
    } else {
        $path = 'plugins/UserSettings/images/browsers/UNK.gif';
    }
    return $path;
}

function getBrowserNameExtended($label)
{
    $short = substr($label, 0, 2);
    $ver = substr($label, 3, 10);
    if (array_key_exists($short, UserAgentParserEnhanced::$browsers)) {
        return trim(ucfirst(UserAgentParserEnhanced::$browsers[$short]) . ' ' . $ver);
    } else {
        return Piwik::translate('General_Unknown');
    }
}

function getBrowserLogoExtended($label)
{
    $short = substr($label, 0, 2);

    $familyName = getBrowserFamilyFullNameExtended($short);
    $path = getBrowserFamilyLogoExtended($familyName);

    return $path;
}

function getDeviceBrandLabel($label)
{
    if (array_key_exists($label, UserAgentParserEnhanced::$deviceBrands)) {
        return ucfirst(UserAgentParserEnhanced::$deviceBrands[$label]);
    } else {
        return Piwik::translate('General_Unknown');
    }
}

function getDeviceTypeLabel($label)
{
    if (isset(UserAgentParserEnhanced::$deviceTypes[$label])) {
        return UserAgentParserEnhanced::$deviceTypes[$label];
    } else {
        return Piwik::translate('General_Unknown');
    }
}

function getDeviceTypeLogo($label)
{
    $deviceTypeLogos = Array(
        "Desktop"       => "normal.gif",
        "Smartphone"    => "smartphone.png",
        "Tablet"        => "tablet.png",
        "Tv"            => "tv.png",
        "Feature phone" => "mobile.gif",
        "Console"       => "console.gif");

    if (!array_key_exists($label, $deviceTypeLogos) || $label == "Unknown") {
        $label = 'unknown.gif';
    } else {
        $label = $deviceTypeLogos[$label];
    }
    $path = 'plugins/DevicesDetection/images/screens/' . $label;
    return $path;
}

function getModelName($label)
{
    if (!$label) {
        return Piwik::translate('General_Unknown');
    }
    return $label;
}

function getOSFamilyFullNameExtended($label)
{
    foreach (UserAgentParserEnhanced::$osFamilies as $name => $family) {
        if (in_array($label, $family)) {
            return $name;
        }
    }
    return Piwik::translate('General_Unknown');
}

function getOsFamilyLogoExtended($label)
{
    if (array_key_exists($label, UserAgentParserEnhanced::$osFamilies)) {
        $path = 'plugins/UserSettings/images/os/' . UserAgentParserEnhanced::$osFamilies[$label][0] . ".gif";
    } else {
        $path = 'plugins/UserSettings/images/os/UNK.gif';
    }
    return $path;
}

function getOsFullNameExtended($label)
{
    if (!empty($label) && $label != ";") {
        $os = substr($label, 0, 3);
        $ver = substr($label, 4, 15);
        $name = UserAgentParserEnhanced::getOsNameFromId($os, $ver);
        if (!empty($name)) {
            return $name;
        }
    }
    return Piwik::translate('General_Unknown');
}


function getOsLogoExtended($label)
{
    $short = substr($label, 0, 3);
    $familyName = getOsFamilyFullNameExtended($short);
    $path = getOsFamilyLogoExtended($familyName);
    return $path;
}