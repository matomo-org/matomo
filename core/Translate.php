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

/**
 * @package Piwik
 */
class Piwik_Translate
{
    static private $instance = null;
    static private $languageToLoad = null;
    private $loadedLanguage = false;

    /**
     * @return Piwik_Translate
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function loadEnglishTranslation()
    {
        $this->loadTranslation('en');
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
        Piwik_PluginsManager::getInstance()->loadPluginTranslations($language);
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
        $this->loadTranslation($language);
    }

    private function loadTranslation($language)
    {
        $path = PIWIK_INCLUDE_PATH . '/lang/' . $language . '.php';
        if (!Piwik_Common::isValidFilename($language) || !is_readable($path)) {
            throw new Exception(Piwik_TranslateException('General_ExceptionLanguageFileNotFound', array($language)));
        }
        require $path;
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
        $GLOBALS['Piwik_translations'] = array_merge($GLOBALS['Piwik_translations'], array_filter($translation, 'strlen'));
    }

    /**
     * @return string the language filename prefix, eg 'en' for english
     * @throws exception if the language set is not a valid filename
     */
    public function getLanguageToLoad()
    {
        if (is_null(self::$languageToLoad)) {
            $lang = Piwik_Common::getRequestVar('language', '', 'string');

            Piwik_PostEvent('Translate.getLanguageToLoad', $lang);

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
        return Piwik_Config::getInstance()->General['default_language'];
    }

    /**
     * Generate javascript translations array
     *
     * @param array $moduleList
     * @return string containing javascript code with translations array (including <script> tag)
     */
    public function getJavascriptTranslations(array $moduleList)
    {
        if (!in_array('General', $moduleList)) {
            $moduleList[] = 'General';
        }

        $js = 'var translations = {';

        $moduleRegex = '#^(';
        foreach ($moduleList as $module) {
            $moduleRegex .= $module . '|';
        }
        $moduleRegex = substr($moduleRegex, 0, -1);
        $moduleRegex .= ')_.*_js$#i';

        // Hack: common translations used in JS but not only, force as them to be defined in JS
        $translations = $GLOBALS['Piwik_translations'];
        $toSetInJs = array('General_Save', 'General_OrCancel');
        foreach ($toSetInJs as $toSetId) {
            $translations[$toSetId . '_js'] = $translations[$toSetId];
        }
        foreach ($translations as $key => $value) {
            if (preg_match($moduleRegex, $key)) {
                $js .= '"' . $key . '": "' . str_replace('"', '\"', $value) . '",';
            }
        }
        $js = substr($js, 0, -1);
        $js .= '};';
        $js .= "\n" . 'if(typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }' .
            'for(var i in translations) { piwik_translations[i] = translations[i];} ';
        $js .= 'function _pk_translate(translationStringId) { ' .
            'if( typeof(piwik_translations[translationStringId]) != \'undefined\' ){  return piwik_translations[translationStringId]; }' .
            'return "The string "+translationStringId+" was not loaded in javascript. Make sure it is suffixed with _js and that you called  %7BloadJavascriptTranslations plugins=\'\$YOUR_PLUGIN_NAME\'%7D before your javascript code.";}';
        return $js;
    }

    /**
     * Set locale
     *
     * @see http://php.net/setlocale
     */
    private function setLocale()
    {
        $locale = $GLOBALS['Piwik_translations']['General_Locale'];
        $locale_variant = str_replace('UTF-8', 'UTF8', $locale);
        setlocale(LC_ALL, $locale, $locale_variant);
        setlocale(LC_CTYPE, '');
    }
}

/**
 * Returns translated string or given message if translation is not found.
 *
 * @param string $string Translation string index
 * @param array $args sprintf arguments
 * @return string
 */
function Piwik_Translate($string, $args = array())
{
    if (!is_array($args)) {
        $args = array($args);
    }
    if (isset($GLOBALS['Piwik_translations'][$string])) {
        $string = $GLOBALS['Piwik_translations'][$string];
    }
    if (count($args) == 0) {
        return $string;
    }
    return vsprintf($string, $args);
}

/**
 * Returns translated string or given message if translation is not found.
 * This function does not throw any exception. Use it to translate exceptions.
 *
 * @param string $message Translation string index
 * @param array $args sprintf arguments
 * @return string
 */
function Piwik_TranslateException($message, $args = array())
{
    try {
        return Piwik_Translate($message, $args);
    } catch (Exception $e) {
        return $message;
    }
}
