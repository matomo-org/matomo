<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Test\Integration;

use Piwik\Cache;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\LanguageDataProvider;
use Piwik\Plugins\LanguagesManager\API;
use \Exception;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\ByParameterCount;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\EmptyTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\EncodedEntities;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\UnnecassaryWhitespaces;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\CoreTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\NoScripts;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Writer;
use Piwik\Translate;

/**
 * @group LanguagesManager
 */
class LanguagesManagerTest extends \PHPUnit_Framework_TestCase
{
    function getTestDataForLanguageFiles()
    {
        // we also test that none of the language php files outputs any character on the screen (eg. space before the <?php)
        $languages = API::getInstance()->getAvailableLanguages();

        $plugins = \Piwik\Plugin\Manager::getInstance()->readPluginsDirectory();

        $pluginsWithTranslation = array();

        foreach ($plugins as $plugin) {

            if (API::getInstance()->getPluginTranslationsForLanguage($plugin, 'en')) {

                $pluginsWithTranslation[] = $plugin;
            }
        }

        $return = array();
        foreach ($languages as $language) {
            if ($language != 'en') {
                $return[] = array($language, null);

                foreach ($pluginsWithTranslation as $plugin) {

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
     *
     * @dataProvider getTestDataForLanguageFiles
     */
    function testGetTranslationsForLanguages($language, $plugin)
    {
        $translationWriter = new Writer($language, $plugin);

        $baseTranslations = $translationWriter->getTranslations('en');

        $translationWriter->addValidator(new NoScripts());
        if (empty($plugin)) {
            $translationWriter->addValidator(new CoreTranslations($baseTranslations));
        }

        // prevent build from failing when translations string have been deleted
//        $translationWriter->addFilter(new ByBaseTranslations($baseTranslations));
        $translationWriter->addFilter(new EmptyTranslations());
        $translationWriter->addFilter(new ByParameterCount($baseTranslations));
        $translationWriter->addFilter(new UnnecassaryWhitespaces($baseTranslations));
        $translationWriter->addFilter(new EncodedEntities());

        $translations = $translationWriter->getTranslations($language);

        if (empty($translations)) {
            return; // skip language / plugin combinations that aren't present
        }

        $translationWriter->setTranslations($translations);

        $this->assertTrue($translationWriter->isValid(), $translationWriter->getValidationMessage());

        if ($translationWriter->wasFiltered()) {

            $translationWriter->saveTemporary();
            $this->markTestSkipped(implode("\n", $translationWriter->getFilterMessages()) . "\n"
                . 'Translation file errors detected in ' . $language . "...\n"
                . "To synchronise the language files with the english strings, you can manually edit the language files or run the following command may work if you have access to Transifex: \n"
                . "$ ./console translations:update [--plugin=XYZ] \n"
            );
        }
    }

    /**
     * test language when it's not defined
     *
     * @group Plugins
     *
     * @expectedException Exception
     */
    function testWriterInvalidPlugin()
    {
        new Writer('de', 'iNvaLiDPluGin'); // invalid plugin throws exception
    }

    /**
     * test language when it's not defined
     *
     * @group Plugins
     */
    function testGetTranslationsForLanguagesNot()
    {
        $this->assertFalse(API::getInstance()->getTranslationsForLanguage("../no-language"));
    }

    /**
     * check all english translations do not contain more than one
     *
     * @group Plugins
     * @group numbered
     */
    function testTranslationsUseNumberedPlaceholders()
    {
        Cache::flushAll();
        $translator = StaticContainer::get('Piwik\Translation\Translator');
        $translator->reset();
        Translate::loadAllTranslations();
        $translations = $translator->getAllTranslations();
        foreach ($translations AS $plugin => $pluginTranslations) {
            foreach ($pluginTranslations as $key => $pluginTranslation) {
                $this->assertLessThanOrEqual(1, substr_count($pluginTranslation, '%s'),
                    sprintf('%s.%s must use numbered placeholders instead of multiple %%s', $plugin, $key));
            }
        }
    }

    /**
     * test English short name for language
     *
     * @group Plugins
     */
    function testGetLanguageNamesInEnglish()
    {
        $languages = API::getInstance()->getAvailableLanguages();

        /** @var LanguageDataProvider $dataProvider */
        $dataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\LanguageDataProvider');
        $languagesReference = $dataProvider->getLanguageList();

        foreach ($languages as $language) {
            $data = file_get_contents(PIWIK_INCLUDE_PATH . "/plugins/Intl/lang/$language.json");
            $translations = json_decode($data, true);
            $name = $translations['Intl']['EnglishLanguageName'];

            if ($language != 'en') {
                $this->assertFalse($name == 'English', "for $language");
            }

            $languageCode = substr($language, 0, 2);
            $this->assertTrue(isset($languagesReference[$languageCode]));
            $names = $languagesReference[$languageCode];

            if (isset($languagesReference[$language])) {
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
     */
    public function testGetLanguagesList()
    {
        /** @var LanguageDataProvider $languageDataProvider */
        $languageDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\LanguageDataProvider');

        $languages = $languageDataProvider->getLanguageList();
        $this->assertTrue(count($languages) > 0);
        foreach ($languages as $langCode => $langs) {
            $this->assertTrue(strlen($langCode) == 2, "$langCode length = 2");
            $this->assertTrue(is_array($langs) && count($langs) >= 1, "$langCode array(names) >= 1");
        }
    }
}
