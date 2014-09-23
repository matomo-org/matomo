<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserSettings;

use Piwik\Piwik;
use Piwik\Tracker\Request;
use UserAgentParser;

/**
 * @see libs/UserAgentParser/UserAgentParser.php
 */
require_once PIWIK_INCLUDE_PATH . '/libs/UserAgentParser/UserAgentParser.php';

function getPluginsLogo($oldLabel)
{
    if ($oldLabel == Piwik::translate('General_Others')) {
        return false;
    }
    return 'plugins/UserSettings/images/plugins/' . $oldLabel . '.gif';
}

function getOSLabel($osId)
{
    $osName = UserAgentParser::getOperatingSystemNameFromId($osId);
    if ($osName !== false) {
        return $osName;
    }
    if ($osId == 'UNK') {
        return Piwik::translate('General_Unknown');
    }
    return $osId;
}

function getOSShortLabel($osId)
{
    $osShortName = UserAgentParser::getOperatingSystemShortNameFromId($osId);
    if ($osShortName !== false) {
        return $osShortName;
    }
    if ($osId == 'UNK') {
        return Piwik::translate('General_Unknown');
    }
    return $osId;
}

function getOSFamily($osLabel)
{
    $osId = UserAgentParser::getOperatingSystemIdFromName($osLabel);
    $osFamily = UserAgentParser::getOperatingSystemFamilyFromId($osId);

    if ($osFamily == 'unknown') {
        $osFamily = Piwik::translate('General_Unknown');
    } else if ($osFamily == 'Gaming Console') {
        $osFamily = Piwik::translate('UserSettings_GamingConsole');
    }

    return $osFamily;
}

function getDeviceTypeFromOS($osLabel)
{
    $osId = UserAgentParser::getOperatingSystemIdFromName($osLabel);
    $osFamily = UserAgentParser::getOperatingSystemFamilyFromId($osId);

    // NOTE: translations done in another filter
    switch ($osFamily) {
        case 'Windows':
        case 'Linux':
        case 'Mac':
        case 'Unix':
        case 'Other':
        case 'Gaming Console':
            return 'General_Desktop';
        case 'iOS':
        case 'Android':
        case 'Windows Mobile':
        case 'Other Mobile':
        case 'Mobile Gaming Console':
            return 'General_Mobile';
        default:
            return 'General_Unknown';
    }
}

function getBrowserTypeLabel($oldLabel)
{
    if (isset(UserSettings::$browserType_display[$oldLabel])) {
        return UserSettings::$browserType_display[$oldLabel];
    }
    if ($oldLabel == 'unknown') {
        return Piwik::translate('General_Unknown');
    }
    return $oldLabel;
}

function getConfigurationLabel($str)
{
    if (strpos($str, ';') === false) {
        return $str;
    }
    $values = explode(";", $str);

    $os = getOSLabel($values[0]);
    $name = $values[1];
    $browser = UserAgentParser::getBrowserNameFromId($name);
    if ($browser === false) {
        $browser = Piwik::translate('General_Unknown');
    }
    $resolution = $values[2];
    return $os . " / " . $browser . " / " . $resolution;
}

function getBrowserLabel($oldLabel)
{
    $browserId = getBrowserId($oldLabel);
    $version = getBrowserVersion($oldLabel);
    $browserName = UserAgentParser::getBrowserNameFromId($browserId);
    if ($browserName !== false) {
        return $browserName . " " . $version;
    }
    if ($browserId == 'UNK') {
        return Piwik::translate('General_Unknown');
    }
    return $oldLabel;
}

function getBrowserShortLabel($oldLabel)
{
    $browserId = getBrowserId($oldLabel);
    $version = getBrowserVersion($oldLabel);
    $browserName = UserAgentParser::getBrowserShortNameFromId($browserId);
    if ($browserName !== false) {
        return $browserName . " " . $version;
    }
    if ($browserId == 'UNK') {
        return Piwik::translate('General_Unknown');
    }
    return $oldLabel;
}

