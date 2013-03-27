<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once 'LanguagesManager/API.php';

class Test_LanguagesManager extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';
    }

    static $errors;
    static $englishStringsIndexed = array();
    static $englishStringsWithParameters = array();
    static $allLanguages = array();
    static $allCountries = array();
    static $expectedLanguageKeys = array();

    function getTestDataForLanguageFiles()
    {
        self::$allLanguages = Piwik_Common::getLanguagesList();
        self::$allCountries = Piwik_Common::getCountriesList();
        self::$englishStringsWithParameters = array();
        self::$englishStringsIndexed = array();
        self::$expectedLanguageKeys = array();
        $englishStrings = Piwik_LanguagesManager_API::getInstance()->getTranslationsForLanguage('en');
        foreach ($englishStrings as $englishString) {
            $stringLabel = $englishString['label'];
            $stringValue = $englishString['value'];
            $count = $this->getCountParametersToReplace($stringValue);
            if ($count > 0) {
                self::$englishStringsWithParameters[$stringLabel] = $count;
            }
            self::$englishStringsIndexed[$stringLabel] = $stringValue;
            self::$expectedLanguageKeys[] = $stringLabel;
        }

        // we also test that none of the language php files outputs any character on the screen (eg. space before the <?php)
        $languages = Piwik_LanguagesManager_API::getInstance()->getAvailableLanguages();

        $return = array();
        foreach ($languages AS $language) {
            if ($language != 'en') {
                $return[] = array($language);
            }
        }
        return $return;
    }

    /**
     * test all languages
     *
     * @group Plugins
     * @group LanguagesManager
     * @dataProvider getTestDataForLanguageFiles
     */
    function testGetTranslationsForLanguages($language)
    {
        self::$errors = array();
        ob_start();
        $writeCleanedFile = false;
        $strings = Piwik_LanguagesManager_API::getInstance()->getTranslationsForLanguage($language);
        $content = ob_get_flush();
        $serializedStrings = serialize($strings);
        $invalids = array("<script", 'document.', 'javascript:', 'src=', 'BACKGROUND=', 'onload=');
        foreach ($invalids as $invalid) {
            $this->assertTrue(stripos($serializedStrings, $invalid) === false, "$language: language file containing javascript");
        }
        $this->assertTrue(count($strings) > 250, "$language: expecting at least 250 translations in the language file");
        $this->assertTrue(strlen($content) == 0, "$language: buffer was " . strlen($content) . " long but should be zero. Translation file for '$language' must be buggy.");

        $cleanedStrings = array();
        foreach ($strings as $string) {
            $stringLabel = $string['label'];
            $stringValue = $string['value'];

            $plugin = substr($stringLabel, 0, strpos($stringLabel, '_'));
            $plugins[$plugin] = true;
            // Testing that the translated string is not empty => '',
            if (empty($stringValue) || trim($stringValue) === '') {
                $writeCleanedFile = true;
                self::$errors[] = "$language: The string $stringLabel is empty in the translation file, removing the line.";
                $cleanedStrings[$stringLabel] = false;
            } elseif (!in_array($stringLabel, self::$expectedLanguageKeys)
                // translation files should not contain 3rd plugin translations, but if they are there, we shall not delete them
                // since translators have spent time working on it... at least for now we shall leave them in (until V2 and plugin repository is done)
                && !in_array($plugin, array('GeoIP', 'Forecast', 'EntryPage', 'UserLanguage'))
            ) {
                $writeCleanedFile = true;
                self::$errors[] = "$language: The string $stringLabel was not found in the English language file, removing the line.";
                $cleanedStrings[$stringLabel] = false;
            } else {
                // checking that translated strings have the same number of %s as the english source strings
                if (isset(self::$englishStringsWithParameters[$stringLabel])) {
                    $englishParametersCount = self::$englishStringsWithParameters[$stringLabel];
                    $countTranslation = $this->getCountParametersToReplace($stringValue);
                    if ($englishParametersCount != $countTranslation) {
                        // Write fixed file in given location
                        // Will trigger a ->fail()
                        $writeCleanedFile = true;
                        self::$errors[] = "$language: The string $stringLabel has $englishParametersCount parameters in English, but $countTranslation in this translation.";
                    } else {
                        $cleanedStrings[$stringLabel] = $stringValue;
                    }
                } // No %s found
                else {
                    $cleanedStrings[$stringLabel] = $stringValue;
                }
            }

            // If the translation is the same as in English, we remove it from the translation file (as it might have been copied by
            // the translator but this would skew translation stats
            if (isset($englishStringsIndexed[$stringLabel])
                && $englishStringsIndexed[$stringLabel] == $stringValue
                // Do not however remove the General_ since there are definiely legit translations that are same as in english (eg. short days)
                && strpos($stringLabel, 'General_') === false
                && strpos($stringLabel, 'CoreHome_') === false
                && strpos($stringLabel, 'UserCountry_') === false
                && $language != 'de'
            ) {
                $writeCleanedFile = true;
                self::$errors[] = "$language: The string $stringLabel is the same as in English, removing...";
                $cleanedStrings[$stringLabel] = false;
            }
            // remove excessive line breaks (and leading/trailing whitespace) from translations
            if (!empty($cleanedStrings[$stringLabel])) {
                $stringNoLineBreak = trim($cleanedStrings[$stringLabel]);
                if ($stringLabel != 'Login_MailPasswordChangeBody') {
                    $stringNoLineBreak = str_replace(array("\n", "\r"), " ", $stringNoLineBreak);
                }
                if ($cleanedStrings[$stringLabel] !== $stringNoLineBreak) {
                    self::$errors[] = "$language: found unnecessary whitespace in some strings in $stringLabel";
                    $writeCleanedFile = true;
                    $cleanedStrings[$stringLabel] = $stringNoLineBreak;
                }
            }
            // Test locale
            if ($stringLabel == 'General_Locale'
                && !empty($cleanedStrings[$stringLabel])
            ) {
                if (!preg_match('/^([a-z]{2})_([A-Z]{2})\.UTF-8$/', $cleanedStrings[$stringLabel], $matches)) {
                    $this->fail("$language: invalid locale in $stringLabel");
                } else if (!array_key_exists($matches[1], self::$allLanguages)) {
                    $this->fail("$language: invalid language code in $stringLabel");
                } else if (!array_key_exists(strtolower($matches[2]), self::$allCountries)) {
                    $this->fail("$language: invalid region (country code) in $stringLabel");
                }
            }
            if (isset($cleanedStrings[$stringLabel])) {
                $currentString = $cleanedStrings[$stringLabel];
                $decoded = Piwik_TranslationWriter::clean($currentString);
                if ($currentString != $decoded) {
                    self::$errors[] = "$language: found encoded entities in $stringLabel, converting entities to characters";
                    $writeCleanedFile = true;
                    $cleanedStrings[$stringLabel] = $decoded;
                }
            }
        }
        $this->assertTrue(!empty($cleanedStrings['General_TranslatorName']), "$language: translator info not specified");
        $this->assertTrue(!empty($cleanedStrings['General_TranslatorEmail']), "$language: translator email not specified");
        if (!empty($cleanedStrings['General_LayoutDirection'])
            && !in_array($cleanedStrings['General_LayoutDirection'], array('rtl', 'ltr'))
        ) {
            $writeCleanedFile = true;
            $cleanedStrings['General_LayoutDirection'] = false;
            self::$errors[] = "$language: General_LayoutDirection must be rtl or ltr";
        }
        if ($writeCleanedFile) {
            $path = Piwik_TranslationWriter::getTranslationPath($language, 'tmp');

            // Reorder cleaned up translations as the same order as en.php
            uksort($cleanedStrings, array($this, 'sortTranslationsKeys'));

            Piwik_TranslationWriter::saveTranslation($cleanedStrings, $path);
            $output[] = (implode("\n", self::$errors) . "\n" . 'Translation file errors detected in ' . $language . '...
                    Wrote cleaned translation file in: ' . $path . ".
                    You can copy the cleaned files to /lang/\n");
        }
        if (!empty($output)) {
            $this->fail(implode(",", $output));
        }
    }

    /**
     * Keep strings in the order of the english file
     *
     * @param $k1
     * @param $k2
     *
     * @return int
     */
    protected function sortTranslationsKeys($k1, $k2)
    {
        static $order = array();
        if (empty($order)) {
            $i = 0;
            foreach (self::$englishStringsIndexed as $key => $value) {
                $order[$key] = $i;
                $i++;
            }
        }
        if (empty($order[$k1])) {
            $order[$k1] = 5000;
        }
        if (empty($order[$k2])) {
            $order[$k2] = 5000;
        }

        return $order[$k1] < $order[$k2] ? -1 : ($order[$k1] == $order[$k2] ? strcmp($k1, $k2) : 1);
    }

    private function getCountParametersToReplace($string)
    {
        $sprintfParameters = array('%s', '%1$s', '%2$s', '%3$s', '%4$s', '%5$s', '%6$s');
        $count = 0;
        foreach ($sprintfParameters as $parameter) {
            $count += substr_count($string, $parameter);
        }
        return $count;
    }

    /**
     * test language when it's not defined
     *
     * @group Plugins
     * @group LanguagesManager
     */
    function testGetTranslationsForLanguagesNot()
    {
        $this->assertFalse(Piwik_LanguagesManager_API::getInstance()->getTranslationsForLanguage("../no-language"));
    }

    /**
     * test English short name for language
     *
     * @group Plugins
     * @group LanguagesManager
     */
    function testGetLanguageNamesInEnglish()
    {
        $languages = Piwik_LanguagesManager_API::getInstance()->getAvailableLanguages();
        foreach ($languages as $language) {
            require PIWIK_INCLUDE_PATH . "/lang/$language.php";
            $name = $translations['General_EnglishLanguageName'];

            if ($language != 'en') {
                $this->assertFalse($name == 'English', "for $language");
            }

            $languageCode = substr($language, 0, 2);
            $this->assertTrue(isset($GLOBALS['Piwik_LanguageList'][$languageCode]));
            $names = $GLOBALS['Piwik_LanguageList'][$languageCode];

            if (isset($GLOBALS['Piwik_LanguageList'][$language])) {
                if (is_array($names)) {
                    $this->assertTrue(in_array($name, $names), "$language: failed because $name not a known language name");
                } else {
                    $this->assertTrue($name == $names, "$language: failed because $name == $names");
                }
            } else {
                if (is_array($names)) {
                    $this->assertTrue(strpos($name, $names[0]) !== false);
                } else {
                    $this->fail("$language: expected an array of language names");
                }
            }
        }
    }

    /**
     * test format of DataFile/Languages.php
     *
     * @group Plugins
     * @group LanguagesManager
     */
    function testGetLanguagesList()
    {
        $languages = Piwik_Common::getLanguagesList();
        $this->assertTrue(count($languages) > 0);
        foreach ($languages as $langCode => $langs) {
            $this->assertTrue(strlen($langCode) == 2, "$langCode length = 2");
            $this->assertTrue(is_array($langs) && count($langs) >= 1, "$langCode array(names) >= 1");
        }
    }
}
