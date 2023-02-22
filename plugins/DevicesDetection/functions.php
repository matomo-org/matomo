<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection;

use Piwik\Piwik;
use DeviceDetector\Parser\OperatingSystem as OperatingSystemParser;
use DeviceDetector\Parser\Device\AbstractDeviceParser as DeviceParser;
use DeviceDetector\Parser\Client\Browser as BrowserParser;
use Piwik\Tracker\Settings;

function getBrandLogo($label)
{
    $path  = 'plugins/Morpheus/icons/dist/brand/%s.png';
    $label = preg_replace('/[^a-z0-9_\-]+/i', '_', $label);
    if (!file_exists(PIWIK_INCLUDE_PATH . '/' . sprintf($path, $label))) {
        $label = 'unk';
    }
    return sprintf($path, $label);
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
    if (!empty($label) && in_array($label, BrowserParser::getAvailableBrowsers())) {
        return getBrowserLogo($label);
    }
    if (!empty($label) && array_key_exists($label, $browserFamilies)) {
        return getBrowserLogo($browserFamilies[$label][0]);
    }
    return getBrowserLogo($label);
}

function getBrowserNameWithVersion($label)
{
    $pos      = strrpos($label, ';');
    $short    = substr($label, 0, $pos);
    $ver      = substr($label, $pos + 1);
    $browsers = BrowserParser::getAvailableBrowsers();
    if ($short && array_key_exists($short, $browsers)) {
        return trim(ucfirst($browsers[$short]) . ' ' . $ver);
    } elseif (strlen($short) > 2 && $short !== 'UNK') {
        return trim($short . ' ' . $ver);
    }
    return Piwik::translate('General_Unknown');
}

function getBrowserName($label)
{
    // Return early if the label is empty so that we don't try to manipulate an empty value
    if (empty($label)) {
        return Piwik::translate('General_Unknown');
    }

    $short    = substr($label, 0, 2);
    $browsers = BrowserParser::getAvailableBrowsers();

    if ($short && array_key_exists($short, $browsers)) {
        return trim(ucfirst($browsers[$short]));
    } elseif (strlen($label) > 2 && strpos($label, 'UNK') === false) {
        return $label;
    }

    return Piwik::translate('General_Unknown');
}

/**
 * Returns the path to the logo for the given browser
 *
 * First try to find a logo for the given short code
 * If none can be found try to find a logo for the browser family
 * Return unknown logo otherwise
 *
 * @param string $short Shortcode or name of browser
 *
 * @return string  path to image
 */
function getBrowserLogo($short)
{
    $path = 'plugins/Morpheus/icons/dist/browsers/%s.png';

    // If name is given instead of short code, try to find matching shortcode
    if (!empty($short) && strlen($short) > 2) {
        if (in_array($short, BrowserParser::getAvailableBrowsers())) {
            $flippedBrowsers = array_flip(BrowserParser::getAvailableBrowsers());
            $short           = $flippedBrowsers[$short];
        } else {
            $short = substr($short, 0, 2);
        }
    }

    if (empty($short)) {
        return sprintf($path, 'UNK');
    }

    $family = getBrowserFamilyFullName($short);

    $browserFamilies = BrowserParser::getAvailableBrowserFamilies();

    if (array_key_exists($short, BrowserParser::getAvailableBrowsers()) &&
        file_exists(PIWIK_INCLUDE_PATH . '/' . sprintf($path, $short))) {
        return sprintf($path, $short);
    }

    if (array_key_exists($family, $browserFamilies)) {
        foreach ($browserFamilies[$family] as $browserShort) {
            if (file_exists(PIWIK_INCLUDE_PATH . '/' . sprintf($path, $browserShort))) {
                return sprintf($path, $browserShort);
            }
        }
    }

    return sprintf($path, 'UNK');
}

function getDeviceBrandLabel($label)
{
    if (array_key_exists($label, DeviceParser::$deviceBrands)) {
        return ucfirst(DeviceParser::$deviceBrands[$label]);
    }

    return Piwik::translate('General_Unknown');
}

