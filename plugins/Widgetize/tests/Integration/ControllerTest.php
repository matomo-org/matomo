<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Widgetize\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Plugins\Widgetize\Controller;
use Piwik\Plugins\Widgetize\UrlTokenAuthFailedException;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetConfig;

/**
 * @group Widgetize
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /**
     * @var Controller
     */
    private $controller;
    /**
     * @var array
     */
    private $backupGet;
    /**
     * @var array
     */
    private $backupRequest;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupRequest = $_REQUEST;

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        FakeAccess::clearAccess(
            $superUser = true,
            $idSitesAdmin = [1],
            $idSitesView = [1],
            $identity = 'superUserLogin'
        );

        $_GET = [
            'moduleToWidgetize' => 'Transitions',
            'actionToWidgetize' => 'getTransitions',
            'idSite' => 1,
            'period' => 'day',
            'date' => 'today',
            'widget' => 1,
        ];
        $_REQUEST = $_GET;

        $this->controller = new Controller();
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;
        $_REQUEST = $this->backupRequest;

        unset(Common::$headersSentInTests['Content-Type']);

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    public function testIframeShouldBootstrapClientRenderedWidgetForLegacyWidgetizeUrls(): void
    {
        $html = $this->controller->iframe();

        $this->assertStringContainsString('vue-entry="CoreHome.Widget"', $html);
        $this->assertStringContainsString('widgetized="true"', $html);
        $this->assertStringContainsString('clientComponent', $html);
        $this->assertStringContainsString('TransitionsPage', $html);
    }

    public function testBuildClientWidgetMetadataShouldRejectDisabledWidgets(): void
    {
        $config = new WidgetConfig();
        $config->setClientSideComponent('Transitions', 'TransitionsPage');
        $config->disable();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionWidgetNotEnabled');

        $method = new \ReflectionMethod(Controller::class, 'buildClientWidgetMetadata');
        $method->setAccessible(true);
        $method->invoke($this->controller, $config);
    }

    public function testBuildClientWidgetMetadataShouldIgnoreNonWidgetizableWidgets(): void
    {
        $config = new WidgetConfig();
        $config->setClientSideComponent('Transitions', 'TransitionsPage');
        $config->setIsNotWidgetizable();

        $method = new \ReflectionMethod(Controller::class, 'buildClientWidgetMetadata');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($this->controller, $config));
    }

    public function testIframeRefusesToEmbedActionsThatDoNotReturnHtml(): void
    {
        // Actions returning a non-HTML response (e.g. JSON via Json::sendHeaderJSON()) cannot be embedded.
        Common::$headersSentInTests['Content-Type'] = 'application/json; charset=utf-8';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Dashboard.getAllDashboards cannot be widgetized');

        $this->invokeEmbeddableAssertion('Dashboard', 'getAllDashboards');
    }

    /**
     * @dataProvider getEmbeddableContentTypes
     */
    public function testIframeAllowsActionsThatReturnHtml(string $contentType): void
    {
        $this->expectNotToPerformAssertions();

        Common::$headersSentInTests['Content-Type'] = $contentType;

        // must not throw for HTML responses, or when no explicit content type was set (HTML default)
        $this->invokeEmbeddableAssertion('AnyModule', 'anyAction');
    }

    public function getEmbeddableContentTypes(): array
    {
        return [
            ['text/html; charset=utf-8'],
            ['application/xhtml+xml'],
            [''],
        ];
    }

    private function invokeEmbeddableAssertion(string $module, string $action): void
    {
        $method = new \ReflectionMethod(Controller::class, 'assertDispatchedContentIsEmbeddable');
        $method->setAccessible(true);
        $method->invoke($this->controller, $module, $action);
    }

    public function testIframeThrowsTargetedErrorWhenAnonymousRequestSuppliesTokenAuthInUrl(): void
    {
        $_GET['token_auth'] = 'not-a-real-token-just-to-trigger-check';
        $_REQUEST = $_GET;

        // simulate the state after FrontController failed to reload auth from token_auth: anonymous user
        FakeAccess::clearAccess(false, [], [], 'anonymous');

        // must be a 4xx HttpCodeException so the response is a client error, not a logged HTTP 500
        $this->expectException(UrlTokenAuthFailedException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Widgetize_ErrorTokenAuthFailed');

        $this->invokeAssertUrlTokenAuthDidNotFail();
    }

    public function testIframeErrorMessageMentionsPolicyWhenOnlyAllowSecureAuthTokensIsEnabled(): void
    {
        $_GET['token_auth'] = 'not-a-real-token-just-to-trigger-check';
        $_REQUEST = $_GET;

        FakeAccess::clearAccess(false, [], [], 'anonymous');

        Config::getInstance()->General['only_allow_secure_auth_tokens'] = 1;

        try {
            $this->expectException(UrlTokenAuthFailedException::class);
            $this->expectExceptionCode(401);
            $this->expectExceptionMessage('Widgetize_ErrorTokenAuthFailedDisabledByPolicy');

            $this->invokeAssertUrlTokenAuthDidNotFail();
        } finally {
            Config::getInstance()->General['only_allow_secure_auth_tokens'] = 0;
        }
    }

    public function testIframeDoesNotThrowTargetedErrorForAnonymousToken(): void
    {
        // token_auth=anonymous intentionally authenticates as the anonymous user, so ending up
        // anonymous is the expected outcome and must not trigger the targeted error.
        $_GET['token_auth'] = 'anonymous';
        $_REQUEST = $_GET;

        FakeAccess::clearAccess(false, [], [], 'anonymous');

        $this->expectNotToPerformAssertions();
        $this->invokeAssertUrlTokenAuthDidNotFail();
    }

    public function testIframeDoesNotThrowTargetedErrorWhenNoTokenAuthInUrl(): void
    {
        // no token_auth in URL, anonymous user — the targeted "URL token failed" error must not fire.
        unset($_GET['token_auth']);
        $_REQUEST = $_GET;

        FakeAccess::clearAccess(false, [], [], 'anonymous');

        $this->expectNotToPerformAssertions();
        $this->invokeAssertUrlTokenAuthDidNotFail();
    }

    public function testIframeDoesNotThrowTargetedErrorForAuthenticatedRequest(): void
    {
        $_GET['token_auth'] = 'irrelevant-since-user-is-not-anonymous';
        $_REQUEST = $_GET;

        // authenticated user (non-anonymous identity): the targeted error must not fire.
        FakeAccess::clearAccess(true, [1], [1], 'superUserLogin');

        $this->expectNotToPerformAssertions();
        $this->invokeAssertUrlTokenAuthDidNotFail();
    }

    private function invokeAssertUrlTokenAuthDidNotFail(): void
    {
        $method = new \ReflectionMethod(Controller::class, 'assertUrlTokenAuthDidNotFail');
        $method->setAccessible(true);
        $method->invoke($this->controller);
    }
}
