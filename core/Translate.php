<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager;
use Piwik\Translation\Translator;

/**
 * @deprecated Use Piwik\Translation\Translator instead.
 * @see \Piwik\Translation\Translator
 */
class Translate
{
    /**
     * Clean a string that may contain HTML special chars, single/double quotes, HTML entities, leading/trailing whitespace
     *
     * @param string $s
     * @return string
     */
    public static function clean($s)
    {
        return html_entity_decode(trim($s), ENT_QUOTES, 'UTF-8');
    }

    /**
     * @deprecated
     */
    public static function loadEnglishTranslation()
    {
        self::loadAllTranslations();
    }

    /**
     * @deprecated
     */
    public static function unloadEnglishTranslation()
    {
        self::reset();
    }

    /**
     * @deprecated
     */
    public static function reloadLanguage($language = false)
    {
    }

    /**
     * Reads the specified code translation file in memory.
     *
     * @param bool|string $language 2 letter language code. If not specified, will detect current user translation, or load default translation.
     * @return void
     */
    public static function loadCoreTranslation($language = false)
    {
        self::getTranslator()->addDirectory(PIWIK_INCLUDE_PATH . '/lang');
    }

    /**
     * @deprecated
     */
    public static function mergeTranslationArray($translation)
    {
    }

    /**
     * @return string the language filename prefix, eg 'en' for english
     * @throws exception if the language set is not a valid filename
     */
    public static function getLanguageToLoad()
    {
        return self::getTranslator()->getCurrentLanguage();
    }

    /** Reset the cached language to load. Used in tests. */
    public static function reset()
    {
        self::getTranslator()->reset();
    }

    /**
     * Either the name of the currently loaded language such as 'en' or 'de' or null if no language is loaded at all.
     * @return bool|string
     */
    public static function getLanguageLoaded()
    {
        return self::getTranslator()->getCurrentLanguage();
    }

    public static function getLanguageDefault()
    {
        return self::getTranslator()->getDefaultLanguage();
    }

    /**
     * Generate javascript translations array
     */
    public static function getJavascriptTranslations()
    {
        return self::getTranslator()->getJavascriptTranslations();
    }

    public static function findTranslationKeyForTranslation($translation)
    {
        return self::getTranslator()->findTranslationKeyForTranslation($translation);
    }

    /**
     * @return Translator
     */
    private static function getTranslator()
    {
        return StaticContainer::get('Piwik\Translation\Translator');
    }

    public static function loadAllTranslations()
    {
        self::loadCoreTranslation();
        Manager::getInstance()->loadPluginTranslations();
    }
}
