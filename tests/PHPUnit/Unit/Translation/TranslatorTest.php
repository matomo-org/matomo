<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Translation\Loader\JsonFileLoader;
use Piwik\Translation\Translator;

/**
 * @group Translation
 */
class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    public function test_translate_shouldReturnTranslationId_ifNoTranslationFound()
    {
        $loader = $this->createLoader();
        $translator = new Translator($loader, array());

        $this->assertEquals('General_foo', $translator->translate('General_foo'));
    }

    public function test_translate_shouldReturnTranslation()
    {
        $loader = $this->createLoader(array(
            'General' => array(
                'foo' => 'Hello world',
            ),
        ));
        $translator = new Translator($loader, array());

        $this->assertEquals('Hello world', $translator->translate('General_foo'));
    }

    public function test_translate_shouldReplacePlaceholders()
    {
        $loader = $this->createLoader(array(
            'General' => array(
                'foo' => 'Hello %s',
            ),
        ));
        $translator = new Translator($loader, array());

        $this->assertEquals('Hello John', $translator->translate('General_foo', 'John'));
    }

    public function test_translate_withADifferentLanguage()
    {
        $translator = new Translator(new JsonFileLoader(), array(__DIR__ . '/Loader/fixtures/dir1'));

        $this->assertEquals('Hello', $translator->translate('General_test1'));

        $translator->setCurrentLanguage('fr');
        $this->assertEquals('fr', $translator->getCurrentLanguage());
        $this->assertEquals('Bonjour', $translator->translate('General_test1'));
    }

    public function test_translate_shouldFallback_ifTranslationNotFound()
    {
        $translator = new Translator(new JsonFileLoader(), array(__DIR__ . '/Loader/fixtures/dir1'));
        $translator->setCurrentLanguage('fr');
        $this->assertEquals('Hello', $translator->translate('General_test2'));
    }

    public function test_addDirectory_shouldImportNewTranslations()
    {
        $translator = new Translator(new JsonFileLoader(), array(__DIR__ . '/Loader/fixtures/dir1'));
        // translation not found
        $this->assertEquals('General_test3', $translator->translate('General_test3'));

        $translator->addDirectory(__DIR__ . '/Loader/fixtures/dir2');
        // translation is now found
        $this->assertEquals('Hello 3', $translator->translate('General_test3'));
    }

    public function test_addDirectory_shouldImportOverExistingTranslations()
    {
        $translator = new Translator(new JsonFileLoader(), array(__DIR__ . '/Loader/fixtures/dir1'));
        $this->assertEquals('Hello', $translator->translate('General_test2'));

        $translator->addDirectory(__DIR__ . '/Loader/fixtures/dir2');
        $this->assertEquals('Hello 2', $translator->translate('General_test2'));
    }

    private function createLoader(array $translations = array())
    {
        $loader = $this->getMockForAbstractClass('Piwik\Translation\Loader\LoaderInterface');
        $loader->expects($this->any())
            ->method('load')
            ->willReturn($translations);

        return $loader;
    }
}
