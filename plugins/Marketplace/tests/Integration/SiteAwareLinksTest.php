<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Access;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\Marketplace\SiteAwareLinks;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugins
 * @group Marketplace
 * @group SiteAwareLinksTest
 * @group SiteAwareLinks
 */
class SiteAwareLinksTest extends IntegrationTestCase
{
    /**
     * @var SiteAwareLinks
     */
    private $siteAwareLinks;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-01-01 02:02:02');
        Fixture::createWebsite('2014-01-01 02:02:02');

        $this->siteAwareLinks = new SiteAwareLinks();
    }

    public function tearDown(): void
    {
        unset($_GET['idSite']);

        parent::tearDown();
    }

    public function testGetCurrentValidIdSiteOrDefaultReturnsRequestedIdSiteWhenItExists()
    {
        $_GET['idSite'] = '2';

        $this->assertSame(2, $this->siteAwareLinks->getCurrentValidIdSiteOrDefault());
    }

    public function testGetCurrentValidIdSiteOrDefaultFallsBackToDefaultWhenRequestedIdSiteDoesNotExist()
    {
        $_GET['idSite'] = '9999';

        $this->assertSame($this->defaultWebsiteId(), $this->siteAwareLinks->getCurrentValidIdSiteOrDefault());
    }

    public function testGetCurrentValidIdSiteOrDefaultFallsBackToDefaultWhenNoIdSiteRequested()
    {
        $this->assertSame($this->defaultWebsiteId(), $this->siteAwareLinks->getCurrentValidIdSiteOrDefault());
    }

    public function testGetCurrentValidIdSiteOrDefaultFallsBackToDefaultForNonNumericIdSite()
    {
        $_GET['idSite'] = 'notanumber';

        $this->assertSame($this->defaultWebsiteId(), $this->siteAwareLinks->getCurrentValidIdSiteOrDefault());
    }

    public function testGetCurrentValidIdSiteOrDefaultFallsBackToDefaultWhenUserHasNoAccessToRequestedIdSite()
    {
        $_GET['idSite'] = '2';

        // A non-super user who may only view site 1 but requests site 2. Without the
        // access check this would leak an inaccessible idSite into the generated link.
        $realAccess = Access::getInstance();
        $noAccessToSite2 = new FakeAccess($superUser = false, $idSitesAdmin = [], $idSitesView = [1], $identity = 'testUser');
        StaticContainer::getContainer()->set('Piwik\Access', $noAccessToSite2);

        try {
            $result = $this->siteAwareLinks->getCurrentValidIdSiteOrDefault();
        } finally {
            StaticContainer::getContainer()->set('Piwik\Access', $realAccess);
        }

        $this->assertSame(1, $result);
    }

    public function testGetIdSiteParameterReturnsTheValidatedIdSite()
    {
        $_GET['idSite'] = '2';

        $this->assertSame(['idSite' => 2], $this->siteAwareLinks->getIdSiteParameter());
    }

    public function testGetIdSiteParameterFallsBackToDefaultForInvalidIdSite()
    {
        $_GET['idSite'] = '9999';

        $this->assertSame(['idSite' => $this->defaultWebsiteId()], $this->siteAwareLinks->getIdSiteParameter());
    }

    public function testGetActionUrlContainsModuleActionAndValidatedIdSite()
    {
        $_GET['idSite'] = '2';

        $url = $this->siteAwareLinks->getActionUrl('subscriptionOverview');

        $this->assertStringStartsWith('?', $url);
        $this->assertStringContainsString('module=Marketplace', $url);
        $this->assertStringContainsString('action=subscriptionOverview', $url);
        $this->assertStringContainsString('idSite=2', $url);
    }

    public function testGetActionUrlMergesAdditionalParameters()
    {
        $_GET['idSite'] = '2';

        $url = $this->siteAwareLinks->getActionUrl('overview', ['activated' => '1']);

        $this->assertStringContainsString('action=overview', $url);
        $this->assertStringContainsString('activated=1', $url);
    }

    public function testGetActionUrlDoesNotLetCallerOverrideTrustedParameters()
    {
        $_GET['idSite'] = '2';

        $url = $this->siteAwareLinks->getActionUrl('overview', [
            'module' => 'Evil',
            'action' => 'evil',
            'idSite' => '999',
        ]);

        $this->assertStringContainsString('module=Marketplace', $url);
        $this->assertStringContainsString('action=overview', $url);
        $this->assertStringContainsString('idSite=2', $url);
        $this->assertStringNotContainsString('Evil', $url);
        $this->assertStringNotContainsString('idSite=999', $url);
    }

    public function testGetOverviewUrlWithoutPluginHasNoShowPluginHash()
    {
        $_GET['idSite'] = '2';

        $url = $this->siteAwareLinks->getOverviewUrl();

        $this->assertStringContainsString('action=overview', $url);
        $this->assertStringNotContainsString('showPlugin', $url);
    }

    public function testGetOverviewUrlWithPluginAppendsShowPluginHash()
    {
        $_GET['idSite'] = '2';

        $url = $this->siteAwareLinks->getOverviewUrl('MyPluginName');

        $this->assertStringContainsString('action=overview', $url);
        $this->assertStringContainsString('#?showPlugin=MyPluginName', $url);
    }

    private function defaultWebsiteId(): int
    {
        return (int) (new UserPreferences())->getDefaultWebsiteId();
    }
}
