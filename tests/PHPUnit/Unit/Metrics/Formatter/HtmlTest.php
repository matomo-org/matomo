<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Metrics\Formatter;

use Piwik\Container\StaticContainer;
use Piwik\Metrics\Formatter\Html;
use Piwik\Tests\Framework\Fixture;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

/**
 * @group Core
 */
class HtmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Html
     */
    private $formatter;

    private $sitesInfo;

    public function setUp(): void
    {
        $this->sitesInfo = array(
            1 => array(
                'idsite' => '1',
                'currency' => 'EUR'
            )
        );

        $this->formatter = new Html();

        Fixture::loadAllTranslations();
        $this->setSiteManagerApiMock();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        $this->unsetSiteManagerApiMock();
    }

    public function testGetPrettyTimeFromSecondsDefaultsToShowingSentencesAndUsesNonBreakingSpaces()
    {
        $expected = '1&nbsp;days&nbsp;10&nbsp;hours';
        $value = $this->formatter->getPrettyTimeFromSeconds(86400 + 3600 * 10);

        $this->assertEquals($expected, $value);
    }

    public function testGetPrettySizeFromBytesUsesNonBreakingSpaces()
    {
        $expected = '1.5&nbsp;K';
        $value = $this->formatter->getPrettySizeFromBytes(1536);

        $this->assertEquals($expected, $value);
    }

    public function testGetPrettySizeFromBytesInFixedUnitThatIsHigherThanBestUnit()
    {
        $expected = '0.001465&nbsp;M';
        $value = $this->formatter->getPrettySizeFromBytes(1536, 'M', 6);

        $this->assertEquals($expected, $value);
    }

    public function testGetPrettySizeFromBytesInUnitThatIsLowerThanBestUnit()
    {
        $expected = '1536&nbsp;B';
        $value = $this->formatter->getPrettySizeFromBytes(1536, 'B');

        $this->assertEquals($expected, $value);
    }

    public function testGetPrettyMoneyUsesNonBreakingSpaces()
    {
        StaticContainer::get('Piwik\Translation\Translator')->setCurrentLanguage('de');

        $expected = html_entity_decode('1&nbsp;€');
        $value = $this->formatter->getPrettyMoney(1, 1);

        $this->assertEquals($expected, $value);
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
