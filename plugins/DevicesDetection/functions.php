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
    $label = preg_replace("/[^a-z0-9_-]+/i", "_", $label);
    $path = dirname(__FILE__) . '/images/brand/' . $label . '.ico';
    if (file_exists($path)) {
        return 'plugins/DevicesDetection/images/brand/' . $label . '.ico';
    } else {
        return 'plugins/DevicesDetection/images/brand/Unknown.ico';
    }
}

function getBrowserFamilyFullName($label)
{
    foreach (BrowserParser::getAvailableBrowserFamilies() as $name => $family) {
        if (in_array($label, $family)) {
            return $name;
        }
    }
    return Piwik::translate('General_Unknown');
}

function getBrowserFamilyLogo($label)
{
    $browserFamilies = BrowserParser::getAvailableBrowserFamilies();
    if (!empty($label) && array_key_exists($label, $browserFamilies)) {
        return getBrowserLogo($browserFamilies[$label][0]);
    }
    return getBrowserLogo($label);
}

function getBrowserNameWithVersion($label)
{
    $short = substr($label, 0, 2);
    $ver = substr($label, 3, 10);
    $browsers = BrowserParser::getAvailableBrowsers();
    if ($short && array_key_exists($short, $browsers)) {
        return trim(ucfirst($browsers[$short]) . ' ' . $ver);
    } else {
        return Piwik::translate('General_Unknown');
    }
}

