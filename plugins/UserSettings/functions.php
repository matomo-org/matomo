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

function getPluginsLogo($oldLabel)
{
    if ($oldLabel == Piwik::translate('General_Others')) {
        return false;
    }
    return 'plugins/UserSettings/images/plugins/' . $oldLabel . '.gif';
}

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

function getLogoImageFromId($dir, $id)
{
    $path = $dir . '/' . $id . '.gif';
    if (file_exists(PIWIK_INCLUDE_PATH . '/' . $path)) {
        return $path;
    } else {
        return $dir . '/UNK.gif';
    }
}

function getScreensLogo($label)
{
    return 'plugins/UserSettings/images/screens/' . $label . '.gif';
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