function getBrowserId($str)
{
    return substr($str, 0, strpos($str, ';'));
}

function getBrowserVersion($str)
{
    return substr($str, strpos($str, ';') + 1);
}

function getLogoImageFromId($dir, $id)
{
    $path = $dir . '/' . $id . '.gif';
    if (file_exists(PIWIK_INCLUDE_PATH . '/' . $path)) {
        return $path;
    } else {
        return $dir . '/UNK.gif';
    }
}

function getBrowsersLogo($label)
{
    $id = getBrowserId($label);
    // For aggregated row 'Others'
    if (empty($id)) {
        $id = 'UNK';
    }
    return getLogoImageFromId('plugins/UserSettings/images/browsers', $id);
}

function getOSLogo($label)
{
    // For aggregated row 'Others'
    if (empty($label)) {
        $label = 'UNK';
    }
    return getLogoImageFromId('plugins/UserSettings/images/os', $label);
}

function getScreensLogo($label)
{
    return 'plugins/UserSettings/images/screens/' . $label . '.gif';
}

function getDeviceTypeImg($oldOSImage, $osFamilyLabel)
{
    switch ($osFamilyLabel) {
        case 'General_Desktop':
            return 'plugins/UserSettings/images/screens/normal.gif';
        case 'General_Mobile':
            return 'plugins/UserSettings/images/screens/mobile.gif';
        case 'General_Unknown':
        default:
            return 'plugins/UserSettings/images/os/UNK.gif';
    }
}

function getScreenTypeFromResolution($resolution)
{
    if ($resolution === Request::UNKNOWN_RESOLUTION) {
        return $resolution;
    }

    $width = intval(substr($resolution, 0, strpos($resolution, 'x')));
    $height = intval(substr($resolution, strpos($resolution, 'x') + 1));
    $ratio = Piwik::secureDiv($width, $height);

    if ($width < 640) {
        $name = 'mobile';
    } elseif ($ratio < 1.4) {
        $name = 'normal';
    } else if ($ratio < 2) {
        $name = 'wide';
    } else {
        $name = 'dual';
    }
    return $name;
}

function getBrowserFamily($browserLabel)
{
    $familyNameToUse = UserAgentParser::getBrowserFamilyFromId(substr($browserLabel, 0, 2));
    return $familyNameToUse;
}

/**
 * Extracts the browser name from a string with the browser name and version.
 */
function getBrowserFromBrowserVersion($browserWithVersion)
{
    if (preg_match("/(.+) [0-9]+(?:\.[0-9]+)?$/", $browserWithVersion, $matches) === 0) {
        return $browserWithVersion;
    }

    return $matches[1];
}

/**
 * Returns the given language code to translated language name
 *
 * @param $label
 *
 * @return string
 */
function languageTranslate($label)
{
    if ($label == '' || $label == 'xx') {
        return Piwik::translate('General_Unknown');
    }

    $key = 'UserSettings_Language_' . $label;

    $translation = Piwik::translate($key);

    // Show language code if unknown code
    if ($translation == $key) {
        $translation = Piwik::translate('UserSettings_LanguageCode') . ' ' . $label;
    }

    return $translation;
}

/**
 * @param $label
 * @return string
 */
function languageTranslateWithCode($label)
{
    $ex = explode('-', $label);
    $lang = languageTranslate($ex[0]);

    if (count($ex) == 2 && $ex[0] != $ex[1]) {
        $countryKey = 'UserCountry_country_' . $ex[1];
        $country = Piwik::translate($countryKey);

        if ($country == $countryKey) {
            return sprintf("%s (%s)", $lang, $ex[0]);
        }

        return sprintf("%s - %s (%s)", $lang, $country, $label);

    } else {
        return sprintf("%s (%s)", $lang, $ex[0]);
    }

}

/**
 * @param $lang
 * @return mixed
 */
function groupByLangCallback($lang)
{
    $ex = explode('-', $lang);
    return $ex[0];
}