function getClientTypeMapping()
{
    return [
        1 => 'browser',
        2 => 'library',
        3 => 'feed reader',
        4 => 'mediaplayer',
        5 => 'mobile app',
        6 => 'pim',
    ];
}

function getClientTypeLabel($label)
{
    $translations = [
        'browser'     => 'DevicesDetection_ColumnBrowser',
        'library'     => 'DevicesDetection_Library',
        'feed reader' => 'DevicesDetection_FeedReader',
        'mediaplayer' => 'DevicesDetection_MediaPlayer',
        'mobile app'  => 'DevicesDetection_MobileApp',
        'pim'         => 'DevicesDetection_Pim',
    ];

    $clientTypes = getClientTypeMapping();

    if (is_numeric($label) &&
        array_key_exists($label, $clientTypes) &&
        isset($translations[$clientTypes[$label]])) {
        return Piwik::translate($translations[$clientTypes[$label]]);
    } elseif (isset($translations[$label])) {
        return Piwik::translate($translations[$label]);
    }

    return Piwik::translate('General_Unknown');
}

function getDeviceTypeLabel($label)
{
    $translations = [
        'desktop'               => 'General_Desktop',
        'smartphone'            => 'DevicesDetection_Smartphone',
        'tablet'                => 'DevicesDetection_Tablet',
        'phablet'               => 'DevicesDetection_Phablet',
        'feature phone'         => 'DevicesDetection_FeaturePhone',
        'console'               => 'DevicesDetection_Console',
        'tv'                    => 'DevicesDetection_TV',
        'car browser'           => 'DevicesDetection_CarBrowser',
        'smart display'         => 'DevicesDetection_SmartDisplay',
        'camera'                => 'DevicesDetection_Camera',
        'portable media player' => 'DevicesDetection_PortableMediaPlayer',
        'smart speaker'         => 'DevicesDetection_SmartSpeaker',
        'wearable'              => 'DevicesDetection_Wearable',
        'peripheral'            => 'DevicesDetection_Peripheral',
    ];

    $deviceTypes = DeviceParser::getAvailableDeviceTypes();

    if (is_numeric($label) &&
        in_array($label, $deviceTypes) &&
        isset($translations[array_search($label, $deviceTypes)])) {
        return Piwik::translate($translations[array_search($label, $deviceTypes)]);
    } elseif (isset($translations[$label])) {
        return Piwik::translate($translations[$label]);
    }

    return Piwik::translate('General_Unknown');
}

function getDeviceTypeLogo($label)
{
    if (is_numeric($label) && in_array($label, DeviceParser::getAvailableDeviceTypes())) {
        $label = array_search($label, DeviceParser::getAvailableDeviceTypes());
        $label = strtolower($label);
        $label = str_replace(' ', '_', $label);
    } else {
        $label = 'unknown';
    }

    return 'plugins/Morpheus/icons/dist/devices/' . $label . '.png';
}

function getModelName($label)
{
    if (strpos($label, ';') !== false) {
        [$brand, $model] = explode(';', $label, 2);
    } else {
        $brand = null;
        $model = $label;
    }
    if ($brand) {
        $brand = getDeviceBrandLabel($brand);
        if ($brand == Piwik::translate('General_Unknown')) {
            $brand = null;
        }
    }
    if (!$model) {
        $model = Piwik::translate('General_Unknown');
    } else {
        if (strpos($model, 'generic ') === 0) {
            $model = substr($model, 8);
            if ($model == 'mobile') {
                $model = Piwik::translate(
                    'DevicesDetection_GenericDevice',
                    Piwik::translate('DevicesDetection_MobileDevice')
                );
            } else {
                $model = Piwik::translate('DevicesDetection_GenericDevice', getDeviceTypeLabel($model));
            }
        }
    }
    if (empty($brand)) {
        return $model;
    }
    return $brand . ' - ' . $model;
}

