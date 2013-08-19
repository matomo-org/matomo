<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Common;
use Piwik\Plugins\LanguagesManager\API;
use Piwik\TranslationWriter;

require_once 'LanguagesManager/API.php';

class Test_LanguagesManager extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';
    }

    static $englishStringsIndexed = array();
    static $englishStringsWithParameters = array();
    static $allLanguages = array();
    static $allCountries = array();
    static $expectedLanguageKeys = array();

    function getTestDataForLanguageFiles()
    {
        self::$allLanguages = Common::getLanguagesList();
        self::$allCountries = Common::getCountriesList();
        self::$englishStringsWithParameters = array();
        self::$englishStringsIndexed = array();
        self::$expectedLanguageKeys = array();
        $englishStrings = API::getInstance()->getTranslationsForLanguage('en');
        foreach ($englishStrings as $englishString) {
            $stringLabel = $englishString['label'];
            $stringValue = $englishString['value'];
            $count = $this->getCountParametersToReplace($stringValue);
            if (array_sum($count) > 0) {
                self::$englishStringsWithParameters[$stringLabel] = $count;
            }
            self::$englishStringsIndexed[$stringLabel] = $stringValue;
            self::$expectedLanguageKeys[] = $stringLabel;
        }

        // we also test that none of the language php files outputs any character on the screen (eg. space before the <?php)
        $languages = API::getInstance()->getAvailableLanguages();

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
        /** Indicates wether the translation files needs to be changed */
        $writeCleanedFile = false;
        $errorsInCurrentFile = array();

        $strings = API::getInstance()->getTranslationsForLanguage($language);

        // check if any translation contains restricted script tags
        $serializedStrings = serialize($strings);
        $invalids = array("<script", 'document.', 'javascript:', 'src=', 'BACKGROUND=', 'onload=');
        foreach ($invalids as $invalid) {
            $this->assertTrue(stripos($serializedStrings, $invalid) === false, "$language: language file containing javascript");
        }

        // check for at least 250 translation in file
        $this->assertTrue(count($strings) > 250, "$language: expecting at least 250 translations in the language file");

        $cleanedStrings = array();
        foreach ($strings as $string) {
            $stringLabel = $string['label'];
            $stringValue = $string['value'];

            // translations that are empty or don't exist in english translations should be removed
            if (empty($stringValue) || trim($stringValue) === '' || !in_array($stringLabel, self::$expectedLanguageKeys)) {

                $writeCleanedFile = true;

            } else {
                // checking that translated strings have the same number of %s as the english source strings
                if (isset(self::$englishStringsWithParameters[$stringLabel])) {
                    $englishParametersCount = self::$englishStringsWithParameters[$stringLabel];
                    $countTranslation = $this->getCountParametersToReplace($stringValue);
                    if ($englishParametersCount != $countTranslation) {
                        $writeCleanedFile = true;
                        $errorsInCurrentFile[] = "$language: The string $stringLabel has ".json_encode($englishParametersCount)." parameters in English, but ".json_encode($countTranslation)." in this translation.";
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
            if (isset(self::$englishStringsIndexed[$stringLabel])
                && self::$englishStringsIndexed[$stringLabel] == $stringValue
                // Do not however remove the General_ since there are definiely legit translations that are same as in english (eg. short days)
                && strpos($stringLabel, 'General_') === false
                && strpos($stringLabel, 'CoreHome_') === false
                && strpos($stringLabel, 'UserCountry_') === false
                && strpos($stringLabel, 'UserLanguage_') === false
                && $language != 'de'
            ) {
                $writeCleanedFile = true;
                $errorsInCurrentFile[] = "$language: The string $stringLabel is the same as in English, removing...";
                unset($cleanedStrings[$stringLabel]);
            }

            // remove excessive line breaks (and leading/trailing whitespace) from translations
            if (!empty($cleanedStrings[$stringLabel])) {
                $stringNoLineBreak = trim($cleanedStrings[$stringLabel]);
                $stringNoLineBreak = str_replace("\r", "", $stringNoLineBreak); # remove useless carrige renturns
                $stringNoLineBreak = preg_replace('/([\n]{2,})/', "\n\n", $stringNoLineBreak); # remove excessive line breaks
                if (!isset(self::$englishStringsIndexed[$stringLabel]) || !substr_count(self::$englishStringsIndexed[$stringLabel], "\n")) {
                    $stringNoLineBreak = str_replace("\n", " ", $stringNoLineBreak); # remove all line breaks if english string doesn't contain any
                }
                if ($cleanedStrings[$stringLabel] !== $stringNoLineBreak) {
                    $errorsInCurrentFile[] = "$language: found unnecessary whitespace in some strings in $stringLabel";
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
                $decoded = TranslationWriter::clean($currentString);
                if ($currentString != $decoded) {
                    $errorsInCurrentFile[] = "$language: found encoded entities in $stringLabel, converting entities to characters";
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
            $errorsInCurrentFile[] = "$language: General_LayoutDirection must be rtl or ltr";
        }
        if ($writeCleanedFile) {
            $path = TranslationWriter::getTranslationPath($language, 'tmp');

            // Reorder cleaned up translations as the same order as en.php
            uksort($cleanedStrings, array($this, 'sortTranslationsKeys'));

            $nested = array();
            foreach ($cleanedStrings as $key => $value) {
                list($plugin, $nkey) = explode("_", $key, 2);
                $nested[$plugin][$nkey] = $value;
            }
            $cleanedStrings = $nested;

            TranslationWriter::saveTranslation($cleanedStrings, $path);
            $output[] = (implode("\n", $errorsInCurrentFile) . "\n" . 'Translation file errors detected in ' . $language . '...
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
        $sprintfParameters = array('%s', '%1$s', '%2$s', '%3$s', '%4$s', '%5$s', '%6$s', '%7$s', '%8$s', '%9$s');
        $count = array();
        foreach ($sprintfParameters as $parameter) {

            $placeholderCount = substr_count($string, $parameter);
            if ($placeholderCount > 0) {

                $count[$parameter] = $placeholderCount;
            }
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
        $this->assertFalse(API::getInstance()->getTranslationsForLanguage("../no-language"));
    }

    /**
     * test English short name for language
     *
     * @group Plugins
     * @group LanguagesManager
     */
    function testGetLanguageNamesInEnglish()
    {
        $languages = API::getInstance()->getAvailableLanguages();
        foreach ($languages as $language) {
            $data = file_get_contents(PIWIK_INCLUDE_PATH . "/lang/$language.json");
            $translations = json_decode($data, true);
            $name = $translations['General']['EnglishLanguageName'];

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
        $languages = Common::getLanguagesList();
        $this->assertTrue(count($languages) > 0);
        foreach ($languages as $langCode => $langs) {
            $this->assertTrue(strlen($langCode) == 2, "$langCode length = 2");
            $this->assertTrue(is_array($langs) && count($langs) >= 1, "$langCode array(names) >= 1");
        }
    }
}
