<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Translate\Validate;

use Piwik\Translate\Validate\ValidateAbstract;
use Piwik\Common;

/**
 * @package Piwik
 * @subpackage Piwik_Translate
 */
class CoreTranslations extends ValidateAbstract
{
    /**
     * Error States
     */
    const __ERRORSTATE_MINIMUMTRANSLATIONS__        = 'At least 250 translations required';
    const __ERRORSTATE_LOCALEREQUIRED__             = 'Locale required';
    const __ERRORSTATE_TRANSLATORINFOREQUIRED__     = 'Translator info required';
    const __ERRORSTATE_TRANSLATOREMAILREQUIRED__    = 'Translator email required';
    const __ERRORSTATE_LAYOUTDIRECTIONINVALID__     = 'Layout direction must be rtl or ltr';
    const __ERRORSTATE_LOCALEINVALID__              = 'Locale is invalid';
    const __ERRORSTATE_LOCALEINVALIDLANGUAGE__      = 'Locale is invalid - invalid language code';
    const __ERRORSTATE_LOCALEINVALIDCOUNTRY__       = 'Locale is invalid - invalid country code';

    protected $_baseTranslations = array();

    /**
     * Sets base translations
     *
     * @param array $baseTranslations
     */
    public function __construct($baseTranslations=array())
    {
        $this->_baseTranslations = $baseTranslations;
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
     *
     */
    public function isValid($translations)
    {
        $this->_message = null;

        if (250 > count($translations, COUNT_RECURSIVE)) {
            $this->_message = self::__ERRORSTATE_MINIMUMTRANSLATIONS__;
            return false;
        }

        if (empty($translations['General']['Locale'])) {
            $this->_message = self::__ERRORSTATE_LOCALEREQUIRED__;
            return false;
        }

        if (empty($translations['General']['TranslatorName'])) {
            $this->_message = self::__ERRORSTATE_TRANSLATORINFOREQUIRED__;
            return false;
        }

        if (empty($translations['General']['TranslatorEmail'])) {
            $this->_message = self::__ERRORSTATE_TRANSLATOREMAILREQUIRED__;
            return false;
        }

        if (!empty($translations['General']['LayoutDirection']) &&
            !in_array($translations['General']['LayoutDirection'], array('ltr', 'rtl'))
        ) {
            $this->_message = self::__ERRORSTATE_LAYOUTDIRECTIONINVALID__;
            return false;
        }

        $allLanguages = Common::getLanguagesList();
        $allCountries = Common::getCountriesList();

        if (!preg_match('/^([a-z]{2})_([A-Z]{2})\.UTF-8$/', $translations['General']['Locale'], $matches)) {
            $this->_message = self::__ERRORSTATE_LOCALEINVALID__;
            return false;
        } else if (!array_key_exists($matches[1], $allLanguages)) {
            $this->_message = self::__ERRORSTATE_LOCALEINVALIDLANGUAGE__;
            return false;
        } else if (!array_key_exists(strtolower($matches[2]), $allCountries)) {
            $this->_message = self::__ERRORSTATE_LOCALEINVALIDCOUNTRY__;
            return false;
        }

        return true;
    }
}