<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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
        return getBrowserLogoExtended(UserAgentParserEnhanced::$browserFamilies[$label][0]);
    }
    return getBrowserLogoExtended($label);
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

/**
 * Returns the path to the logo for the given browser
 *
 * First try to find a logo for the given short code
 * If none can be found try to find a logo for the browser family
 * Return unkown logo otherwise
 *
 * @param string  $short  Shortcode or name of browser
 *
 * @return string  path to image
 */
function getBrowserLogoExtended($short)
{
    $path = 'plugins/UserSettings/images/browsers/%s.gif';

    // If name is given instead of short code, try to find matching shortcode
    if (strlen($short) > 2) {

        if (in_array($short, UserAgentParserEnhanced::$browsers)) {
            $flippedBrowsers = array_flip(UserAgentParserEnhanced::$browsers);
            $short = $flippedBrowsers[$short];
        } else {
            $short = substr($short, 0, 2);
        }
    }

    $family = getBrowserFamilyFullNameExtended($short);

    if (array_key_exists($short, UserAgentParserEnhanced::$browsers) && file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, $short))) {
        return sprintf($path, $short);
    } elseif (array_key_exists($family, UserAgentParserEnhanced::$browserFamilies) && file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, UserAgentParserEnhanced::$browserFamilies[$family][0]))) {
        return sprintf($path, UserAgentParserEnhanced::$browserFamilies[$family][0]);
    }
    return sprintf($path, 'UNK');
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
    $translations = array(
        'desktop'       => 'General_Desktop',
        'smartphone'    => 'DevicesDetection_Smartphone',
        'tablet'        => 'DevicesDetection_Tablet',
        'feature phone' => 'DevicesDetection_FeaturePhone',
        'console'       => 'DevicesDetection_Console',
        'tv'            => 'DevicesDetection_TV',
        'car browser'   => 'DevicesDetection_CarBbrowser',
        'smart display' => 'DevicesDetection_SmartDisplay',
        'camera'        => 'DevicesDetection_Camera'
    );
    if (isset(UserAgentParserEnhanced::$deviceTypes[$label]) && isset($translations[UserAgentParserEnhanced::$deviceTypes[$label]])) {
        return Piwik::translate($translations[UserAgentParserEnhanced::$deviceTypes[$label]]);
    } else if (isset($translations[$label])) {
        return Piwik::translate($translations[$label]);
    } else {
        return Piwik::translate('General_Unknown');
    }
}

function getDeviceTypeLogo($label)
{
    if (is_numeric($label) && isset(UserAgentParserEnhanced::$deviceTypes[$label])) {
        $label = UserAgentParserEnhanced::$deviceTypes[$label];
    }

    $label = strtolower($label);

    $deviceTypeLogos = Array(
        "desktop"       => "normal.gif",
        "smartphone"    => "smartphone.png",
        "tablet"        => "tablet.png",
        "tv"            => "tv.png",
        "feature phone" => "mobile.gif",
        "console"       => "console.gif",
        "car browser"   => "carbrowser.png",
        "camera"        => "camera.png");

    if (!array_key_exists($label, $deviceTypeLogos)) {
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
    $label = UserAgentParserEnhanced::getOsFamily($label);
    if($label !== false) {
        return $label;
    }
    return Piwik::translate('General_Unknown');
}

function getOsFamilyLogoExtended($label)
{
    if (array_key_exists($label, UserAgentParserEnhanced::$osFamilies)) {
        return getOsLogoExtended(UserAgentParserEnhanced::$osFamilies[$label][0]);
    }
    return getOsLogoExtended($label);
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

/**
 * Returns the path to the logo for the given OS
 *
 * First try to find a logo for the given short code
 * If none can be found try to find a logo for the os family
 * Return unkown logo otherwise
 *
 * @param string  $short  Shortcode or name of OS
 *
 * @return string  path to image
 */
function getOsLogoExtended($short)
{
    $path = 'plugins/UserSettings/images/os/%s.gif';

    // If name is given instead of short code, try to find matching shortcode
    if (strlen($short) > 3) {

        if (array_key_exists($short, UserAgentParserEnhanced::$osShorts)) {
            $short = UserAgentParserEnhanced::$osShorts[$short];
        } else {
            $short = substr($short, 0, 3);
        }
    }

    $family = getOsFamilyFullNameExtended($short);

    if (in_array($short, UserAgentParserEnhanced::$osShorts) && file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, $short))) {
        return sprintf($path, $short);
    } elseif (array_key_exists($family, UserAgentParserEnhanced::$osFamilies) && file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, UserAgentParserEnhanced::$osFamilies[$family][0]))) {
        return sprintf($path, UserAgentParserEnhanced::$osFamilies[$family][0]);
    }
    return sprintf($path, 'UNK');
}
