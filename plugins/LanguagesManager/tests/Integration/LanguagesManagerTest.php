<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\tests\Integration;

use Piwik\Cache;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\LanguageDataProvider;
use Piwik\Plugins\LanguagesManager\API;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\ByParameterCount;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\EmptyTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\EncodedEntities;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\UnnecassaryWhitespaces;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\CoreTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\NoScripts;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Writer;
use Piwik\Tests\Framework\Fixture;

/**
 * @group LanguagesManager
 */
class LanguagesManagerTest extends \PHPUnit\Framework\TestCase
{
    function getTestDataForLanguageFiles()
    {
        // we also test that none of the language php files outputs any character on the screen (eg. space before the <?php)
        $languages = API::getInstance()->getAvailableLanguages();

        $plugins = \Piwik\Plugin\Manager::getInstance()->readPluginsDirectory();

        $pluginsWithTranslation = array();

        foreach ($plugins as $plugin) {

            if ('Intl' !== $plugin && API::getInstance()->getPluginTranslationsForLanguage($plugin, 'en')) {

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
        $translationWriter->addFilter(new EncodedEntities($baseTranslations));

        $translations = $translationWriter->getTranslations($language);

        if (empty($translations)) {
            self::assertTrue(true);
            return; // skip language / plugin combinations that aren't present
        }

        $translationWriter->setTranslations($translations);

        $this->assertTrue($translationWriter->isValid(), $translationWriter->getValidationMessage() ?: '');

        if ($translationWriter->wasFiltered()) {

            if (!$translationWriter->hasTranslations()) {
                $this->markTestSkipped('Translation file errors detected in ' . $language . "...\n"
                    . "File would be empty after filtering. You may remove it manually to fix this test.\n"
                );
                return;
            }

            $translationWriter->saveTemporary();
            $this->markTestSkipped(implode("\n", $translationWriter->getFilterMessages()) . "\n"
                . 'Translation file errors detected in ' . $language . "...\n"
                . "To synchronise the language files with the english strings, you can manually edit the language files or run the following command may work if you have access to Weblate: \n"
                . "$ ./console translations:update [--plugin=XYZ] \n"
            );
        }
    }

    /**
     * test language when it's not defined
     *
     * @group Plugins
     */
    function testWriterInvalidPlugin()
    {
        $this->expectException(\Exception::class);

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
        Fixture::loadAllTranslations();
        $translations = $translator->getAllTranslations();
        foreach ($translations AS $plugin => $pluginTranslations) {
            foreach ($pluginTranslations as $key => $pluginTranslation) {
                $this->assertLessThanOrEqual(1, substr_count($pluginTranslation, '%s'),
                    sprintf('%s.%s must use numbered placeholders instead of multiple %%s', $plugin, $key));
            }
        }
    }

    /**
     * check all english translations do not contain unescaped % symbols
     *
     * @group Plugins
     * @group numbered2
     */
    function testTranslationsUseEscapedPercentSigns()
    {
        Cache::flushAll();
        $translator = StaticContainer::get('Piwik\Translation\Translator');
        $translator->reset();
        Fixture::loadAllTranslations();
        $translations = $translator->getAllTranslations();
        foreach ($translations AS $plugin => $pluginTranslations) {
            if ($plugin == 'Intl') {
                continue; // skip generated stuff
            }
            foreach ($pluginTranslations as $key => $pluginTranslation) {
                $pluginTranslation = preg_replace('/(%(?:[1-9]\$)?[a-z])/', '', $pluginTranslation); // remove placeholders
                $pluginTranslation = str_replace('%%', '', $pluginTranslation); // remove already escaped symbols
                $this->assertEquals(0, substr_count($pluginTranslation, '%'),
                    sprintf('%s.%s must use escaped %% symbols', $plugin, $key));
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
