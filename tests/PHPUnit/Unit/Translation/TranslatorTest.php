<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation;

use Piwik\Translation\Loader\JsonFileLoader;
use Piwik\Translation\Translator;

/**
 * @group Translation
 */
class TranslatorTest extends \PHPUnit\Framework\TestCase
{
    public function testTranslateShouldReturnTranslationIdIfNoTranslationFound()
    {
        $loader = $this->createLoader();
        $translator = new Translator($loader, array());

        $this->assertEquals('General_foo', $translator->translate('General_foo'));
    }

    public function testTranslateShouldReturnTranslation()
    {
        $loader = $this->createLoader(array(
            'General' => array(
                'foo' => 'Hello world',
            ),
        ));
        $translator = new Translator($loader, array());

        $this->assertEquals('Hello world', $translator->translate('General_foo'));
    }

    public function testTranslateShouldReplacePlaceholders()
    {
        $loader = $this->createLoader(array(
            'General' => array(
                'foo' => 'Hello %s',
            ),
        ));
        $translator = new Translator($loader, array());

        $this->assertEquals('Hello John', $translator->translate('General_foo', 'John'));
    }

    public function testTranslateWithADifferentLanguage()
    {
        $translator = new Translator(new JsonFileLoader(), array(__DIR__ . '/Loader/fixtures/dir1'));

        $this->assertEquals('Hello', $translator->translate('General_test1'));

        $translator->setCurrentLanguage('fr');
        $this->assertEquals('fr', $translator->getCurrentLanguage());
        $this->assertEquals('Bonjour', $translator->translate('General_test1'));
    }

    public function testTranslateShouldFallbackIfTranslationNotFound()
    {
        $translator = new Translator(new JsonFileLoader(), array(__DIR__ . '/Loader/fixtures/dir1'));
        $translator->setCurrentLanguage('fr');
        $this->assertEquals('Hello', $translator->translate('General_test2'));
    }

    public function testAddDirectoryShouldImportNewTranslations()
    {
        $translator = new Translator(new JsonFileLoader(), array(__DIR__ . '/Loader/fixtures/dir1'));
        // translation not found
        $this->assertEquals('General_test3', $translator->translate('General_test3'));

        $translator->addDirectory(__DIR__ . '/Loader/fixtures/dir2');
        // translation is now found
        $this->assertEquals('Hello 3', $translator->translate('General_test3'));
    }

    public function testAddDirectoryShouldImportOverExistingTranslations()
    {
        $translator = new Translator(new JsonFileLoader(), array(__DIR__ . '/Loader/fixtures/dir1'));
        $this->assertEquals('Hello', $translator->translate('General_test2'));

        $translator->addDirectory(__DIR__ . '/Loader/fixtures/dir2');
        $this->assertEquals('Hello 2', $translator->translate('General_test2'));
    }

    /**
     * @dataProvider getAndListingData
     */
    public function testCreateAndListing(array $items, string $language, string $expectedResult)
    {
        $translator = new Translator(new JsonFileLoader(), array(PIWIK_INCLUDE_PATH . '/plugins/Intl/lang'));

        self::assertEquals($expectedResult, $translator->createAndListing($items, $language));
    }

    public function getAndListingData(): array
    {
        return [
            [[], 'en', ''],
            [['1'], 'en', '1'],
            [['1', '2'], 'en', '1 and 2'],
            [['1', '2'], 'cs', '1 a 2'],
            [['1', '2'], 'de', '1 und 2'],
            // note: we currently use (american) english, so a comma before the and is correct
            // british english doesn't have that
            [['1', '2', '3'], 'en', '1, 2, and 3'],
            [['1', '2', '3', '4'], 'en', '1, 2, 3, and 4'],
            [['1', '2', '3'], 'cs', '1, 2 a 3'],
            [['1', '2', '3'], 'de', '1, 2 und 3'],
            [['1', '2', '3', '4'], 'de', '1, 2, 3 und 4'],
            [['1', '2', '3', '4'], 'am', '1፣ 2፣ 3 እና 4'],
        ];
    }

    /**
     * @dataProvider getOrListingData
     */
    public function testCreateOrListing(array $items, string $language, string $expectedResult)
    {
        $translator = new Translator(new JsonFileLoader(), array(PIWIK_INCLUDE_PATH . '/plugins/Intl/lang'));

        self::assertEquals($expectedResult, $translator->createOrListing($items, $language));
    }

    public function getOrListingData(): array
    {
        return [
            [[], 'en', ''],
            [['1'], 'en', '1'],
            [['1', '2'], 'en', '1 or 2'],
            [['1', '2'], 'cs', '1 nebo 2'],
            [['1', '2'], 'de', '1 oder 2'],
            // note: we currently use (american) english, so a comma before the or is correct
            // british english doesn't have that
            [['1', '2', '3'], 'en', '1, 2, or 3'],
            [['1', '2', '3', '4'], 'en', '1, 2, 3, or 4'],
            [['1', '2', '3'], 'cs', '1, 2 nebo 3'],
            [['1', '2', '3'], 'de', '1, 2 oder 3'],
            [['1', '2', '3', '4'], 'de', '1, 2, 3 oder 4'],
            [['1', '2', '3', '4'], 'am', '1፣ 2፣ 3 ወይም 4'],
        ];
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
