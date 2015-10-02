<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\Test\Unit\TranslationWriter;

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
class WriterTest extends \PHPUnit_Framework_TestCase
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
        return array(
            array('en', ''),
            array('de', ''),
            array('en', 'ExamplePlugin'),
        );
    }

    /**
     * @group Core
     *
     * @expectedException \Exception
     */
    public function testConstructorInvalid()
    {
        new Writer('en', 'InValIdPlUGin');
    }

    /**
     * @group Core
     */
    public function testHasTranslations()
    {
        $writer = new Writer('de');
        $writer->setTranslations(array('General' => array('test' => 'test')));
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
        $writer->setTranslations(array());
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
        $translations = json_decode(file_get_contents(PIWIK_INCLUDE_PATH.'/lang/de.json'), true);
        return array(
            array(array('General' => array('Locale' => '')) + $translations, CoreTranslations::ERRORSTATE_LOCALEREQUIRED),
            array(array('General' => array('Locale' => 'de_DE.UTF-8')) + $translations, CoreTranslations::ERRORSTATE_TRANSLATORINFOREQUIRED),
            array(array('General' => array('Locale' => 'invalid',
                                           'TranslatorName' => 'name')) + $translations, CoreTranslations::ERRORSTATE_LOCALEINVALID),
            array(array('General' => array('Locale' => 'xx_DE.UTF-8',
                                           'TranslatorName' => 'name')) + $translations, CoreTranslations::ERRORSTATE_LOCALEINVALIDLANGUAGE),
            array(array('General' => array('Locale' => 'de_XX.UTF-8',
                                           'TranslatorName' => 'name')) + $translations, CoreTranslations::ERRORSTATE_LOCALEINVALIDCOUNTRY),
            array(array('General' => array('Locale' => '<script>')) + $translations, 'script tags restricted for language files'),
        );
    }

    /**
     * @group Core
     *
     * @expectedException \Exception
     */
    public function testSaveException()
    {
        $writer = new Writer('it');
        $writer->save();
    }

    /**
     * @group Core
     *
     * @expectedException \Exception
     */
    public function testSaveTemporaryException()
    {
        $writer = new Writer('it');
        $writer->saveTemporary();
    }

    /**
     * @group Core
     */
    public function testSaveTranslation()
    {
        $translations = json_decode(file_get_contents(PIWIK_INCLUDE_PATH.'/lang/en.json'), true);

        $translationsToWrite = array();
        $translationsToWrite['General'] = $translations['General'];
        $translationsToWrite['Mobile'] = $translations['Mobile'];

        $translationsToWrite['General']['Yes'] = 'string with %1$s';
        $translationsToWrite['Plugin'] = array(
            'Body' => "Message\nBody"
        );

        $translationWriter = new Writer('fr');

        $translationWriter->addFilter(new UnnecassaryWhitespaces($translations));
        $translationWriter->addFilter(new ByBaseTranslations($translations));
        $translationWriter->addFilter(new ByParameterCount($translations));

        $translationWriter->setTranslations($translationsToWrite);

        $rc = $translationWriter->saveTemporary();

        @unlink(PIWIK_INCLUDE_PATH.'/tmp/fr.json');

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
        return array(
            array('de', null, PIWIK_INCLUDE_PATH . '/lang/de.json'),
            array('te', null, PIWIK_INCLUDE_PATH . '/lang/te.json'),
            array('de', 'CoreHome', PIWIK_INCLUDE_PATH . '/plugins/CoreHome/lang/de.json'),
            array('pt-br', 'Actions', PIWIK_INCLUDE_PATH . '/plugins/Actions/lang/pt-br.json'),
        );
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

        return array(
            array('de', null, $tmpPath . '/de.json'),
            array('te', null, $tmpPath . '/te.json'),
            array('de', 'CoreHome', $tmpPath . '/plugins/CoreHome/lang/de.json'),
            array('pt-br', 'Actions', $tmpPath . '/plugins/Actions/lang/pt-br.json'),
        );
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
        return array(
            array('de'),
            array('te'),
            array('pt-br'),
            array('tzm'),
            array('abc'),
            array('de-de'),
            array('DE'),
            array('DE-DE'),
            array('DE-de'),
        );
    }
    /**
     * @group Core
     *
     * @expectedException \Exception
     * @dataProvider getInvalidLanguages
     */
    public function testSetLanguageInvalid($language)
    {
        $writer = new Writer('en', null);
        $writer->setLanguage($language);
    }

    public function getInvalidLanguages()
    {
        return array(
            array(''),
            array('abcd'),
            array('pt-brfr'),
            array('00'),
            array('a-b'),
            array('x3'),
            array('X4-fd'),
            array('12-34'),
            array('$§'),
        );
    }
}
