<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Piwik;
use DeviceDetector\Parser\OperatingSystem AS OperatingSystemParser;
use DeviceDetector\Parser\Device\DeviceParserAbstract AS DeviceParser;
use DeviceDetector\Parser\Client\Browser AS BrowserParser;

function getBrandLogo($label)
{
    $label = str_replace(" ", "_", $label);
    $path = dirname(__FILE__) . '/images/brand/' . $label . '.ico';
    if (file_exists($path)) {
        return 'plugins/DevicesDetection/images/brand/' . $label . '.ico';
    } else {
        return 'plugins/DevicesDetection/images/brand/Unknown.ico';
    }
}

function getBrowserFamilyFullNameExtended($label)
{
    foreach (BrowserParser::getAvailableBrowserFamilies() as $name => $family) {
        if (in_array($label, $family)) {
            return $name;
        }
    }
    return Piwik::translate('General_Unknown');
}

function getBrowserFamilyLogoExtended($label)
{
    $browserFamilies = BrowserParser::getAvailableBrowserFamilies();
    if (!empty($label) && array_key_exists($label, $browserFamilies)) {
        return getBrowserLogoExtended($browserFamilies[$label][0]);
    }
    return getBrowserLogoExtended($label);
}

function getBrowserNameExtended($label)
{
    $short = substr($label, 0, 2);
    $ver = substr($label, 3, 10);
    $browsers = BrowserParser::getAvailableBrowsers();
    if (array_key_exists($short, $browsers)) {
        return trim(ucfirst($browsers[$short]) . ' ' . $ver);
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

        if (in_array($short, BrowserParser::getAvailableBrowsers())) {
            $flippedBrowsers = array_flip(BrowserParser::getAvailableBrowsers());
            $short = $flippedBrowsers[$short];
        } else {
            $short = substr($short, 0, 2);
        }
    }

    $family = getBrowserFamilyFullNameExtended($short);

    $browserFamilies = BrowserParser::getAvailableBrowserFamilies();

    if (!empty($short) &&
        array_key_exists($short, BrowserParser::getAvailableBrowsers()) &&
        file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, $short))) {

        return sprintf($path, $short);

    } elseif (!empty($short) &&
        array_key_exists($family, $browserFamilies) &&
        file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, $browserFamilies[$family][0]))) {

        return sprintf($path, $browserFamilies[$family][0]);
    }
    return sprintf($path, 'UNK');
}

function getDeviceBrandLabel($label)
{
    if (array_key_exists($label, DeviceParser::$deviceBrands)) {
        return ucfirst(DeviceParser::$deviceBrands[$label]);
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

    $deviceTypes = DeviceParser::getAvailableDeviceTypes();

    if (is_numeric($label) &&
        in_array($label, $deviceTypes) &&
        isset($translations[array_search($label, $deviceTypes)])) {

        return Piwik::translate($translations[array_search($label, $deviceTypes)]);
    } else if (isset($translations[$label])) {
        return Piwik::translate($translations[$label]);
    } else {
        return Piwik::translate('General_Unknown');
    }
}

function getDeviceTypeLogo($label)
{
    if (is_numeric($label) && in_array($label, DeviceParser::getAvailableDeviceTypes())) {
        $label = array_search($label, DeviceParser::getAvailableDeviceTypes());
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
    if ($label == \Piwik\Tracker\Settings::OS_BOT) {
        return 'Bot';
    }
    $label = OperatingSystemParser::getOsFamily($label);
    if($label !== false) {
        return $label;
    }
    return Piwik::translate('General_Unknown');
}

function getOsFamilyLogoExtended($label)
{
    $osFamilies = OperatingSystemParser::getAvailableOperatingSystemFamilies();
    if (!empty($label) && array_key_exists($label, $osFamilies)) {
        return getOsLogoExtended($osFamilies[$label][0]);
    }
    return getOsLogoExtended($label);
}

function getOsFullNameExtended($label)
{
    if (substr($label, 0, 3) == \Piwik\Tracker\Settings::OS_BOT) {
        return 'Bot';
    }
    if (!empty($label) && $label != ";") {
        $os = substr($label, 0, 3);
        $ver = substr($label, 4, 15);
        $name = OperatingSystemParser::getNameFromId($os, $ver);
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

        if (in_array($short, OperatingSystemParser::getAvailableOperatingSystems())) {
            $short = array_search($short, OperatingSystemParser::getAvailableOperatingSystems());
        } else {
            $short = substr($short, 0, 3);
        }
    }

    $family = getOsFamilyFullNameExtended($short);
    $osFamilies = OperatingSystemParser::getAvailableOperatingSystemFamilies();

    if (!empty($short) &&
        array_key_exists($short, OperatingSystemParser::getAvailableOperatingSystems()) &&
        file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, $short))) {

        return sprintf($path, $short);

    } elseif (!empty($family) &&
        array_key_exists($family, $osFamilies) &&
        file_exists(PIWIK_INCLUDE_PATH.'/'.sprintf($path, $osFamilies[$family][0]))) {

        return sprintf($path, $osFamilies[$family][0]);
    }
    return sprintf($path, 'UNK');
}
