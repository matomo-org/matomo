<?php
use Piwik\Common;
use Piwik\Translate\Writer;
use Piwik\Translate\Validate\CoreTranslations;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class WriterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';
        include PIWIK_INCLUDE_PATH . '/core/DataFiles/Countries.php';
    }

    /**
     * @group Core
     * @group Translate
     * @dataProvider getValidConstructorData
     */
    public function testConstructorValid($language, $plugin)
    {
        $translationWriter = new Writer($language, $plugin);
        $this->assertTrue($translationWriter->hasTranslations());
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
     * @group Translate
     * @expectedException Exception
     */
    public function testConstructorInvalid()
    {
        $translationWriter = new Writer('en', 'InValIdPlUGin');
    }

    /**
     * @group Core
     * @group Translate
     * @ expectedException Exception
     * @dataProvider getExceptionalTranslations
     */
    public function testSetTranslationsThrowsException($translations, $error)
    {
        $writer = new Writer('de');
        try {
            $writer->setTranslations($translations);
            $this->fail('Exception not thrown');
        } catch (Exception $e) {
            $this->assertEquals($error, $e->getMessage());
        }
    }

    public function getExceptionalTranslations()
    {
        $translations = json_decode(file_get_contents(PIWIK_INCLUDE_PATH.'/lang/de.json'), true);
        return array(
            array(array('test' => array('test' => 'test')), CoreTranslations::__ERRORSTATE_MINIMUMTRANSLATIONS__),
            array(array('General' => array('Locale' => '')) + $translations, CoreTranslations::__ERRORSTATE_LOCALEREQUIRED__),
            array(array('General' => array('Locale' => 'de_DE.UTF-8')) + $translations, CoreTranslations::__ERRORSTATE_TRANSLATORINFOREQUIRED__),
            array(array('General' => array('Locale' => 'de_DE.UTF-8',
                                           'TranslatorName' => 'name')) + $translations, CoreTranslations::__ERRORSTATE_TRANSLATOREMAILREQUIRED__),
            array(array('General' => array('Locale' => 'de_DE.UTF-8',
                                           'TranslatorName' => 'name',
                                           'TranslatorEmail' => 'name@domain.com',
                                           'LayoutDirection' => 'fail')) + $translations, CoreTranslations::__ERRORSTATE_LAYOUTDIRECTIONINVALID__),
            array(array('General' => array('Locale' => 'invalid',
                                           'TranslatorName' => 'name',
                                           'TranslatorEmail' => 'name@domain.com')) + $translations, CoreTranslations::__ERRORSTATE_LOCALEINVALID__),
            array(array('General' => array('Locale' => 'xx_DE.UTF-8',
                                           'TranslatorName' => 'name',
                                           'TranslatorEmail' => 'name@domain.com',)) + $translations, CoreTranslations::__ERRORSTATE_LOCALEINVALIDLANGUAGE__),
            array(array('General' => array('Locale' => 'de_XX.UTF-8',
                                           'TranslatorName' => 'name',
                                           'TranslatorEmail' => 'name@domain.com',)) + $translations, CoreTranslations::__ERRORSTATE_LOCALEINVALIDCOUNTRY__),
            array(array('General' => array('Locale' => '<script>')) + $translations, 'script tags restricted for language files'),
        );
    }

    /**
     * @group Core
     * @group Translate
     * @group Translate_Write
     */
    public function testSaveTranslation()
    {
        $translations = json_decode(file_get_contents(PIWIK_INCLUDE_PATH.'/lang/en.json'), true);

        $translationsToWrite = array();
        $translationsToWrite['General'] = $translations['General'];
        $translationsToWrite['UserLanguage'] = $translations['UserLanguage'];
        $translationsToWrite['UserCountry'] = $translations['UserCountry'];

        $translationsToWrite['General']['Yes'] = 'string with %1$s';
        $translationsToWrite['Plugin'] = array(
            'Body' => "Message\nBody"
        );

        $translationWriter = new Writer('fr');
        $translationWriter->setTranslations($translationsToWrite);

        $rc = $translationWriter->saveTemporary();
        $this->assertGreaterThan(50000, $rc);

        $this->assertCount(4, $translationWriter->getErrors());
    }

    /**
     * @group Core
     * @group Translate
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
     * @group Translate
     * @dataProvider getTranslationPathTemporaryTestData
     */
    public function testGetTemporaryTranslationPath($language, $plugin, $path)
    {
        $writer = new Writer($language, $plugin);
        $this->assertEquals($path, $writer->getTemporaryTranslationPath());
    }

    public function getTranslationPathTemporaryTestData()
    {
        return array(
            array('de', null, PIWIK_INCLUDE_PATH . '/tmp/de.json'),
            array('te', null, PIWIK_INCLUDE_PATH . '/tmp/te.json'),
            array('de', 'CoreHome', PIWIK_INCLUDE_PATH . '/tmp/plugins/CoreHome/lang/de.json'),
            array('pt-br', 'Actions', PIWIK_INCLUDE_PATH . '/tmp/plugins/Actions/lang/pt-br.json'),
        );
    }

    /**
     * @group Core
     * @group Translate
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
     * @group Translate
     * @expectedException Exception
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
