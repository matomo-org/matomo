<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Translate\Validate;

use Piwik\Common;

/**
 */
class CoreTranslations extends ValidateAbstract
{
    /**
     * Error States
     */
    const ERRORSTATE_LOCALEREQUIRED = 'Locale required';
    const ERRORSTATE_TRANSLATORINFOREQUIRED = 'Translator info required';
    const ERRORSTATE_TRANSLATOREMAILREQUIRED = 'Translator email required';
    const ERRORSTATE_LAYOUTDIRECTIONINVALID = 'Layout direction must be rtl or ltr';
    const ERRORSTATE_LOCALEINVALID = 'Locale is invalid';
    const ERRORSTATE_LOCALEINVALIDLANGUAGE = 'Locale is invalid - invalid language code';
    const ERRORSTATE_LOCALEINVALIDCOUNTRY = 'Locale is invalid - invalid country code';

    protected $baseTranslations = array();

    /**
     * Sets base translations
     *
     * @param array $baseTranslations
     */
    public function __construct($baseTranslations = array())
    {
        $this->baseTranslations = $baseTranslations;
    }

    /**
     * Validates the given translations
     *  * There need to be more than 250 translations presen
     *  * Locale, TranslatorName and TranslatorEmail needs to be set in plugin General
     *  * LayoutDirection needs to be ltr or rtl if present
     *  * Locale must be valid (format, language & country)
     *
     * @param array $translations
     *
     * @return boolean
     */
    public function isValid($translations)
    {
        $this->message = null;

        if (empty($translations['General']['Locale'])) {
            $this->message = self::ERRORSTATE_LOCALEREQUIRED;
            return false;
        }

        if (empty($translations['General']['TranslatorName'])) {
            $this->message = self::ERRORSTATE_TRANSLATORINFOREQUIRED;
            return false;
        }

        if (empty($translations['General']['TranslatorEmail'])) {
            $this->message = self::ERRORSTATE_TRANSLATOREMAILREQUIRED;
            return false;
        }

        if (!empty($translations['General']['LayoutDirection']) &&
            !in_array($translations['General']['LayoutDirection'], array('ltr', 'rtl'))
        ) {
            $this->message = self::ERRORSTATE_LAYOUTDIRECTIONINVALID;
            return false;
        }

        $allLanguages = Common::getLanguagesList();
        $allCountries = Common::getCountriesList();

        if (!preg_match('/^([a-z]{2})_([A-Z]{2})\.UTF-8$/', $translations['General']['Locale'], $matches)) {
            $this->message = self::ERRORSTATE_LOCALEINVALID;
            return false;
        } else if (!array_key_exists($matches[1], $allLanguages)) {
            $this->message = self::ERRORSTATE_LOCALEINVALIDLANGUAGE;
            return false;
        } else if (!array_key_exists(strtolower($matches[2]), $allCountries)) {
            $this->message = self::ERRORSTATE_LOCALEINVALIDCOUNTRY;
            return false;
        }

        return true;
    }
}
