<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\NumberFormatter;
use Piwik\Translation\Translator;

/**
 * @group Core
 * @group NumberFormatter
 */
class NumberFormatterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Translator
     */
    private $translator;

    public function setUp(): void
    {
        \Piwik\Plugin\Manager::getInstance()->loadPluginTranslations();

        $this->translator = StaticContainer::get('Piwik\Translation\Translator');
    }

    public function tearDown(): void
    {
        $this->translator->reset();
    }

    /**
     * @dataProvider getFormatMethodTestData
     */
    public function test_format_CorrectlyFormatsValueAsNumberOrPercent(
        $language, $value, $maximumFractionDigits, $minimumFractionDigits, $expected)
    {
        $this->translator->setCurrentLanguage($language);
        $numberFormatter = new NumberFormatter($this->translator);
        $this->assertEquals($expected, $numberFormatter->format($value, $maximumFractionDigits,$minimumFractionDigits));
    }

    public function getFormatMethodTestData()
    {
        return array(
            // number formatting
            array('en', 5, 0, 0, '5'),
            array('en', -5, 0, 3, '-5'),
            array('en', 5.299, 0, 0, '5'),
            array('en', 5.299, 3, 0, '5.299'),
            array('en', sqrt(33), 2, 0, '5.74'),

            // percent formatting
            array('en', '5.299%', 0, 0, '5%'),
            array('en', '5.299%', 3, 0, '5.299%'),
        );
    }

    /**
     * @dataProvider getNumberFormattingTestData
     */
    public function testNumberFormatting($language, $value, $maximumFractionDigits, $minimumFractionDigits, $expected)
    {
        $this->translator->setCurrentLanguage($language);
        $numberFormatter = new NumberFormatter($this->translator);

        $this->assertSame($expected, $numberFormatter->formatNumber($value, $maximumFractionDigits, $minimumFractionDigits));
    }

    public function getNumberFormattingTestData()
    {
        return array(
            // english formats
            array('en', 5, 0, 0, '5'),
            array('en', -5, 0, 3, '-5'),
            array('en', 5.299, 0, 0, '5'),
            array('en', 5.299, 3, 0, '5.299'),
            array('en', -50, 3, 3, '-50.000'),
            array('en', 5000, 0, 0, '5,000'),
            array('en', 5000000, 0, 0, '5,000,000'),
            array('en', -5000000, 0, 0, '-5,000,000'),

            // foreign languages
            array('ar', 51239.56, 3, 0, '51٬239٫56'),
            array('be', 51239.56, 3, 0, '51 239,56'),
            array('de', 51239.56, 3, 0, '51.239,56'),
            array('bn', 152551239.56, 3, 0, '15,25,51,239.56'),
            array('hi', 152551239.56, 0, 0, '15,25,51,240'),
            array('lt', -152551239.56, 0, 0, '−152 551 240'),
        );
    }

    /**
     * @dataProvider getPercentNumberFormattingTestData
     */
    public function testPercentNumberFormatting($language, $value, $maximumFractionDigits, $minimumFractionDigits, $expected)
    {
        $this->translator->setCurrentLanguage($language);
        $numberFormatter = new NumberFormatter($this->translator);
        $this->assertEquals($expected, $numberFormatter->formatPercent($value, $maximumFractionDigits, $minimumFractionDigits));
    }

    public function getPercentNumberFormattingTestData()
    {
        return array(
            // english formats
            array('en', 5, 0, 0, '5%'),
            array('en', -5, 0, 3, '-5%'),
            array('en', 5.299, 0, 0, '5%'),
            array('en', 5.299, 3, 0, '5.299%'),
            array('en', -50, 3, 3, '-50.000%'),
            array('en', -50.1, 3, 3, '-50.100%'),
            array('en', 5000, 0, 0, '5,000%'),
            array('en', +5000, 0, 0, '5,000%'),
            array('en', 5000000, 0, 0, '5,000,000%'),
            array('en', -5000000, 0, 0, '-5,000,000%'),

            // foreign languages
            array('ar', 51239.56, 3, 0, '51٬239٫56٪؜'),
            array('be', 51239.56, 3, 0, '51 239,56 %'),
            array('de', 51239.56, 3, 0, '51.239,56 %'),
            array('bn', 152551239.56, 3, 0, '152,551,239.56%'),
            array('hi', 152551239.56, 0, 0, '15,25,51,240%'),
            array('lt', -152551239.56, 0, 0, '−152 551 240 %'),
        );
    }

    /**
     * @dataProvider getPercentNumberEvolutionFormattingTestData
     */
    public function testPercentEvolutionNumberFormatting($language, $value, $expected)
    {
        $this->translator->setCurrentLanguage($language);
        $numberFormatter = new NumberFormatter($this->translator);
        $this->assertEquals($expected, $numberFormatter->formatPercentEvolution($value));
    }

    public function getPercentNumberEvolutionFormattingTestData()
    {
        return array(
            // english formats
            array('en', 5, '+5%'),
            array('en', -5, '-5%'),
            array('en', 5.299, '+5%'),
            array('en', -50, '-50%'),
            array('en', 5000, '+5,000%'),
            array('en', +5000, '+5,000%'),
            array('en', 5000000, '+5,000,000%'),
            array('en', -5000000, '-5,000,000%'),
        );
    }

    public function testChangeLanguage()
    {
        $this->translator->setCurrentLanguage('en');
        $numberFormatter = new NumberFormatter($this->translator);

        $this->assertEquals('5,000.1', $numberFormatter->formatNumber(5000.1, 1));
        $this->assertEquals('50.1%', $numberFormatter->formatPercent(50.1, 1));
        $this->assertEquals('+50%', $numberFormatter->formatPercentEvolution(50));
        $this->assertEquals('$5,000.10', $numberFormatter->formatCurrency(5000.1, '$'));

        $this->translator->setCurrentLanguage('de');
        $this->assertEquals('5.000,1', $numberFormatter->formatNumber(5000.1, 1));
        $this->assertEquals('50,1 %', $numberFormatter->formatPercent(50.1, 1));
        $this->assertEquals('+50 %', $numberFormatter->formatPercentEvolution(50));
        $this->assertEquals('5.000,10 €', $numberFormatter->formatCurrency(5000.1, '€'));

        $this->translator->setCurrentLanguage('ar');
        $this->assertEquals('5٬000٫1٪؜', $numberFormatter->formatPercent(5000.1, 1));

        $this->translator->setCurrentLanguage('bn');
        $this->assertEquals('50,00,000', $numberFormatter->formatNumber(5000000));
    }
}
