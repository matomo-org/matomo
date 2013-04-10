<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_LanguagesManager
 *
 */

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
 * @package Piwik_LanguagesManager
 */
class Piwik_LanguagesManager_API
{
    static private $instance = null;

    /**
     * @return Piwik_LanguagesManager_API
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

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
            && Piwik_Common::isValidFilename($languageCode)
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
        $languages = _glob($path . "*.php");
        $pathLength = strlen($path);
        $languageNames = array();
        if ($languages) {
            foreach ($languages as $language) {
                $languageNames[] = substr($language, $pathLength, -strlen('.php'));
            }
        }
        $this->languageNames = $languageNames;
        return $languageNames;
    }

    /**
     * Return information on translations (code, language, % translated, etc)
     *
     * @return array Array of arrays
     */
    public function getAvailableLanguagesInfo()
    {
        require PIWIK_INCLUDE_PATH . '/lang/en.php';
        $englishTranslation = $translations;
        $filenames = $this->getAvailableLanguages();
        $languagesInfo = array();
        foreach ($filenames as $filename) {
            require PIWIK_INCLUDE_PATH . "/lang/$filename.php";
            $translationStringsDone = array_intersect_key($englishTranslation, array_filter($translations, 'strlen'));
            $percentageComplete = count($translationStringsDone) / count($englishTranslation);
            $percentageComplete = round(100 * $percentageComplete, 0);
            $languageInfo = array('code'                => $filename,
                                  'name'                => $translations['General_OriginalLanguageName'],
                                  'english_name'        => $translations['General_EnglishLanguageName'],
                                  'translators'         => $translations['General_TranslatorName'],
                                  'translators_email'   => $translations['General_TranslatorEmail'],
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
        if (!is_null($this->availableLanguageNames)) {
            return $this->availableLanguageNames;
        }

        $filenames = $this->getAvailableLanguages();
        $languagesInfo = array();
        foreach ($filenames as $filename) {
            require PIWIK_INCLUDE_PATH . "/lang/$filename.php";
            $languagesInfo[] = array(
                'code'         => $filename,
                'name'         => $translations['General_OriginalLanguageName'],
                'english_name' => $translations['General_EnglishLanguageName']
            );
        }
        $this->availableLanguageNames = $languagesInfo;
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
        require PIWIK_INCLUDE_PATH . "/lang/$languageCode.php";
        $languageInfo = array();
        foreach ($translations as $key => $value) {
            $languageInfo[] = array('label' => $key, 'value' => $value);
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
        Piwik::checkUserIsSuperUserOrTheUser($login);
        Piwik::checkUserIsNotAnonymous();
        return Piwik_FetchOne('SELECT language FROM ' . Piwik_Common::prefixTable('user_language') .
            ' WHERE login = ? ', array($login));
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
        Piwik::checkUserIsSuperUserOrTheUser($login);
        Piwik::checkUserIsNotAnonymous();
        if (!$this->isLanguageAvailable($languageCode)) {
            return false;
        }
        $paramsBind = array($login, $languageCode, $languageCode);
        Piwik_Query('INSERT INTO ' . Piwik_Common::prefixTable('user_language') .
                ' (login, language)
                    VALUES (?,?)
                ON DUPLICATE KEY UPDATE language=?',
            $paramsBind);
    }
}
