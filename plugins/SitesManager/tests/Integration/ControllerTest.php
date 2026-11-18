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
use Piwik\Plugins\SitesManager\API;
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

    /** @var array */
    private $backupPost;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupPost = $_POST;

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
        $_POST = $this->backupPost;

        parent::tearDown();
    }

    public function testSiteWithoutDataCtaContainsInviteTeamMemberLinkByDefault()
    {
        $html = $this->renderSiteWithoutDataCta();

        self::assertStringContainsString('icon-user-add', $html);
    }

    public function testInviteUserLinkOpensTheInviteForm()
    {
        self::assertStringContainsString('showadduser=1', $this->getInviteUserLink());
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

    public function testSetGlobalSettingsPostsEventWithStoredSettings()
    {
        $postedSettings = [];
        Piwik::addAction('SitesManager.globalSettingsUpdated', function ($settings) use (&$postedSettings) {
            $postedSettings[] = $settings;
        });

        $_POST = $this->getGlobalSettingsRequest();

        $response = $this->getControllerSkippingTokenCheck()->setGlobalSettings();

        self::assertStringContainsString('success', $response);
        self::assertSame([[
            'keepURLFragmentsGlobal' => true,
            'defaultCurrency' => 'EUR',
            'searchKeywordParametersGlobal' => 'q,query',
            'searchCategoryParametersGlobal' => 'cat',
            'defaultTimezone' => 'Europe/Berlin',
            'excludedIpsGlobal' => '10.0.0.1,192.168.0.0/16',
            'excludedQueryParametersGlobal' => 'sessionid',
            'excludedUserAgentsGlobal' => 'bot',
            'excludedReferrersGlobal' => 'spam.example.com',
            'exclusionTypeForQueryParams' => 'custom',
        ]], $postedSettings);
    }

    public function testSetGlobalSettingsPostsEventWhenSaveFailsAfterAChangeWasSaved()
    {
        $postedSettings = [];
        Piwik::addAction('SitesManager.globalSettingsUpdated', function ($settings) use (&$postedSettings) {
            $postedSettings[] = $settings;
        });

        $_POST = array_merge($this->getGlobalSettingsRequest(), ['currency' => 'not-a-currency']);

        $response = $this->getControllerSkippingTokenCheck()->setGlobalSettings();

        self::assertStringContainsString('error', $response);
        self::assertCount(1, $postedSettings);
        self::assertSame('Europe/Berlin', $postedSettings[0]['defaultTimezone']);
        self::assertSame('USD', $postedSettings[0]['defaultCurrency']);
        self::assertFalse($postedSettings[0]['excludedIpsGlobal']);
    }

    public function testSetGlobalSettingsDoesNotPostEventWhenSaveFailsBeforeAChangeWasSaved()
    {
        $eventCount = 0;
        Piwik::addAction('SitesManager.globalSettingsUpdated', function () use (&$eventCount) {
            $eventCount++;
        });

        $_POST = array_merge($this->getGlobalSettingsRequest(), ['timezone' => 'UTC', 'currency' => 'not-a-currency']);

        $response = $this->getControllerSkippingTokenCheck()->setGlobalSettings();

        self::assertStringContainsString('error', $response);
        self::assertSame(0, $eventCount);
    }

    public function testSetGlobalSettingsReportsSuccessWhenListenerFails()
    {
        Piwik::addAction('SitesManager.globalSettingsUpdated', function () {
            throw new \Exception('listener failed');
        });

        $_POST = $this->getGlobalSettingsRequest();

        $response = $this->getControllerSkippingTokenCheck()->setGlobalSettings();

        self::assertStringContainsString('success', $response);
        self::assertSame('Europe/Berlin', API::getInstance()->getDefaultTimezone());
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    private function getControllerSkippingTokenCheck(): Controller
    {
        $controller = $this->getMockBuilder(Controller::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkTokenInUrl'])
            ->getMock();

        $controller->expects($this->once())
            ->method('checkTokenInUrl');

        return $controller;
    }

    private function getGlobalSettingsRequest(): array
    {
        return [
            'format' => 'json',
            'timezone' => 'Europe/Berlin',
            'excludedIps' => '10.0.0.1,192.168.0.0/16',
            'excludedQueryParameters' => 'sessionid',
            'excludedUserAgents' => 'bot',
            'excludedReferrers' => 'spam.example.com',
            'currency' => 'EUR',
            'searchKeywordParameters' => 'q,query',
            'searchCategoryParameters' => 'cat',
            'keepURLFragments' => '1',
            'exclusionTypeForQueryParams' => 'custom',
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

    private function getInviteUserLink(): string
    {
        return $this->invokeControllerMethod('getInviteUserLink');
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
