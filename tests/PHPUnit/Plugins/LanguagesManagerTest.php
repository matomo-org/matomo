<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Common;
use Piwik\Plugins\LanguagesManager\API;
use Piwik\Translate\Writer;
use Piwik\PluginsManager;

require_once 'LanguagesManager/API.php';

class Test_LanguagesManager extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';
    }

    function getTestDataForLanguageFiles()
    {
        // we also test that none of the language php files outputs any character on the screen (eg. space before the <?php)
        $languages = API::getInstance()->getAvailableLanguages();

        $plugins = PluginsManager::getInstance()->readPluginsDirectory();

        $pluginsWithTranslation = array();

        foreach ($plugins AS $plugin) {

            if (API::getInstance()->getPluginTranslationsForLanguage($plugin, 'en')) {

                $pluginsWithTranslation[] = $plugin;
            }
        }

        $return = array();
        foreach ($languages AS $language) {
            if ($language != 'en') {
                $return[] = array($language, null);

                foreach ($pluginsWithTranslation AS $plugin) {

                    $return[] = array($language, $plugin);
                }
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
    function testGetTranslationsForLanguages($language, $plugin)
    {
        $translationWriter = new Writer($language, $plugin);

        if ($translationWriter->hasErrors()) {

            $translationWriter->saveTemporary();
            $this->fail(implode("\n", $translationWriter->getErrors()) . "\n" . 'Translation file errors detected in ' . $language . "...\n");
        }
    }

    /**
     * test language when it's not defined
     *
     * @group Plugins
     * @group LanguagesManager
     * @expectedException Exception
     */
    function testWriterInvalidPlugin()
    {
        $writer = new Writer('de', 'iNvaLiDPluGin'); // invalid plugin throws exception
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
