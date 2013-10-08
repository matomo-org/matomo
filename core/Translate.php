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
namespace Piwik;
use Exception;

/**
 * @package Piwik
 */
class Translate
{
    const GET_CLIENT_SIDE_TRANSLATION_KEYS_EVENT = 'Translate.getClientSideTranslationKeys';

    static private $instance = null;
    static private $languageToLoad = null;
    private $loadedLanguage = false;

    /**
     * @return \Piwik\Translate
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Clean a string that may contain HTML special chars, single/double quotes, HTML entities, leading/trailing whitespace
     *
     * @param string $s
     * @return string
     */
    static public function clean($s)
    {
        return html_entity_decode(trim($s), ENT_QUOTES, 'UTF-8');
    }

    public function loadEnglishTranslation()
    {
        $this->loadCoreTranslationFile('en');
    }

    public function unloadEnglishTranslation()
    {
        $GLOBALS['Piwik_translations'] = array();
    }

    public function reloadLanguage($language = false)
    {
        if (empty($language)) {
            $language = $this->getLanguageToLoad();
        }
        $this->unloadEnglishTranslation();
        $this->loadEnglishTranslation();
        $this->loadCoreTranslation($language);
        PluginsManager::getInstance()->loadPluginTranslations($language);
    }

    /**
     * Reads the specified code translation file in memory.
     *
     * @param bool|string $language 2 letter language code. If not specified, will detect current user translation, or load default translation.
     * @return void
     */
    public function loadCoreTranslation($language = false)
    {
        if (empty($language)) {
            $language = $this->getLanguageToLoad();
        }
        if ($this->loadedLanguage == $language) {
            return;
        }
        $this->loadCoreTranslationFile($language);
    }

    private function loadCoreTranslationFile($language)
    {
        $path = PIWIK_INCLUDE_PATH . '/lang/' . $language . '.json';
        if (!Filesystem::isValidFilename($language) || !is_readable($path)) {
            throw new Exception(Piwik_TranslateException('General_ExceptionLanguageFileNotFound', array($language)));
        }
        $data = file_get_contents($path);
        $translations = json_decode($data, true);
        $this->mergeTranslationArray($translations);
        $this->setLocale();
        $this->loadedLanguage = $language;
    }

    public function mergeTranslationArray($translation)
    {
        if (!isset($GLOBALS['Piwik_translations'])) {
            $GLOBALS['Piwik_translations'] = array();
        }
        // we could check that no string overlap here
        $GLOBALS['Piwik_translations'] = array_replace_recursive($GLOBALS['Piwik_translations'], $translation);
    }

    /**
     * @return string the language filename prefix, eg 'en' for english
     * @throws exception if the language set is not a valid filename
     */
    public function getLanguageToLoad()
    {
        if (is_null(self::$languageToLoad)) {
            $lang = Common::getRequestVar('language', '', 'string');

            Piwik_PostEvent('User.getLanguage', array(&$lang));

            self::$languageToLoad = $lang;
        }

        return self::$languageToLoad;
    }

    /** Reset the cached language to load. Used in tests. */
    static public function reset()
    {
        self::$languageToLoad = null;
    }

    public function getLanguageLoaded()
    {
        return $this->loadedLanguage;
    }

    public function getLanguageDefault()
    {
        return Config::getInstance()->General['default_language'];
    }

    /**
     * Generate javascript translations array
     */
    public function getJavascriptTranslations()
    {
        $translations = &$GLOBALS['Piwik_translations'];

        $clientSideTranslations = array();
        foreach ($this->getClientSideTranslationKeys() as $key) {
            list($plugin, $stringName) = explode("_", $key, 2);
            $clientSideTranslations[$key] = $translations[$plugin][$stringName];
        }

        $js = 'var translations = ' . Common::json_encode($clientSideTranslations) . ';';
        $js .= "\n" . 'if(typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }' .
            'for(var i in translations) { piwik_translations[i] = translations[i];} ';
        $js .= 'function _pk_translate(translationStringId) { ' .
            'if( typeof(piwik_translations[translationStringId]) != \'undefined\' ){  return piwik_translations[translationStringId]; }' .
            'return "The string "+translationStringId+" was not loaded in javascript. Make sure it is added in the Translate.getClientSideTranslationKeys hook.";}';
        return $js;
    }

    /**
     * Returns the list of client side translations by key. These translations will be outputted
     * to the translation JavaScript.
     */
    private function getClientSideTranslationKeys()
    {
        $result = array();

        /**
         * This event is called before generating the JavaScript code that allows other JavaScript
         * to access Piwik i18n strings. Plugins should handle this event to specify which translations
         * should be available to JavaScript code.
         *
         * Event handlers should add whole translation keys, ie, keys that include the plugin name.
         * For example:
         *
         * ```
         * public function getClientSideTranslationKeys(&$result)
         * {
         *     $result[] = "MyPlugin_MyTranslation";
         * }
         * ```
         */
        Piwik_PostEvent(self::GET_CLIENT_SIDE_TRANSLATION_KEYS_EVENT, array(&$result));

        $result = array_unique($result);

        return $result;
    }

    /**
     * Set locale
     *
     * @see http://php.net/setlocale
     */
    private function setLocale()
    {
        $locale = $GLOBALS['Piwik_translations']['General']['Locale'];
        $locale_variant = str_replace('UTF-8', 'UTF8', $locale);
        setlocale(LC_ALL, $locale, $locale_variant);
        setlocale(LC_CTYPE, '');
    }
}