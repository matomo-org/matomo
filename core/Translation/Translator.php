<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Translation;

use Exception;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Development;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\Manager;
use Piwik\SettingsServer;

/**
 * Translates messages.
 *
 * @api
 */
class Translator
{
    /**
     * Contains the translated messages.
     *
     * @var array
     */
    private $translations = array();

    /**
     * @var string|null
     */
    private $languageToLoad;

    /**
     * @var bool
     */
    private $loadedLanguage = false;

    /**
     * Returns an internationalized string using a translation ID. If a translation
     * cannot be found for the ID, the ID is returned.
     *
     * @param string $translationId Translation ID, eg, `'General_Date'`.
     * @param array|string|int $args `sprintf` arguments to be applied to the internationalized
     *                               string.
     * @return string The translated string or `$translationId`.
     * @api
     */
    public function translate($translationId, $args = array())
    {
        if (!is_array($args)) {
            $args = array($args);
        }

        if (strpos($translationId, "_") !== false) {
            list($plugin, $key) = explode("_", $translationId, 2);
            if (isset($this->translations[$plugin]) && isset($this->translations[$plugin][$key])) {
                $translationId = $this->translations[$plugin][$key];
            }
        }
        if (count($args) == 0) {
            return $translationId;
        }
        return vsprintf($translationId, $args);
    }

    /**
     * Clean a string that may contain HTML special chars, single/double quotes, HTML entities, leading/trailing whitespace
     *
     * @param string $s
     * @return string
     */
    public function clean($s)
    {
        return html_entity_decode(trim($s), ENT_QUOTES, 'UTF-8');
    }

    public function loadEnglishTranslation()
    {
        $this->loadCoreTranslationFile('en');
    }

    public function unloadEnglishTranslation()
    {
        $this->translations = array();
    }

    public function reloadLanguage($language = false)
    {
        if (empty($language)) {
            $language = $this->getLanguageToLoad();
        }
        $this->unloadEnglishTranslation();
        $this->loadEnglishTranslation();
        $this->loadCoreTranslation($language);
        $this->loadPluginsTranslations($language);
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
        if (empty($language)) {
            return;
        }

        $path = PIWIK_INCLUDE_PATH . '/lang/' . $language . '.json';
        if (!Filesystem::isValidFilename($language) || !is_readable($path)) {
            throw new Exception(Piwik::translate('General_ExceptionLanguageFileNotFound', array($language)));
        }
        $data = file_get_contents($path);
        $translations = json_decode($data, true);
        $this->mergeTranslationArray($translations);
        $this->setLocale();
        $this->loadedLanguage = $language;
    }

    public function mergeTranslationArray($translation)
    {
        if (empty($translation)) {
            return;
        }
        // we could check that no string overlap here
        $this->translations = array_replace_recursive($this->translations, $translation);
    }

    /**
     * @return string the language filename prefix, eg 'en' for english
     * @throws exception if the language set is not a valid filename
     */
    public function getLanguageToLoad()
    {
        if (is_null($this->languageToLoad)) {
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

            $this->languageToLoad = $lang;
        }

        return $this->languageToLoad;
    }

    /**
     * Reset the cached language to load. Used in tests.
     */
    public function reset()
    {
        $this->languageToLoad = null;
    }

    private function isALanguageLoaded()
    {
        return !empty($this->translations);
    }

