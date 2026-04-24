<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 * @phpstan-type AvailableLanguage array{code: string, name: string, english_name: string}
 * @phpstan-type AvailableLanguageInfo array{
 *     code: string,
 *     name: string,
 *     english_name: string,
 *     translators: string,
 *     percentage_complete: string
 * }
 * @phpstan-type TranslationEntry array{label: string, value: string}
 * @phpstan-type Translations array<string, array<string, string>>
 *
 * @method static \Piwik\Plugins\LanguagesManager\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var array<int, list<AvailableLanguage>>
     */
    protected $availableLanguageNames = [];

    /**
     * @var array<int, list<string>>
     */
    protected $languageNames = [];

    /**
     * Returns whether a language code can be used in the current Matomo instance.
     *
     * @param string $languageCode The ISO language code to validate.
     * @param bool $_ignoreConfig Whether to ignore the configured language allowlist.
     * @return bool True if the language is available, `false` otherwise.
     */
    public function isLanguageAvailable(string $languageCode, bool $_ignoreConfig = false)
    {
        return $languageCode !== ''
        && Filesystem::isValidFilename($languageCode)
        && in_array($languageCode, $this->getAvailableLanguages($_ignoreConfig));
    }

    /**
     * Returns the available Matomo language codes.
     *
     * @param bool $_ignoreConfig Whether to ignore the configured language allowlist.
     * @return list<string> Available ISO language codes.
     */
    public function getAvailableLanguages(bool $_ignoreConfig = false)
    {
        $cacheKey = (int)$_ignoreConfig;

        if (!empty($this->languageNames[$cacheKey])) {
            return $this->languageNames[$cacheKey];
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

        $this->languageNames[$cacheKey] = $languages;
        return $languages;
    }

    /**
     * Returns translation coverage information for each available language.
     *
     * @param bool $excludeNonCorePlugins Whether to exclude non-core plugins from the translation percentage calculation.
     * @param bool $_ignoreConfig Whether to ignore the configured language allowlist.
     * @return list<AvailableLanguageInfo> Translation metadata for each available language, including code, names,
     *                                     translators, and completion percentage.
     */
    public function getAvailableLanguagesInfo(bool $excludeNonCorePlugins = true, bool $_ignoreConfig = false)
    {
        $data = file_get_contents(PIWIK_INCLUDE_PATH . '/lang/en.json');
        /** @var Translations $englishTranslation */
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
                /** @var Translations $pluginTranslations */
                $pluginTranslations = json_decode($data, true);
                $englishTranslation = array_merge_recursive($englishTranslation, $pluginTranslations);
            }
        }

        $filenames = $this->getAvailableLanguages($_ignoreConfig);
        $languagesInfo = array();
        foreach ($filenames as $filename) {
            $data = file_get_contents(sprintf('%s/lang/%s.json', PIWIK_INCLUDE_PATH, $filename));
            /** @var Translations $translations */
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
                    /** @var Translations $pluginTranslations */
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
     * Returns the available languages with their localized and English names.
     *
     * @param bool $_ignoreConfig Whether to ignore the configured language allowlist.
     * @return list<AvailableLanguage> Available languages with `code`, `name`, and `english_name` fields.
     */
    public function getAvailableLanguageNames(bool $_ignoreConfig = false)
    {
        $this->loadAvailableLanguages($_ignoreConfig);
        return $this->availableLanguageNames[(int)$_ignoreConfig];
    }

    /**
     * Returns translation strings for a specific language across core and loaded plugins.
     *
     * @param string $languageCode The ISO language code to load.
     * @return list<TranslationEntry>|false Translation entries with `label` and `value` keys, or `false` if the
     *                                      language is unavailable.
     */
    public function getTranslationsForLanguage(string $languageCode)
    {
        if (!$this->isLanguageAvailable($languageCode)) {
            return false;
        }
        $data         = file_get_contents(PIWIK_INCLUDE_PATH . "/lang/$languageCode.json");
        /** @var Translations $translations */
        $translations = json_decode($data, true);
        $languageInfo = [];
        foreach ($translations as $module => $keys) {
            foreach ($keys as $key => $value) {
                $languageInfo[] = array(
                    'label' => sprintf('%s_%s', $module, $key),
                    'value' => $value,
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
     * @return list<TranslationEntry>|false
     * @ignore
     */
    public function getPluginTranslationsForLanguage(string $pluginName, string $languageCode)
    {
        if (!$this->isLanguageAvailable($languageCode)) {
            return false;
        }

        $languageFile = Manager::getPluginDirectory($pluginName) . "/lang/$languageCode.json";

        if (!file_exists($languageFile)) {
            return false;
        }

        $data = file_get_contents($languageFile);
        /** @var Translations $translations */
        $translations = json_decode($data, true);
        $languageInfo = [];
        foreach ($translations as $module => $keys) {
            foreach ($keys as $key => $value) {
                $languageInfo[] = [
                    'label' => sprintf("%s_%s", $module, $key),
                    'value' => $value,
                ];
            }
        }
        return $languageInfo;
    }

    /**
     * Returns the saved language preference for a user.
     *
     * @param string $login The user login to read the language for.
     * @return string|false The saved language code, or `false` for the anonymous user.
     */
    public function getLanguageForUser(string $login)
    {
        if (strtolower($login) === 'anonymous') {
            return false;
        }

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $lang = $this->getModel()->getLanguageForUser($login);

        return $lang;
    }

    private function getModel(): Model
    {
        return new Model();
    }

    /**
     * Stores the language preference for a user.
     *
     * @param string $login The user login to update.
     * @param string $languageCode The ISO language code to store.
     * @return bool `true` if the language was stored, `false` if the language code is unavailable.
     */
    public function setLanguageForUser(string $login, string $languageCode): bool
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
     * Returns whether a user prefers 12-hour time formatting.
     *
     * @param string $login The user login to query.
     * @return bool `true` if the user uses a 12-hour clock, `false` otherwise or for the anonymous user.
     */
    public function uses12HourClockForUser(string $login): bool
    {
        if (strtolower($login) === 'anonymous') {
            return false;
        }

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        return $this->getModel()->uses12HourClock($login);
    }

    /**
     * Stores whether a user prefers 12-hour time formatting.
     *
     * @param string $login The user login to update.
     * @param bool $use12HourClock Whether to enable 12-hour clock formatting.
     * @return bool `true` if the preference was stored, `false` for the anonymous user.
     */
    public function set12HourClockForUser(string $login, bool $use12HourClock): bool
    {
        if (strtolower($login) === 'anonymous') {
            return false;
        }

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        return $this->getModel()->set12HourClock($login, $use12HourClock);
    }

    private function loadAvailableLanguages(bool $_ignoreConfig = false): void
    {
        $cacheKey                = (int)$_ignoreConfig;

        if (!empty($this->availableLanguageNames[$cacheKey])) {
            return;
        }

        $cacheId = 'availableLanguages' . (int) $_ignoreConfig;
        $cache = PiwikCache::getEagerCache();

        if ($cache->contains($cacheId)) {
            /** @var list<AvailableLanguage> $languagesInfo */
            $languagesInfo = $cache->fetch($cacheId);
        } else {
            $languages = $this->getAvailableLanguages($_ignoreConfig);
            $languagesInfo       = [];
            foreach ($languages as $languageCode) {
                $data = @file_get_contents(PIWIK_INCLUDE_PATH . "/plugins/Intl/lang/$languageCode.json");

                // Skip languages not having Intl translations
                if (empty($data)) {
                    continue;
                }

                /** @var Translations $translations */
                $translations = json_decode($data, true);
                $languagesInfo[] = [
                    'code'         => $languageCode,
                    'name'         => (string)$translations['Intl']['OriginalLanguageName'],
                    'english_name' => (string)$translations['Intl']['EnglishLanguageName'],
                ];
            }

            $cache->save($cacheId, $languagesInfo);
        }

        $this->availableLanguageNames[$cacheKey] = $languagesInfo;
    }

    /**
     * @param list<string> $languages
     */
    private function enableDevelopmentLanguageInDevEnvironment(array &$languages): void
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
