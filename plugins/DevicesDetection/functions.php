<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_DevicesDetection
 */
function Piwik_GetBrandLogo($label)
{
    $path = dirname(__FILE__) . '/images/brand/' . $label . '.ico';
    if (file_exists($path)) {
        return 'plugins/DevicesDetection/images/brand/' . $label . '.ico';
    } else {
        return 'plugins/DevicesDetection/images/brand/unknown.ico';
    }
}

function Piwik_getBrowserFamilyFullNameExtended($label)
{
    foreach (UserAgentParserEnhanced::$browserFamilies as $name => $family) {
        if (in_array($label, $family)) {
            return $name;
        }
    }
    return Piwik_Translate('General_Unknown');
}

function Piwik_getBrowserFamilyLogoExtended($label)
{
    if (array_key_exists($label, UserAgentParserEnhanced::$browserFamilies)) {
        $path = 'plugins/UserSettings/images/browsers/' . UserAgentParserEnhanced::$browserFamilies[$label][0] . '.gif';
    } else {
        $path = 'plugins/UserSettings/images/browsers/UNK.gif';
    }
    return $path;
}

function Piwik_getBrowserNameExtended($label)
{
    $short = substr($label, 0, 2);
    $ver = substr($label, 3, 10);
    if (array_key_exists($short, UserAgentParserEnhanced::$browsers)) {
        return trim(ucfirst(UserAgentParserEnhanced::$browsers[$short]) . ' ' . $ver);
    } else {
        return Piwik_Translate('General_Unknown');
    }
}

function Piwik_getBrowserLogoExtended($label)
{
    $short = substr($label, 0, 2);

    $familyName = Piwik_getBrowserFamilyFullNameExtended($short);
    $path = Piwik_getBrowserFamilyLogoExtended($familyName);

    return $path;
}

function Piwik_getDeviceBrandLabel($label)
{
    if (array_key_exists($label, UserAgentParserEnhanced::$deviceBrands)) {
        return ucfirst(UserAgentParserEnhanced::$deviceBrands[$label]);
    } else {
        return Piwik_Translate('General_Unknown');
    }
}

function Piwik_getDeviceTypeLabel($label)
{
    if (isset(UserAgentParserEnhanced::$deviceTypes[$label])) {
        return UserAgentParserEnhanced::$deviceTypes[$label];
    } else {
        return Piwik_Translate('General_Unknown');
    }
}

function Piwik_getDeviceTypeLogo($label)
{
    $deviceTypeLogos = Array(
        "Desktop" => "normal.gif",
        "Smartphone" => "smartphone.png",
        "Tablet" => "tablet.png",
        "Tv" => "tv.png",
        "Feature phone" => "mobile.gif",
        "Console" => "console.gif");

    if (!array_key_exists($label, $deviceTypeLogos) || $label == "Unknown") {
        $label = 'unknown.gif';
    } else {
        $label = $deviceTypeLogos[$label];
    }
    $path = 'plugins/DevicesDetection/images/screens/' . $label;
    return $path;
}

function Piwik_getModelName($label)
{
    if (!$label) {
        return Piwik_Translate('General_Unknown');
    }
    return $label;
}

function Piwik_getOSFamilyFullNameExtended($label)
{
    foreach (UserAgentParserEnhanced::$osFamilies as $name => $family) {
        if (in_array($label, $family)) {
            return $name;
        }
    }
    return Piwik_Translate('General_Unknown');
}

function Piwik_getOsFamilyLogoExtended($label)
{
    if (array_key_exists($label, UserAgentParserEnhanced::$osFamilies)) {
        $path = 'plugins/UserSettings/images/os/' . UserAgentParserEnhanced::$osFamilies[$label][0] . ".gif";
    } else {
        $path = 'plugins/UserSettings/images/os/UNK.gif';
    }
    return $path;
}

function Piwik_getOsFullNameExtended($label)
{
    if (!empty($label) && $label != ";") {
        $os = substr($label, 0, 3);
        $ver = substr($label, 4, 15);
        $name = UserAgentParserEnhanced::getOsNameFromId($os, $ver);
        if(!empty($name)) {
            return $name;
        }
    }
    return Piwik_Translate('General_Unknown');
}



function Piwik_getOsLogoExtended($label)
{
    $short = substr($label, 0, 3);
    $familyName = Piwik_getOsFamilyFullNameExtended($short);
    $path = Piwik_getOsFamilyLogoExtended($familyName);
    return $path;
}