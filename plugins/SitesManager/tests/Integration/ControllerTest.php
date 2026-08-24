<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\Controller;
use Piwik\SiteContentDetector;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SitesManager
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /** @var Controller */
    private $controller;

    /** @var array */
    private $backupGet;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2021-01-01 00:00:00');
        }

        FakeAccess::clearAccess(
            $superUser = true,
            $idSitesAdmin = [1],
            $idSitesView = [1],
            $identity = 'superUserLogin'
        );

        $_GET = ['idSite' => 1, 'period' => 'day', 'date' => 'today'];

        $this->controller = new Controller(StaticContainer::get(SiteContentDetector::class));
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;

        parent::tearDown();
    }

    public function testSiteWithoutDataCtaContainsInviteTeamMemberLinkByDefault()
    {
        $html = $this->renderSiteWithoutDataCta();

        self::assertStringContainsString('icon-user-add', $html);
    }

    public function testAfterTrackingMethodsContentIsEmptyByDefault()
    {
        self::assertSame('', $this->getAfterTrackingMethodsContent());
    }

    public function testShowInviteTeamMemberLinkListenerRemovesInviteLinkButKeepsAdditionalCta()
    {
        Piwik::addAction('SitesManager.siteWithoutData.showInviteTeamMemberLink', function (&$showInviteTeamMemberLink) {
            $showInviteTeamMemberLink = false;
        });
        Piwik::addAction('Template.siteWithoutData.additionalCta', function (&$content) {
            $content .= '<div class="additionalCtaMarker">additional cta</div>';
        });

        $html = $this->renderSiteWithoutDataCta();

        self::assertStringNotContainsString('icon-user-add', $html);
        self::assertStringContainsString('additionalCtaMarker', $html);
    }

    public function testAfterTrackingMethodsListenerContentIsReturned()
    {
        Piwik::addAction('Template.siteWithoutData.afterTrackingMethods', function (&$content) {
            $content .= '<div class="afterTrackingMethodsMarker">after tracking methods</div>';
        });

        self::assertSame(
            '<div class="afterTrackingMethodsMarker">after tracking methods</div>',
            $this->getAfterTrackingMethodsContent()
        );
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    private function renderSiteWithoutDataCta(): string
    {
        return $this->invokeControllerMethod('renderSiteWithoutDataCta');
    }

    private function getAfterTrackingMethodsContent(): string
    {
        return $this->invokeControllerMethod('getAfterTrackingMethodsContent');
    }

    /**
     * siteWithoutData() and getTrackingMethodsForSite() both delegate to these private helpers.
     * Exercising the actions end-to-end would run live site content detection or render the full
     * page layout, so the helpers are tested directly instead.
     */
    private function invokeControllerMethod(string $method)
    {
        $reflection = new \ReflectionMethod($this->controller, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($this->controller);
    }
}
