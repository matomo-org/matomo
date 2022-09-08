<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */
namespace Piwik\Plugins\LanguagesManager;

use Piwik\Cache as PiwikCache;
use Piwik\Config;
use Piwik\Development;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Translation\Loader\DevelopmentLoader;

/**
 * The LanguagesManager API lets you access existing Matomo translations, and change Users languages preferences.
 *
 * "getTranslationsForLanguage" will return all translation strings for a given language,
 * so you can leverage Matomo translations in your application (and automatically benefit from the <a href='https://matomo.org/translations/' rel='noreferrer' target='_blank'>40+ translations</a>!).
 * This is mostly useful to developers who integrate Matomo API results in their own application.
 *
 * You can also request the default language to load for a user via "getLanguageForUser",
 * or update it via "setLanguageForUser".
 *
 * @method static \Piwik\Plugins\LanguagesManager\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    protected $availableLanguageNames = [];
    protected $languageNames = [];

    /**
     * Returns true if specified language is available
     *
     * @param string $languageCode
     * @param bool $_ignoreConfig
     * @return bool true if language available; false otherwise
     */
    public function isLanguageAvailable($languageCode, $_ignoreConfig = false)
    {
        return $languageCode !== false
        && Filesystem::isValidFilename($languageCode)
        && in_array($languageCode, $this->getAvailableLanguages($_ignoreConfig));
    }

    /**
     * Return array of available languages
     *
     * @param bool $_ignoreConfig
     * @return array Array of strings, each containing its ISO language code
     */
    public function getAvailableLanguages($_ignoreConfig = false)
    {
        if (!empty($this->languageNames[$_ignoreConfig])) {
            return $this->languageNames[$_ignoreConfig];
        }
        $path = PIWIK_INCLUDE_PATH . "/lang/";
        $languagesPath = _glob($path . "*.json");

        $pathLength = strlen($path);
        $filesystemLanguages = array();
        if ($languagesPath) {
            foreach ($languagesPath as $language) {
                $filesystemLanguages[] = substr($language, $pathLength, -strlen('.json'));
            }
        }

        $configLanguages = Config::getInstance()->Languages["Languages"];

        if ($_ignoreConfig) {
            $languages = $filesystemLanguages;
        } else {
            $languages = array_intersect($filesystemLanguages, $configLanguages);
        }

        $this->enableDevelopmentLanguageInDevEnvironment($languages);

        /**
         * Hook called after loading available language files.
         *
         * Use this hook to customise the list of languagesPath available in Matomo.
         *
         * @param array
         */
        Piwik::postEvent('LanguagesManager.getAvailableLanguages', array(&$languages));

        $this->languageNames[$_ignoreConfig] = $languages;
        return $languages;
    }

    /**
     * Return information on translations (code, language, % translated, etc)
     *
     * @param bool $excludeNonCorePlugins excludes non core plugin from percentage calculation
     * @param bool $_ignoreConfig
     *
     * @return array Array of arrays
     */
    public function getAvailableLanguagesInfo($excludeNonCorePlugins=true, $_ignoreConfig = false)
    {
        $data = file_get_contents(PIWIK_INCLUDE_PATH . '/lang/en.json');
        $englishTranslation = json_decode($data, true);

        $pluginDirectories = Manager::getPluginsDirectories();
        // merge with plugin translations if any

        $pluginFiles = array();
        foreach ($pluginDirectories as $pluginsDir) {
            $pluginFiles = array_merge($pluginFiles, glob(sprintf('%s*/lang/en.json', $pluginsDir)));
        }

        foreach ($pluginFiles as $file) {
            $fileWithoutPluginDir = str_replace($pluginDirectories, '', $file);

            preg_match('/([^\/]+)\/lang/i', $fileWithoutPluginDir, $matches);
            $plugin = $matches[1];

            if (!$excludeNonCorePlugins || Manager::getInstance()->isPluginBundledWithCore($plugin)) {
                $data = file_get_contents($file);
                $pluginTranslations = json_decode($data, true);
                $englishTranslation = array_merge_recursive($englishTranslation, $pluginTranslations);
            }
        }

        $filenames = $this->getAvailableLanguages($_ignoreConfig);
        $languagesInfo = array();
        foreach ($filenames as $filename) {
            $data = file_get_contents(sprintf('%s/lang/%s.json', PIWIK_INCLUDE_PATH, $filename));
            $translations = json_decode($data, true);

            // merge with plugin translations if any
            $pluginFiles = array();
            foreach ($pluginDirectories as $pluginsDir) {
                $pluginFiles = array_merge($pluginFiles, glob(sprintf('%s*/lang/%s.json', $pluginsDir, $filename)));
            }

            foreach ($pluginFiles as $file) {
                $fileWithoutPluginDir = str_replace($pluginDirectories, '', $file);

                preg_match('/([^\/]+)\/lang/i', $fileWithoutPluginDir, $matches);
                $plugin = $matches[1];

                if (!$excludeNonCorePlugins || Manager::getInstance()->isPluginBundledWithCore($plugin)) {
                    $data = file_get_contents($file);
                    $pluginTranslations = json_decode($data, true);
                    $translations = array_merge_recursive($translations, $pluginTranslations);
                }
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

            // Skip languages not having Intl translations
            if (empty($translations['Intl'])) {
                continue;
            }

            $translationStringsDone = $intersect($englishTranslation, $translations);
            $percentageComplete = count($translationStringsDone, COUNT_RECURSIVE) / count($englishTranslation, COUNT_RECURSIVE);
            $percentageComplete = round(100 * $percentageComplete, 0);
            $languageInfo = array('code'                => $filename,
                                  'name'                => $translations['Intl']['OriginalLanguageName'],
                                  'english_name'        => $translations['Intl']['EnglishLanguageName'],
                                  'translators'         => $translations['General']['TranslatorName'] ?? '-',
                                  'percentage_complete' => $percentageComplete . '%',
            );
            $languagesInfo[] = $languageInfo;
        }
        return $languagesInfo;
    }

    /**
     * Return array of available languages
     *
     * @param bool $_ignoreConfig
     * @return array Array of array, each containing its ISO language code and name of the language
     */
    public function getAvailableLanguageNames($_ignoreConfig = false)
    {
        $this->loadAvailableLanguages($_ignoreConfig);
        return $this->availableLanguageNames[$_ignoreConfig];
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

        $languageFile = Manager::getPluginDirectory($pluginName) . "/lang/$languageCode.json";

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

    /**
     * Returns whether the user uses 12 hour clock
     *
     * @param string $login
     * @return string
     */
    public function uses12HourClockForUser($login)
    {
        if ($login == 'anonymous') {
            return false;
        }

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $lang = $this->getModel()->uses12HourClock($login);

        return $lang;
    }

    /**
     * Returns whether the user uses 12 hour clock
     *
     * @param string $login
     * @param bool $use12HourClock
     * @return string
     */
    public function set12HourClockForUser($login, $use12HourClock)
    {
        if ($login == 'anonymous') {
            return false;
        }

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $lang = $this->getModel()->set12HourClock($login, $use12HourClock);

        return $lang;
    }

    private function loadAvailableLanguages($_ignoreConfig = false)
    {
        if (!empty($this->availableLanguageNames[$_ignoreConfig])) {
            return;
        }

        $cacheId = 'availableLanguages' . (int) $_ignoreConfig;
        $cache = PiwikCache::getEagerCache();

        if ($cache->contains($cacheId)) {
            $languagesInfo = $cache->fetch($cacheId);
        } else {
            $languages = $this->getAvailableLanguages($_ignoreConfig);
            $languagesInfo = array();
            foreach ($languages as $languageCode) {
                $data = @file_get_contents(PIWIK_INCLUDE_PATH . "/plugins/Intl/lang/$languageCode.json");

                // Skip languages not having Intl translations
                if (empty($data)) {
                    continue;
                }

                $translations = json_decode($data, true);
                $languagesInfo[] = array(
                    'code'         => $languageCode,
                    'name'         => $translations['Intl']['OriginalLanguageName'],
                    'english_name' => $translations['Intl']['EnglishLanguageName']
                );
            }

            $cache->save($cacheId, $languagesInfo);
        }

        $this->availableLanguageNames[$_ignoreConfig] = $languagesInfo;
    }

    private function enableDevelopmentLanguageInDevEnvironment(&$languages)
    {
        $key = array_search(DevelopmentLoader::LANGUAGE_ID, $languages);
        if (!Development::isEnabled() && $key) {
            unset($languages[$key]);
        }
        if (Development::isEnabled() && !$key) {
            $languages[] = DevelopmentLoader::LANGUAGE_ID;
        }
    }
}
