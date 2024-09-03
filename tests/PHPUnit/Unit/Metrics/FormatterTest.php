<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Metrics;

use Piwik\Container\StaticContainer;
use Piwik\Metrics\Formatter;
use Piwik\NumberFormatter;
use Piwik\Tests\Framework\Fixture;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

/**
 * @group Core
 */
class FormatterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Formatter
     */
    private $formatter;

    private $sitesInfo;

    public function setUp(): void
    {
        $this->sitesInfo = array(
            1 => array(
                'idsite' => '1',
                'currency' => 'EUR'
            ),
            2 => array(
                'idsite' => '2',
                'currency' => 'DKK'
            ),
            3 => array(
                'idsite' => '3',
                'currency' => 'PLN'
            ),
            4 => array(
                'idsite' => '4',
                'currency' => 'NZD'
            ),
            5 => array(
                'idsite' => '5',
                'currency' => 'JPY'
            )
        );

        $this->formatter = new Formatter();

        Fixture::loadAllTranslations();
        $this->setSiteManagerApiMock();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        NumberFormatter::getInstance()->clearCache();
        $this->unsetSiteManagerApiMock();
    }

    /**
     * @dataProvider getPrettyNumberTestData
     */
    public function testGetPrettyNumberReturnsCorrectResult($number, $expected)
    {
        $this->assertEquals($expected, $this->formatter->getPrettyNumber($number, 2));
    }

    /**
     * @dataProvider getPrettyNumberLocaleTestData
     */
    public function testGetPrettyNumberReturnsCorrectResultWhenLocaleIsEuropean($number, $expected)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage('de');
        $this->assertEquals($expected, $this->formatter->getPrettyNumber($number, 2));
    }

    /**
     * @dataProvider getPrettySizeFromBytesTestData
     */
    public function testGetPrettySizeFromBytesReturnsCorrectResult($bytesSize, $unit, $expected)
    {
        $this->assertEquals($expected, $this->formatter->getPrettySizeFromBytes($bytesSize, $unit));
    }

    /**
     * @dataProvider getPrettyMoneyTestData
     */
    public function testGetPrettyMoneyReturnsCorrectResult($value, $idSite, $language, $expected)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);

        $this->assertEquals($expected, $this->formatter->getPrettyMoney($value, $idSite));
    }

    /**
     * @dataProvider getPrettyPercentFromQuotientTestData
     */
    public function testGetPrettyPercentFromQuotientReturnsCorrectResult($value, $language, $expected)
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage($language);

        $this->assertEquals($expected, $this->formatter->getPrettyPercentFromQuotient($value));
    }

    /**
     * @dataProvider getPrettyTimeFromSecondsData
     */
    public function testGetPrettyTimeFromSecondsReturnsCorrectResult($seconds, $expected)
    {
        if (($seconds * 100) > PHP_INT_MAX || ($seconds * 100 * -1) > PHP_INT_MAX) {
            $this->markTestSkipped("Will not pass on 32-bit machine.");
        }

        $sentenceExpected = $expected[0];
        $this->assertEquals($sentenceExpected, $this->formatter->getPrettyTimeFromSeconds($seconds, $sentence = true));

        $numericExpected = $expected[1];
        $this->assertEquals($numericExpected, $this->formatter->getPrettyTimeFromSeconds($seconds, $sentence = false));
    }

    public function getPrettyNumberTestData()
    {
        return array(
            array(0.14, '0.14'),
            array(0.14567, '0.15'),
            array(100.1234, '100.12'),
            array(1000.45, '1,000.45'),
            array(23456789.00, '23,456,789')
        );
    }

    public function getPrettyNumberLocaleTestData()
    {
        return array(
            array(0.14, '0,14'),
            array(0.14567, '0,15'),
            array(100.1234, '100,12'),
            array(1000.45, '1.000,45'),
            array(23456789.00, '23.456.789'),
        );
    }

    public function getPrettySizeFromBytesTestData()
    {
        return array(
            array(767, null, '767 B'),
            array(1024, null, '1 K'),
            array(1536, null, '1.5 K'),
            array(1024 * 1024, null, '1 M'),
            array(1.25 * 1024 * 1024, null, '1.3 M'),
            array(1.25 * 1024 * 1024 * 1024, null, '1.3 G'),
            array(1.25 * 1024 * 1024 * 1024 * 1024, null, '1.3 T'),
            array(1.25 * 1024 * 1024 * 1024 * 1024 * 1024, null, '1280 T'),
            array(1.25 * 1024 * 1024, 'M', '1.3 M'),
            array(1.25 * 1024 * 1024 * 1024, 'M', '1280 M'),
            array(0, null, '0 M')
        );
    }

    public function getPrettyMoneyTestData()
    {
        return array(
            array(1, 1, 'en', '€1'),
            array(1.045, 2, 'en', 'kr1.05'),
            array(1000.4445, 3, 'en', 'zł1,000.44'),
            array(1234.56, 4, 'en', 'NZ$1,234.56'),
            array(234.76, 5, 'en', '¥234.76'),
            array(234.76, 5, 'de', '234,76 ¥'),
            array(234.76, 5, 'kr', '¥234.76'),
        );
    }

    public function getPrettyPercentFromQuotientTestData()
    {
        return array(
            array(100, 'en', '10,000%'),
            array(1, 'en', '100%'),
            array(.85, 'en', '85%'),
            array(.89999, 'en', '89.999%'),
            array(.0004, 'en', '0.04%'),
            array(0.123, 'eu', '% 12,3'),
            array(0.103, 'zh-cn', '10.3%'),
        );
    }

    /**
     * Dataprovider for testGetPrettyTimeFromSeconds
     */
    public function getPrettyTimeFromSecondsData()
    {
        return array(
            array(30, array('30s', '00:00:30')),
            array(60, array('1 min 0s', '00:01:00')),
            array(100, array('1 min 40s', '00:01:40')),
            array(3600, array('1 hours 0 min', '01:00:00')),
            array(3700, array('1 hours 1 min', '01:01:40')),
            array(86400 + 3600 * 10, array('1 days 10 hours', '1 days 10:00:00')),
            array(86400 * 365, array('365 days 0 hours', '365 days 00:00:00')),
            array((86400 * (365.25 + 10)), array('1 years 10 days', '375 days 06:00:00')),
            array(1.342, array('1.34s', '00:00:01.34')),
            array(.342, array('0.34s', '00:00:00.34')),
            array(.02, array('0.02s', '00:00:00.02')),
            array(.002, array('0.002s', '00:00:00')),
            array(1.002, array('1s', '00:00:01')),
            array(1.02, array('1.02s', '00:00:01.02')),
            array(1.2, array('1.2s', '00:00:01.20')),
            array(122.1, array('2 min 2.1s', '00:02:02.10')),
            array(-122.1, array('-2 min 2.1s', '-00:02:02.10')),
            array(86400 * -365, array('-365 days 0 hours', '-365 days 00:00:00'))
        );
    }

    private function unsetSiteManagerApiMock()
    {
        SitesManagerAPI::unsetInstance();
    }

    private function setSiteManagerApiMock()
    {
        $sitesInfo = $this->sitesInfo;

        $mock = $this->getMockBuilder('stdClass')->addMethods(['getSiteFromId'])->getMock();
        $mock->expects($this->any())->method('getSiteFromId')->willReturnCallback(function ($idSite) use ($sitesInfo) {
            return $sitesInfo[$idSite];
        });

        SitesManagerAPI::setSingletonInstance($mock);
    }
}
