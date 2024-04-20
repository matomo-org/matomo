<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\tests\Unit\TranslationWriter;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\ByBaseTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\ByParameterCount;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Filter\UnnecassaryWhitespaces;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\CoreTranslations;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Validate\NoScripts;
use Piwik\Plugins\LanguagesManager\TranslationWriter\Writer;

/**
 * @group LanguagesManager
 */
class WriterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group Core
     *
     * @dataProvider getValidConstructorData
     */
    public function testConstructorValid($language, $plugin)
    {
        $translationWriter = new Writer($language, $plugin);
        $this->assertEquals($language, $translationWriter->getLanguage());
        $this->assertFalse($translationWriter->hasTranslations());
    }

    public function getValidConstructorData()
    {
        return [
            ['en', ''],
            ['de', ''],
            ['en', 'ExamplePlugin'],
        ];
    }

    /**
     * @group Core
     */
    public function testConstructorInvalid()
    {
        $this->expectException(\Exception::class);

        new Writer('en', 'InValIdPlUGin');
    }

    /**
     * @group Core
     */
    public function testHasTranslations()
    {
        $writer = new Writer('de');
        $writer->setTranslations(['General' => ['test' => 'test']]);
        $this->assertTrue($writer->hasTranslations());
    }

    /**
     * @group Core
     */
    public function testHasNoTranslations()
    {
        $writer = new Writer('de');
        $this->assertFalse($writer->hasTranslations());
    }

    /**
     * @group Core
     */
    public function testSetTranslationsEmpty()
    {
        $writer = new Writer('de');
        $writer->setTranslations([]);
        $this->assertTrue($writer->isValid());
        $this->assertFalse($writer->hasTranslations());
    }

    /**
     * @group Core
     *
     * @dataProvider getInvalidTranslations
     */
    public function testSetTranslationsInvalid($translations, $error)
    {
        $writer = new Writer('de');
        $writer->setTranslations($translations);
        $writer->addValidator(new NoScripts());
        $writer->addValidator(new CoreTranslations());
        $this->assertFalse($writer->isValid());
        $this->assertEquals($error, $writer->getValidationMessage());
    }

    public function getInvalidTranslations()
    {
        $translations = json_decode(file_get_contents(PIWIK_INCLUDE_PATH . '/lang/de.json'), true);
        return [
            [['General' => ['Locale' => '']] + $translations, CoreTranslations::ERRORSTATE_LOCALEREQUIRED],
            [['General' => ['Locale' => 'de_DE.UTF-8']] + $translations, CoreTranslations::ERRORSTATE_TRANSLATORINFOREQUIRED],
            [['General' => [
                     'Locale'         => 'invalid',
                     'TranslatorName' => 'name'
                 ]
                  ] + $translations, CoreTranslations::ERRORSTATE_LOCALEINVALID],
            [['General' => ['Locale' => 'xx_DE.UTF-8',
                                           'TranslatorName' => 'name']] + $translations, CoreTranslations::ERRORSTATE_LOCALEINVALIDLANGUAGE],
            [['General' => ['Locale' => 'de_XX.UTF-8',
                                           'TranslatorName' => 'name']] + $translations, CoreTranslations::ERRORSTATE_LOCALEINVALIDCOUNTRY],
            [['General' => ['Locale' => '<script>']] + $translations, 'script tags restricted for language files'],
        ];
    }

    /**
     * @group Core
     */
    public function testSaveException()
    {
        $this->expectException(\Exception::class);

        $writer = new Writer('it');
        $writer->save();
    }

    /**
     * @group Core
     */
    public function testSaveTemporaryException()
    {
        $this->expectException(\Exception::class);

        $writer = new Writer('it');
        $writer->saveTemporary();
    }

    /**
     * @group Core
     */
    public function testSaveTranslation()
    {
        $translations = json_decode(file_get_contents(PIWIK_INCLUDE_PATH . '/lang/en.json'), true);

        $translationsToWrite = [];
        $translationsToWrite['General'] = $translations['General'];
        $translationsToWrite['Mobile'] = $translations['Mobile'];

        $translationsToWrite['General']['Yes'] = 'string with %1$s';
        $translationsToWrite['Plugin'] = [
            'Body' => "Message\nBody"
        ];

        $translationWriter = new Writer('fr');

        $translationWriter->addFilter(new UnnecassaryWhitespaces($translations));
        $translationWriter->addFilter(new ByBaseTranslations($translations));
        $translationWriter->addFilter(new ByParameterCount($translations));

        $translationWriter->setTranslations($translationsToWrite);

        $rc = $translationWriter->saveTemporary();

        @unlink(PIWIK_INCLUDE_PATH . '/tmp/fr.json');

        $this->assertGreaterThan(25000, $rc);

        $this->assertCount(4, $translationWriter->getFilterMessages());
    }

    /**
     * @group Core
     *
     * @dataProvider getTranslationPathTestData
     */
    public function testGetTranslationsPath($language, $plugin, $path)
    {
        $writer = new Writer($language, $plugin);
        $this->assertEquals($path, $writer->getTranslationPath());
    }

    public function getTranslationPathTestData()
    {
        return [
            ['de', null, PIWIK_INCLUDE_PATH . '/lang/de.json'],
            ['te', null, PIWIK_INCLUDE_PATH . '/lang/te.json'],
            ['de', 'CoreHome', PIWIK_INCLUDE_PATH . '/plugins/CoreHome/lang/de.json'],
            ['pt-br', 'Actions', PIWIK_INCLUDE_PATH . '/plugins/Actions/lang/pt-br.json'],
        ];
    }

    /**
     * @group Core
     *
     * @dataProvider getTranslationPathTemporaryTestData
     */
    public function testGetTemporaryTranslationPath($language, $plugin, $path)
    {
        $writer = new Writer($language, $plugin);
        $this->assertEquals($path, $writer->getTemporaryTranslationPath());
    }

    public function getTranslationPathTemporaryTestData()
    {
        $tmpPath = StaticContainer::get('path.tmp');

        return [
            ['de', null, $tmpPath . '/de.json'],
            ['te', null, $tmpPath . '/te.json'],
            ['de', 'CoreHome', $tmpPath . '/plugins/CoreHome/lang/de.json'],
            ['pt-br', 'Actions', $tmpPath . '/plugins/Actions/lang/pt-br.json'],
        ];
    }

    /**
     * @group Core
     *
     * @dataProvider getValidLanguages
     */
    public function testSetLanguageValid($language)
    {
        $writer = new Writer('en', null);
        $writer->setLanguage($language);
        $this->assertEquals(strtolower($language), $writer->getLanguage());
    }

    public function getValidLanguages()
    {
        return [
            ['de'],
            ['te'],
            ['pt-br'],
            ['tzm'],
            ['abc'],
            ['de-de'],
            ['DE'],
            ['DE-DE'],
            ['DE-de'],
        ];
    }
    /**
     * @group Core
     *
     * @dataProvider getInvalidLanguages
     */
    public function testSetLanguageInvalid($language)
    {
        $this->expectException(\Exception::class);

        $writer = new Writer('en', null);
        $writer->setLanguage($language);
    }

    public function getInvalidLanguages()
    {
        return [
            [''],
            ['abcd'],
            ['pt-brfr'],
            ['00'],
            ['a-b'],
            ['x3'],
            ['X4-fd'],
            ['12-34'],
            ['$§'],
        ];
    }
}
