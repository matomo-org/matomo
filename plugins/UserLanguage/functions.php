<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserLanguage;

use Piwik\Piwik;

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

    $language = Piwik::translate('Intl_Language_'.$label);

    if ($language != 'Intl_Language_'.$label) {
        return $language;
    }

    $key = 'UserLanguage_Language_' . $label;

    $translation = Piwik::translate($key);

    // Show language code if unknown code
    if ($translation == $key) {
        $translation = Piwik::translate('UserLanguage_LanguageCode') . ' ' . $label;
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
        $country = Piwik::translate('Intl_Country_'.strtoupper($ex[1]));

        if ($country == 'Intl_Country_'.strtoupper($ex[1])) {
            $country = Piwik::translate($countryKey);
        }

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