    /**
     * Either the name of the currently loaded language such as 'en' or 'de' or null if no language is loaded at all.
     * @return bool|string
     */
    public function getLanguageLoaded()
    {
        if (!$this->isALanguageLoaded()) {
            return null;
        }

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
        $translations = & $this->translations;

        $clientSideTranslations = array();
        foreach ($this->getClientSideTranslationKeys() as $key) {
            list($plugin, $stringName) = explode("_", $key, 2);
            $clientSideTranslations[$key] = $translations[$plugin][$stringName];
        }

        $js = 'var translations = ' . json_encode($clientSideTranslations) . ';';
        $js .= "\n" . 'if (typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }' .
            'for(var i in translations) { piwik_translations[i] = translations[i];} ';
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
    private function setLocale()
    {
        $locale = $this->translations['General']['Locale'];
        $locale_variant = str_replace('UTF-8', 'UTF8', $locale);
        setlocale(LC_ALL, $locale, $locale_variant);
        setlocale(LC_CTYPE, '');
    }

    /**
     * @param string $translation
     * @return null|string
     */
    public function findTranslationKeyForTranslation($translation)
    {
        if (empty($this->translations)) {
            return null;
        }

        foreach ($this->translations as $key => $translations) {
            $possibleKey = array_search($translation, $translations);
            if (!empty($possibleKey)) {
                return $key . '_' . $possibleKey;
            }
        }

        return null;
    }

    /**
     * Load translations for loaded plugins
     *
     * @param bool|string $language Optional language code
     */
    public function loadPluginsTranslations($language = false)
    {
        if (empty($language)) {
            $language = $this->getLanguageToLoad();
        }

        $pluginManager = Manager::getInstance();

        $cacheKey = 'PluginTranslations';

        if (!empty($language)) {
            $cacheKey .= '-' . trim($language);
        }

        if (!empty($this->loadedPlugins)) {
            // makes sure to create a translation in case loaded plugins change (ie Tests vs Tracker vs UI etc)
            $cacheKey .= '-' . md5(implode('', $pluginManager->getLoadedPluginsName()));
        }

        $cache = Cache::getLazyCache();
        $translations = $cache->fetch($cacheKey);

        if (!empty($translations) &&
            is_array($translations) &&
            !Development::isEnabled()) { // TODO remove this one here once we have environments in DI

            $this->mergeTranslationArray($translations);
            return;
        }

        $translations = array();
        $pluginNames  = Manager::getAllPluginsNames();

        foreach ($pluginNames as $pluginName) {
            if ($pluginManager->isPluginLoaded($pluginName) ||
                $pluginManager->isPluginBundledWithCore($pluginName)) {

                $this->loadPluginTranslations($pluginName, $language);

                if (isset($this->translations[$pluginName])) {
                    $translations[$pluginName] = $this->translations[$pluginName];
                }
            }
        }

        $cache->save($cacheKey, $translations, 43200); // ttl=12hours
    }
    /**
     * Load translation
     *
     * @param Plugin|string $plugin
     * @param string $language
     * @throws \Exception
     * @return bool whether the translation was found and loaded
     */
    public function loadPluginTranslations($plugin, $language)
    {
        // we are in Tracker mode if Loader is not (yet) loaded
        if (SettingsServer::isTrackerApiRequest()) {
            return false;
        }

        $pluginName = ($plugin instanceof Plugin) ? $plugin->getPluginName() : $plugin;

        $path = Manager::getPluginsDirectory() . $pluginName . '/lang/%s.json';

        $defaultLangPath = sprintf($path, $language);
        $defaultEnglishLangPath = sprintf($path, 'en');

        $translationsLoaded = false;

        // merge in english translations as default first
        if (file_exists($defaultEnglishLangPath)) {
            $translations = $this->getTranslationsFromFile($defaultEnglishLangPath);
            $translationsLoaded = true;
            if (isset($translations[$pluginName])) {
                // only merge translations of plugin - prevents overwritten strings
                $this->mergeTranslationArray(array($pluginName => $translations[$pluginName]));
            }
        }

        // merge in specific language translations (to overwrite english defaults)
        if (!empty($language) &&
            $defaultEnglishLangPath != $defaultLangPath &&
            file_exists($defaultLangPath)) {

            $translations = $this->getTranslationsFromFile($defaultLangPath);
            $translationsLoaded = true;
            if (isset($translations[$pluginName])) {
                // only merge translations of plugin - prevents overwritten strings
                $this->mergeTranslationArray(array($pluginName => $translations[$pluginName]));
            }
        }

        return $translationsLoaded;
    }

    /**
     * @param string $pathToTranslationFile
     * @throws \Exception
     * @return mixed
     */
    private function getTranslationsFromFile($pathToTranslationFile)
    {
        $data         = file_get_contents($pathToTranslationFile);
        $translations = json_decode($data, true);

        if (is_null($translations) && Common::hasJsonErrorOccurred()) {
            $jsonError = Common::getLastJsonError();

            $message = sprintf('Not able to load translation file %s: %s', $pathToTranslationFile, $jsonError);

            throw new \Exception($message);
        }

        return $translations;
    }

    /**
     * Returns all the translation messages loaded.
     *
     * @return array
     */
    public function getAllTranslations()
    {
        return $this->translations;
    }
}