function getBrowserName($label)
{
    $short = substr($label, 0, 2);
    $browsers = BrowserParser::getAvailableBrowsers();
    if ($short && array_key_exists($short, $browsers)) {
        return trim(ucfirst($browsers[$short]));
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
function getBrowserLogo($short)
{
    $path = 'plugins/DevicesDetection/images/browsers/%s.gif';

    // If name is given instead of short code, try to find matching shortcode
    if (strlen($short) > 2) {

        if (in_array($short, BrowserParser::getAvailableBrowsers())) {
            $flippedBrowsers = array_flip(BrowserParser::getAvailableBrowsers());
            $short = $flippedBrowsers[$short];
        } else {
            $short = substr($short, 0, 2);
        }
    }

    $family = getBrowserFamilyFullName($short);

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
        'phablet'       => 'DevicesDetection_Phablet',
        'feature phone' => 'DevicesDetection_FeaturePhone',
        'console'       => 'DevicesDetection_Console',
        'tv'            => 'DevicesDetection_TV',
        'car browser'   => 'DevicesDetection_CarBrowser',
        'smart display' => 'DevicesDetection_SmartDisplay',
        'camera'        => 'DevicesDetection_Camera',
        'portable media player' => 'DevicesDetection_PortableMediaPlayer',
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
    if (strpos($label, ';') !== false) {
        list($brand, $model) = explode(';', $label, 2);
    } else {
        $brand = null;
        $model = $label;
    }
    if (!$model) {
        $model = Piwik::translate('General_Unknown');
    }
    if (!$brand) {
        return $model;
    }
    return getDeviceBrandLabel($brand) . ' - ' . $model;
}

function getOSFamilyFullName($label)
{
    if ($label == \Piwik\Tracker\Settings::OS_BOT) {
        return 'Bot';
    }
    $label = OperatingSystemParser::getOsFamily(_mapLegacyOsShortCodes($label));

    if ($label == 'unknown') {
        $label = Piwik::translate('General_Unknown');
    } else if ($label == 'Gaming Console') {
        $label = Piwik::translate('DevicesDetection_Console');
    }

    if ($label !== false) {
        return $label;
    }
    return Piwik::translate('General_Unknown');
}

function getOsFamilyLogo($label)
{
    $label = _mapLegacyOsShortCodes($label);
    $osFamilies = OperatingSystemParser::getAvailableOperatingSystemFamilies();
    if (!empty($label) && array_key_exists($label, $osFamilies)) {
        return getOsLogo($osFamilies[$label][0]);
    }
    return getOsLogo($label);
}

function getOsFullName($label)
{
    if (substr($label, 0, 3) == \Piwik\Tracker\Settings::OS_BOT) {
        return 'Bot';
    }
    if (!empty($label) && $label != ";") {
        $os = substr($label, 0, 3);
        $ver = substr($label, 4, 15);
        $name = OperatingSystemParser::getNameFromId(_mapLegacyOsShortCodes($os), $ver);
        if (!empty($name)) {
            return $name;
        }
    }
    return Piwik::translate('General_Unknown');
}

function _mapLegacyOsShortCodes($shortCode)
{
    $legacyShortCodes = array(
        'IPA' => 'IOS', // iPad => iOS
        'IPH' => 'IOS', // iPhone => iOS
        'IPD' => 'IOS', // iPod => iOS
        'WIU' => 'WII', // WiiU => Nintendo
        '3DS' => 'NDS', // Nintendo 3DS => Nintendo Mobile
        'DSI' => 'NDS', // Nintendo DSi => Nintendo Mobile
        'PSV' => 'PSP', // PlayStation Vita => PlayStation Portable
        'MAE' => 'SMG', // Maemo => MeeGo
        'W10' => 'WIN',
        'W2K' => 'WIN',
        'W31' => 'WIN',
        'WI7' => 'WIN',
        'WI8' => 'WIN',
        'W81' => 'WIN',
        'W95' => 'WIN',
        'W98' => 'WIN',
        'WME' => 'WIN',
        'WNT' => 'WIN',
        'WS3' => 'WIN',
        'WVI' => 'WIN',
        'WXP' => 'WIN',
        //'VMS' => '', // OpenVMS => ??
    );
    return ($shortCode && array_key_exists($shortCode, $legacyShortCodes)) ? $legacyShortCodes[$shortCode] : $shortCode;
}

/**
 * Returns the path to the logo for the given OS
 *
 * First try to find a logo for the given short code
 * If none can be found try to find a logo for the os family
 * Return unknown logo otherwise
 *
 * @param string  $short  Shortcode or name of OS
 *
 * @return string  path to image
 */
function getOsLogo($short)
{
    $path = 'plugins/DevicesDetection/images/os/%s.gif';

    $short = _mapLegacyOsShortCodes($short);

    // If name is given instead of short code, try to find matching shortcode
    if (strlen($short) > 3) {

        if (in_array($short, OperatingSystemParser::getAvailableOperatingSystems())) {
            $short = array_search($short, OperatingSystemParser::getAvailableOperatingSystems());
        } else {
            $short = substr($short, 0, 3);
        }
    }

    $family = getOSFamilyFullName($short);
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

/**
 * Returns the display name for a browser engine
 *
 * @param $engineName
 *
 * @return string
 */
function getBrowserEngineName($engineName) {
    /*
     * Map leagcy types to engines
     */
    $oldTypeMapping = array(
        'ie'     => 'Trident',
        'gecko'  => 'Gecko',
        'khtml'  => 'KHTML',
        'webkit' => 'WebKit',
        'opera'  => 'Presto',
        'unknown' => ''
    );
    if (array_key_exists($engineName, $oldTypeMapping)) {
        $engineName = $oldTypeMapping[$engineName];
    }

    $displayNames = array(
        'Trident' => 'Trident (IE)',
        'Gecko' => 'Gecko (Firefox)',
        'KHTML' => 'KHTML (Konqueror)',
        'Presto' => 'Presto (Opera)',
        'WebKit' => 'WebKit (Safari, Chrome)',
        'Blink' => 'Blink (Chrome, Opera)'
    );

    if (!empty($engineName)) {
        if (!empty($displayNames[$engineName])) {
            return $displayNames[$engineName];
        }
        return $engineName;
    }
    return Piwik::translate('General_Unknown');
}
