<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */
namespace Piwik\Plugins\LanguagesManager;

use Piwik\Cache\PersistentCache;
use Piwik\Db;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;

/**
 * The LanguagesManager API lets you access existing Piwik translations, and change Users languages preferences.
 *
 * "getTranslationsForLanguage" will return all translation strings for a given language,
 * so you can leverage Piwik translations in your application (and automatically benefit from the <a href='http://piwik.org/translations/' target='_blank'>40+ translations</a>!).
 * This is mostly useful to developers who integrate Piwik API results in their own application.
 *
 * You can also request the default language to load for a user via "getLanguageForUser",
 * or update it via "setLanguageForUser".
 *
 * @method static \Piwik\Plugins\LanguagesManager\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected $availableLanguageNames = null;
    protected $languageNames = null;

    /**
     * Returns true if specified language is available
     *
     * @param string $languageCode
     * @return bool true if language available; false otherwise
     */
    public function isLanguageAvailable($languageCode)
    {
        return $languageCode !== false
        && Filesystem::isValidFilename($languageCode)
        && in_array($languageCode, $this->getAvailableLanguages());
    }

    /**
     * Return array of available languages
     *
     * @return array Arry of strings, each containing its ISO language code
     */
    public function getAvailableLanguages()
    {
        if (!is_null($this->languageNames)) {
            return $this->languageNames;
        }
        $path = PIWIK_INCLUDE_PATH . "/lang/";
        $languagesPath = _glob($path . "*.json");

        $pathLength = strlen($path);
        $languages = array();
        if ($languagesPath) {
            foreach ($languagesPath as $language) {
                $languages[] = substr($language, $pathLength, -strlen('.json'));
            }
        }

        /**
         * Hook called after loading available language files.
         *
         * Use this hook to customise the list of languagesPath available in Piwik.
         *
         * @param array
         */
        Piwik::postEvent('LanguageManager.getAvailableLanguages', array(&$languages));

        $this->languageNames = $languages;
        return $languages;
    }

    /**
     * Return information on translations (code, language, % translated, etc)
     *
     * @return array Array of arrays
     */
    public function getAvailableLanguagesInfo()
    {
        $data = file_get_contents(PIWIK_INCLUDE_PATH . '/lang/en.json');
        $englishTranslation = json_decode($data, true);

        // merge with plugin translations if any
        $pluginFiles = glob(sprintf('%s/plugins/*/lang/en.json', PIWIK_INCLUDE_PATH));
        foreach ($pluginFiles as $file) {

            $data = file_get_contents($file);
            $pluginTranslations = json_decode($data, true);
            $englishTranslation = array_merge_recursive($englishTranslation, $pluginTranslations);
        }

        $filenames = $this->getAvailableLanguages();
        $languagesInfo = array();
        foreach ($filenames as $filename) {
            $data = file_get_contents(sprintf('%s/lang/%s.json', PIWIK_INCLUDE_PATH, $filename));
            $translations = json_decode($data, true);

            // merge with plugin translations if any
            $pluginFiles = glob(sprintf('%s/plugins/*/lang/%s.json', PIWIK_INCLUDE_PATH, $filename));
            foreach ($pluginFiles as $file) {

                $data = file_get_contents($file);
                $pluginTranslations = json_decode($data, true);
                $translations = array_merge_recursive($translations, $pluginTranslations);
            }

            $intersect = function ($array, $array2) {
                $res = $array;
                foreach ($array as $module => $keys) {
                    if (!isset($array2[$module])) {
                        unset($res[$module]);
                    } else {
                        $res[$module] = array_intersect_key($res[$module], array_filter($array2[$module], 'strlen'));
                    }
                }
                return $res;
            };
            $translationStringsDone = $intersect($englishTranslation, $translations);
            $percentageComplete = count($translationStringsDone, COUNT_RECURSIVE) / count($englishTranslation, COUNT_RECURSIVE);
            $percentageComplete = round(100 * $percentageComplete, 0);
            $languageInfo = array('code'                => $filename,
                                  'name'                => $translations['General']['OriginalLanguageName'],
                                  'english_name'        => $translations['General']['EnglishLanguageName'],
                                  'translators'         => $translations['General']['TranslatorName'],
                                  'translators_email'   => $translations['General']['TranslatorEmail'],
                                  'percentage_complete' => $percentageComplete . '%',
            );
            $languagesInfo[] = $languageInfo;
        }
        return $languagesInfo;
    }

    /**
     * Return array of available languages
     *
     * @return array Arry of array, each containing its ISO language code and name of the language
     */
    public function getAvailableLanguageNames()
    {
        $this->loadAvailableLanguages();
        return $this->availableLanguageNames;
    }

    /**
     * Returns translation strings by language
     *
     * @param string $languageCode ISO language code
     * @return array|false Array of arrays, each containing 'label' (translation index)  and 'value' (translated string); false if language unavailable
     */
    public function getTranslationsForLanguage($languageCode)
    {
        if (!$this->isLanguageAvailable($languageCode)) {
            return false;
        }
        $data = file_get_contents(PIWIK_INCLUDE_PATH . "/lang/$languageCode.json");
        $translations = json_decode($data, true);
        $languageInfo = array();
        foreach ($translations as $module => $keys) {
            foreach ($keys as $key => $value) {
                $languageInfo[] = array(
                    'label' => sprintf("%s_%s", $module, $key),
                    'value' => $value
                );
            }
        }

        foreach (PluginManager::getInstance()->getLoadedPluginsName() as $pluginName) {
            $translations = $this->getPluginTranslationsForLanguage($pluginName, $languageCode);

            if (!empty($translations)) {
                foreach ($translations as $keys) {
                    $languageInfo[] = $keys;
                }
            }
        }

        return $languageInfo;
    }

    /**
     * Returns translation strings by language for given plugin
     *
     * @param string $pluginName name of plugin
     * @param string $languageCode ISO language code
     * @return array|false Array of arrays, each containing 'label' (translation index)  and 'value' (translated string); false if language unavailable
     *
     * @ignore
     */
    public function getPluginTranslationsForLanguage($pluginName, $languageCode)
    {
        if (!$this->isLanguageAvailable($languageCode)) {
            return false;
        }

        $languageFile = PIWIK_INCLUDE_PATH . "/plugins/$pluginName/lang/$languageCode.json";

        if (!file_exists($languageFile)) {
            return false;
        }

        $data = file_get_contents($languageFile);
        $translations = json_decode($data, true);
        $languageInfo = array();
        foreach ($translations as $module => $keys) {
            foreach ($keys as $key => $value) {
                $languageInfo[] = array(
                    'label' => sprintf("%s_%s", $module, $key),
                    'value' => $value
                );
            }
        }
        return $languageInfo;
    }

    /**
     * Returns the language for the user
     *
     * @param string $login
     * @return string
     */
    public function getLanguageForUser($login)
    {
        if ($login == 'anonymous') {
            return false;
        }

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $lang = $this->getModel()->getLanguageForUser($login);

        return $lang;
    }

    private function getModel()
    {
        return new Model();
    }

    /**
     * Sets the language for the user
     *
     * @param string $login
     * @param string $languageCode
     * @return bool
     */
    public function setLanguageForUser($login, $languageCode)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);
        Piwik::checkUserIsNotAnonymous();

        if (!$this->isLanguageAvailable($languageCode)) {
            return false;
        }

        $this->getModel()->setLanguageForUser($login, $languageCode);

        return true;
    }

    private function loadAvailableLanguages()
    {
        if (!is_null($this->availableLanguageNames)) {
            return;
        }

        $cache = new PersistentCache('availableLanguages');

        if ($cache->has()) {
            $languagesInfo = $cache->get();
        } else {
            $filenames = $this->getAvailableLanguages();
            $languagesInfo = array();
            foreach ($filenames as $filename) {
                $data = file_get_contents(PIWIK_INCLUDE_PATH . "/lang/$filename.json");
                $translations = json_decode($data, true);
                $languagesInfo[] = array(
                    'code'         => $filename,
                    'name'         => $translations['General']['OriginalLanguageName'],
                    'english_name' => $translations['General']['EnglishLanguageName']
                );
            }

            $cache->set($languagesInfo);
        }

        $this->availableLanguageNames = $languagesInfo;
    }
}
