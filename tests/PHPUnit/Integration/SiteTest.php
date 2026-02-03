<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Http\BadRequestException;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\Site;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class SiteTest extends IntegrationTestCase
{
    private $idSite;

    public $siteAppendix = ' foo';

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2014-01-02 03:04:05');

        $self = $this;

        Piwik::addAction('Site.setSites', function (&$sites) use ($self) {
            foreach ($sites as &$site) {
                if (strpos($site['name'], $self->siteAppendix) !== 0) {
                    $site['name'] .= $self->siteAppendix;
                }
            }
        });
    }

    public function testConstructorThrowsExceptionIfSiteDoesNotExist()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);
        $this->expectExceptionMessage('An unexpected website was found in the request');

        $this->makeSite(9999);
    }

    public function testConstructorEnrichesSite()
    {
        $site = $this->makeSite($this->idSite);
        $this->assertSame('Piwik test' . $this->siteAppendix, $site->getName());
    }

    public function testConstructEnrichesSiteEvenIfSiteWasSetToCachePreviously()
    {
        $site = API::getInstance()->getSiteFromId($this->idSite);
        Site::setSiteFromArray($this->idSite, $site);

        $site = $this->makeSite($this->idSite);
        $this->assertSame('Piwik test' . $this->siteAppendix, $site->getName());
    }

    public function testConstructWhenRemovingSiteFromGlobalSitesArrayTheObjectItselfStillworks()
    {
        $site = $this->makeSite($this->idSite);
        $this->assertSame('Piwik test' . $this->siteAppendix, $site->getName());

        Site::clearCache();

        $this->assertSame('Piwik test' . $this->siteAppendix, $site->getName());
        $this->assertSame(array(), Site::getSites()); // make sure data was not fetched again
    }

    public function testGetIdSitesFromIdSitesStringFiltersInvalidByDefault()
    {
        $result = Site::getIdSitesFromIdSitesString('1,foo,2,,0,-3');
        $this->assertSame([1, 2], $result);
    }

    public function testGetIdSitesFromIdSitesStringAllowsValidIdsWhenStrict()
    {
        $result = Site::getIdSitesFromIdSitesString([1, '2'], false, true);
        $this->assertSame([1, 2], $result);
    }

    /**
     * @dataProvider getInvalidIdSiteStrings
     */
    public function testGetIdSitesFromIdSitesStringThrowsOnInvalidWhenStrict($idSites)
    {
        $this->expectException(BadRequestException::class);
        Site::getIdSitesFromIdSitesString($idSites, false, true);
    }


    public function getInvalidIdSiteStrings(): iterable
    {
        yield "negative int value" => ['1,-1'];
        yield "zero value" => ['1,0'];
        yield "boolean value" => [true];
        yield "float value" => ['1,2.5'];
        yield "string value" => ['1,foo'];
    }

    private function makeSite($idSite)
    {
        return new Site($idSite);
    }
}