function getOSFamilyFullName($label)
{
    if ($label == Settings::OS_BOT) {
        return 'Bot';
    }
    $label = OperatingSystemParser::getOsFamily(_mapLegacyOsShortCodes($label));

    if ($label == 'unknown') {
        $label = Piwik::translate('General_Unknown');
    } elseif ($label == 'Gaming Console') {
        $label = Piwik::translate('DevicesDetection_Console');
    }

    if ($label !== null) {
        return $label;
    }
    return Piwik::translate('General_Unknown');
}

function getOsFamilyLogo($label)
{
    $label      = _mapLegacyOsShortCodes($label);
    $osFamilies = OperatingSystemParser::getAvailableOperatingSystemFamilies();
    if (!empty($label) && array_key_exists($label, $osFamilies)) {
        return getOsLogo($osFamilies[$label][0]);
    }
    return getOsLogo($label);
}

function getOsFullName($label)
{
    // Return early if the label is empty so that we don't try to manipulate an empty value
    if (empty($label)) {
        return Piwik::translate('General_Unknown');
    }

    if (substr($label, 0, 3) == Settings::OS_BOT) {
        return 'Bot';
    }
    if (!empty($label) && $label != ';') {
        $os   = substr($label, 0, 3);
        $ver  = substr($label, 4, 15);
        $name = OperatingSystemParser::getNameFromId(_mapLegacyOsShortCodes($os), $ver);
        if (!empty($name)) {
            return $name;
        }
    }
    return Piwik::translate('General_Unknown');
}

function _mapLegacyOsShortCodes($shortCode): string
{
    $legacyShortCodes = [
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
    ];
    return ($shortCode && array_key_exists(
            $shortCode,
            $legacyShortCodes
        )) ? $legacyShortCodes[$shortCode] : (string)$shortCode;
}

/**
 * Returns the path to the logo for the given OS
 *
 * First try to find a logo for the given short code
 * If none can be found try to find a logo for the os family
 * Return unknown logo otherwise
 *
 * @param string $short Shortcode or name of OS
 *
 * @return string  path to image
 */
function getOsLogo($short)
{
    $path = 'plugins/Morpheus/icons/dist/os/%s.png';

    $short = _mapLegacyOsShortCodes($short);

    // If name is given instead of short code, try to find matching shortcode
    if (strlen($short) > 3) {
        if (in_array($short, OperatingSystemParser::getAvailableOperatingSystems())) {
            $short = array_search($short, OperatingSystemParser::getAvailableOperatingSystems());
        } else {
            $short = substr($short, 0, 3);
        }
    }

    $family     = getOSFamilyFullName($short);
    $osFamilies = OperatingSystemParser::getAvailableOperatingSystemFamilies();

    if (!empty($short) &&
        array_key_exists($short, OperatingSystemParser::getAvailableOperatingSystems()) &&
        file_exists(PIWIK_INCLUDE_PATH . '/' . sprintf($path, $short))) {
        return sprintf($path, $short);
    } elseif (!empty($family) &&
        array_key_exists($family, $osFamilies) &&
        file_exists(PIWIK_INCLUDE_PATH . '/' . sprintf($path, $osFamilies[$family][0]))) {
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
function getBrowserEngineName($engineName)
{
    /*
     * Map legacy types to engines
     */
    $oldTypeMapping = [
        'ie'      => 'Trident',
        'gecko'   => 'Gecko',
        'khtml'   => 'KHTML',
        'webkit'  => 'WebKit',
        'opera'   => 'Presto',
        'unknown' => '',
    ];
    if (array_key_exists($engineName, $oldTypeMapping)) {
        $engineName = $oldTypeMapping[$engineName];
    }

    $displayNames = [
        'Trident' => 'Trident (IE)',
        'Gecko'   => 'Gecko (Firefox)',
        'KHTML'   => 'KHTML (Konqueror)',
        'Presto'  => 'Presto (Opera)',
        'WebKit'  => 'WebKit (Safari)',
        'Blink'   => 'Blink (Chrome, Opera)',
    ];

    if (!empty($engineName)) {
        if (!empty($displayNames[$engineName])) {
            return $displayNames[$engineName];
        }
        return $engineName;
    }
    return Piwik::translate('General_Unknown');
}
