<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

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

    public function test_constructor_throwsException_ifSiteDoesNotExist()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);
        $this->expectExceptionMessage('An unexpected website was found in the request');

        $this->makeSite(9999);
    }

    public function test_constructor_enrichesSite()
    {
        $site = $this->makeSite($this->idSite);
        $this->assertSame('Piwik test' . $this->siteAppendix, $site->getName());
    }

    public function test_construct_enrichesSiteEvenIfSiteWasSetToCachePreviously()
    {
        $site = API::getInstance()->getSiteFromId($this->idSite);
        Site::setSiteFromArray($this->idSite, $site);

        $site = $this->makeSite($this->idSite);
        $this->assertSame('Piwik test' . $this->siteAppendix, $site->getName());
    }

    public function test_construct_whenRemovingSiteFromGlobalSitesArray_TheObjectItselfStillworks()
    {
        $site = $this->makeSite($this->idSite);
        $this->assertSame('Piwik test' . $this->siteAppendix, $site->getName());

        Site::clearCache();

        $this->assertSame('Piwik test' . $this->siteAppendix, $site->getName());
        $this->assertSame(array(), Site::getSites()); // make sure data was not fetched again
    }

    private function makeSite($idSite)
    {
        return new Site($idSite);
    }
}
