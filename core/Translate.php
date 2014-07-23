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

/**
 */
class Translate
{
    private static $languageToLoad = null;
    private static $loadedLanguage = false;

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

    public static function loadEnglishTranslation()
    {
        self::loadCoreTranslationFile('en');
    }

    public static function unloadEnglishTranslation()
    {
        $GLOBALS['Piwik_translations'] = array();
    }

    public static function reloadLanguage($language = false)
    {
        if (empty($language)) {
            $language = self::getLanguageToLoad();
        }
        self::unloadEnglishTranslation();
        self::loadEnglishTranslation();
        self::loadCoreTranslation($language);
        \Piwik\Plugin\Manager::getInstance()->loadPluginTranslations($language);
    }

    /**
     * Reads the specified code translation file in memory.
     *
     * @param bool|string $language 2 letter language code. If not specified, will detect current user translation, or load default translation.
     * @return void
     */
    public static function loadCoreTranslation($language = false)
    {
        if (empty($language)) {
            $language = self::getLanguageToLoad();
        }
        if (self::$loadedLanguage == $language) {
            return;
        }
        self::loadCoreTranslationFile($language);
    }

    private static function loadCoreTranslationFile($language)
    {
        if(empty($language)) {
            return;
        }
        $path = PIWIK_INCLUDE_PATH . '/lang/' . $language . '.json';
        if (!Filesystem::isValidFilename($language) || !is_readable($path)) {
            throw new Exception(Piwik::translate('General_ExceptionLanguageFileNotFound', array($language)));
        }
        $data = file_get_contents($path);
        $translations = json_decode($data, true);
        self::mergeTranslationArray($translations);
        self::setLocale();
        self::$loadedLanguage = $language;
    }

    public static function mergeTranslationArray($translation)
    {
        if (!isset($GLOBALS['Piwik_translations'])) {
            $GLOBALS['Piwik_translations'] = array();
        }
        if (empty($translation)) {
            return;
        }
        // we could check that no string overlap here
        $GLOBALS['Piwik_translations'] = array_replace_recursive($GLOBALS['Piwik_translations'], $translation);
    }

    /**
     * @return string the language filename prefix, eg 'en' for english
     * @throws exception if the language set is not a valid filename
     */
    public static function getLanguageToLoad()
    {
        if (is_null(self::$languageToLoad)) {
            $lang = Common::getRequestVar('language', '', 'string');

            /**
             * Triggered when the current user's language is requested.
             *
             * By default the current language is determined by the **language** query
             * parameter. Plugins can override this logic by subscribing to this event.
             *
             * **Example**
             *
             *     public function getLanguage(&$lang)
             *     {
             *         $client = new My3rdPartyAPIClient();
             *         $thirdPartyLang = $client->getLanguageForUser(Piwik::getCurrentUserLogin());
             *
             *         if (!empty($thirdPartyLang)) {
             *             $lang = $thirdPartyLang;
             *         }
             *     }
             *
             * @param string &$lang The language that should be used for the current user. Will be
             *                      initialized to the value of the **language** query parameter.
             */
            Piwik::postEvent('User.getLanguage', array(&$lang));

            self::$languageToLoad = $lang;
        }

        return self::$languageToLoad;
    }

    /** Reset the cached language to load. Used in tests. */
    public static function reset()
    {
        self::$languageToLoad = null;
    }

    private static function isALanguageLoaded() {
        return !empty($GLOBALS['Piwik_translations']);
    }

    /**
     * Either the name of the currently loaded language such as 'en' or 'de' or null if no language is loaded at all.
     * @return bool|string
     */
    public static function getLanguageLoaded()
    {
        if (!self::isALanguageLoaded()) {
            return null;
        }

        return self::$loadedLanguage;
    }

    public static function getLanguageDefault()
    {
        return Config::getInstance()->General['default_language'];
    }

    /**
     * Generate javascript translations array
     */
    public static function getJavascriptTranslations()
    {
        $translations = & $GLOBALS['Piwik_translations'];

        $clientSideTranslations = array();
        foreach (self::getClientSideTranslationKeys() as $key) {
            list($plugin, $stringName) = explode("_", $key, 2);
            $clientSideTranslations[$key] = $translations[$plugin][$stringName];
        }

        $js = 'var translations = ' . Common::json_encode($clientSideTranslations) . ';';
        $js .= "\n" . 'if(typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }' .
            'for(var i in translations) { piwik_translations[i] = translations[i];} ';
        return $js;
    }

    /**
     * Returns the list of client side translations by key. These translations will be outputted
     * to the translation JavaScript.
     */
    private static function getClientSideTranslationKeys()
    {
        $result = array();

        /**
         * Triggered before generating the JavaScript code that allows i18n strings to be used
         * in the browser.
         *
         * Plugins should subscribe to this event to specify which translations
         * should be available to JavaScript.
         *
         * Event handlers should add whole translation keys, ie, keys that include the plugin name.
         *
         * **Example**
         *
         *     public function getClientSideTranslationKeys(&$result)
         *     {
         *         $result[] = "MyPlugin_MyTranslation";
         *     }
         *
         * @param array &$result The whole list of client side translation keys.
         */
        Piwik::postEvent('Translate.getClientSideTranslationKeys', array(&$result));

        $result = array_unique($result);

        return $result;
    }

    /**
     * Set locale
     *
     * @see http://php.net/setlocale
     */
    private static function setLocale()
    {
        $locale = $GLOBALS['Piwik_translations']['General']['Locale'];
        $locale_variant = str_replace('UTF-8', 'UTF8', $locale);
        setlocale(LC_ALL, $locale, $locale_variant);
        setlocale(LC_CTYPE, '');
    }

    public static function findTranslationKeyForTranslation($translation)
    {
        if (empty($GLOBALS['Piwik_translations'])) {
            return;
        }

        foreach ($GLOBALS['Piwik_translations'] as $key => $translations) {
            $possibleKey = array_search($translation, $translations);
            if (!empty($possibleKey)) {
                return $key . '_' . $possibleKey;
            }
        }
    }
}
