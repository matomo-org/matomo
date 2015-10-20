<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
class NumberFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Translator
     */
    private $translator;

    public function setUp()
    {
        \Piwik\Plugin\Manager::getInstance()->loadPluginTranslations();

        $this->translator = StaticContainer::get('Piwik\Translation\Translator');
    }

    public function tearDown()
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

        $this->assertEquals($expected, $numberFormatter->formatNumber($value, $maximumFractionDigits, $minimumFractionDigits));
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
            array('en', -50, 3, 3, '-50%'),
            array('en', 5000, 0, 0, '5,000%'),
            array('en', +5000, 0, 0, '5,000%'),
            array('en', 5000000, 0, 0, '5,000,000%'),
            array('en', -5000000, 0, 0, '-5,000,000%'),

            // foreign languages
            array('ar', 51239.56, 3, 0, '51٬239٫56٪'),
            array('be', 51239.56, 3, 0, '51 239,56 %'),
            array('de', 51239.56, 3, 0, '51.239,56 %'),
            array('bn', 152551239.56, 3, 0, '15,25,51,239.56%'),
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
}
