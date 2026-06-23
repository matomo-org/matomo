<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Request;

use Piwik\Cache;

class AuthenticationToken extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        $_GET = $_POST = [];
        unset($_SERVER['HTTP_AUTHORIZATION']);
        $this->setNestedApiInvocationCount(0);
        Cache::getTransientCache()->delete('API.setIsRootRequestApiRequest');
    }

    /**
     * @dataProvider provideGetAuthenticationTokenData
     */
    public function testGetAuthenticationToken($getParams, $postParams, $authorizationHeader, $requestParams, $expectedToken, $isSecure, $isSessionToken)
    {
        $_GET = $getParams;
        $_POST = $postParams;
        $_SERVER['HTTP_AUTHORIZATION'] = $authorizationHeader;

        $token = new \Piwik\Request\AuthenticationToken();
        self::assertEquals($expectedToken, $token->getAuthToken($requestParams));
        self::assertEquals($isSecure, $token->wasTokenAuthProvidedSecurely());
        self::assertEquals($isSessionToken, $token->isSessionToken());
    }

    public function provideGetAuthenticationTokenData(): iterable
    {
        yield 'token in GET request only' => [
            ['token_auth' => 'randomGetAccessToken'],
            [],
            null,
            null,
            'randomGetAccessToken',
            false, // insecure
            false, // no session token
        ];

        yield 'session token in GET request only' => [
            ['token_auth' => 'randomGetAccessToken', 'force_api_session' => 1],
            [],
            null,
            null,
            'randomGetAccessToken',
            false, // insecure
            true, // session token
        ];

        yield 'token in POST request only' => [
            [],
            ['token_auth' => 'randomPostAccessToken'],
            null,
            null,
            'randomPostAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'session token in POST request only' => [
            [],
            ['token_auth' => 'randomPostAccessToken', 'force_api_session' => 1],
            null,
            null,
            'randomPostAccessToken',
            true, // secure
            true, // session token
        ];

        yield 'token in auth header only' => [
            [],
            [],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in POST request overwrites GET token' => [
            ['token_auth' => 'randomPostAccessToken'],
            ['token_auth' => 'randomPostAccessToken'],
            null,
            null,
            'randomPostAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in POST request overwrites GET session token' => [
            ['token_auth' => 'randomPostAccessToken', 'force_api_session' => 1],
            ['token_auth' => 'randomPostAccessToken'],
            null,
            null,
            'randomPostAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET token' => [
            ['token_auth' => 'randomHeaderAccessToken'],
            [],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET session token' => [
            ['token_auth' => 'randomHeaderAccessToken', 'force_api_session' => 1],
            [],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites POST token' => [
            [],
            ['token_auth' => 'randomHeaderAccessToken'],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites POST session token' => [
            [],
            ['token_auth' => 'randomHeaderAccessToken', 'force_api_session' => 1],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET and POST token' => [
            ['token_auth' => 'randomHeaderAccessToken'],
            ['token_auth' => 'randomHeaderAccessToken'],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET session and POST token' => [
            ['token_auth' => 'randomHeaderAccessToken', 'force_api_session' => 1],
            ['token_auth' => 'randomHeaderAccessToken'],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET and POST session token' => [
            ['token_auth' => 'randomHeaderAccessToken'],
            ['token_auth' => 'randomHeaderAccessToken', 'force_api_session' => 1],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET session and POST session token' => [
            ['token_auth' => 'randomHeaderAccessToken', 'force_api_session' => 1],
            ['token_auth' => 'randomHeaderAccessToken', 'force_api_session' => 1],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];


        yield 'incorrectly provided token in header will be discarded' => [
            [],
            [],
            'realm=randomHeaderAccessToken',
            null,
            '',
            false, // secure
            false, // no session token
        ];
    }

    /**
     * @dataProvider provideConflictingAuthParametersData
     */
    public function testGetAuthenticationTokenThrowsOnConflictingAuthParameters(array $getParams, array $postParams, ?string $authorizationHeader)
    {
        $this->expectException(\Piwik\Http\BadRequestException::class);
        $this->expectExceptionCode(400);

        $_GET = $getParams;
        $_POST = $postParams;
        $_SERVER['HTTP_AUTHORIZATION'] = $authorizationHeader;

        $token = new \Piwik\Request\AuthenticationToken();
        $token->getAuthToken();
    }

    public function provideConflictingAuthParametersData(): iterable
    {
        yield 'conflicting token_auth between GET and POST' => [
            ['token_auth' => 'randomGetAccessToken'],
            ['token_auth' => 'randomPostAccessToken'],
            null,
        ];

        yield 'conflicting token_auth between header and GET' => [
            ['token_auth' => 'randomGetAccessToken'],
            [],
            'Bearer randomHeaderAccessToken',
        ];

        yield 'conflicting token_auth between header and POST' => [
            [],
            ['token_auth' => 'randomPostAccessToken'],
            'Bearer randomHeaderAccessToken',
        ];

        yield 'conflicting token_auth between header and GET/POST' => [
            ['token_auth' => 'sameRequestToken'],
            ['token_auth' => 'sameRequestToken'],
            'Bearer randomHeaderAccessToken',
        ];

        yield 'conflicting force_api_session without token_auth on both sources' => [
            ['force_api_session' => 1],
            ['force_api_session' => 0],
            null,
        ];

        yield 'conflicting force_api_session when token_auth only provided in one source' => [
            ['token_auth' => 'randomGetAccessToken', 'force_api_session' => 1],
            ['force_api_session' => 0],
            null,
        ];
    }

    /**
     * @dataProvider provideConflictingAuthParametersForNestedApiRequestData
     */
    public function testGetAuthenticationTokenDoesNotThrowOnConflictingAuthForNestedApiRequest(
        array $getParams,
        array $postParams,
        ?string $authorizationHeader,
        string $expectedToken,
        bool $expectedSessionToken
    ) {
        $this->setNestedApiInvocationCount(2);

        $_GET = $getParams;
        $_POST = $postParams;
        $_SERVER['HTTP_AUTHORIZATION'] = $authorizationHeader;

        $token = new \Piwik\Request\AuthenticationToken();
        self::assertEquals($expectedToken, $token->getAuthToken());
        self::assertSame($expectedSessionToken, $token->isSessionToken());
    }

    public function provideConflictingAuthParametersForNestedApiRequestData(): iterable
    {
        yield 'conflicting token_auth and force_api_session between GET and POST' => [
            ['token_auth' => 'randomGetAccessToken', 'force_api_session' => 1],
            ['token_auth' => 'randomPostAccessToken', 'force_api_session' => 0],
            null,
            'randomPostAccessToken',
            false,
        ];

        yield 'conflicting token_auth including header and conflicting force_api_session' => [
            ['token_auth' => 'randomGetAccessToken', 'force_api_session' => 1],
            ['token_auth' => 'randomPostAccessToken', 'force_api_session' => 0],
            'Bearer randomHeaderAccessToken',
            'randomHeaderAccessToken',
            false,
        ];

        yield 'force_api_session conflict only keeps POST session state in nested requests' => [
            ['token_auth' => 'sameToken', 'force_api_session' => 0],
            ['token_auth' => 'sameToken', 'force_api_session' => 1],
            null,
            'sameToken',
            true,
        ];
    }

    /**
     * Overlay.startOverlaySession is a browser navigation endpoint with a fixed request shape.
     * GET credentials are not part of that shape and must not be consumed as authentication.
     *
     * @dataProvider provideOverlayNavigationEndpointData
     */
    public function testGetAuthenticationTokenIgnoresGetCredentialsOnOverlayNavigationEndpoint(array $getParams)
    {
        $_GET = array_merge(['module' => 'Overlay', 'action' => 'startOverlaySession'], $getParams);
        $_POST = [];
        $_SERVER['HTTP_AUTHORIZATION'] = null;

        $token = new \Piwik\Request\AuthenticationToken();

        self::assertSame('', $token->getAuthToken());
        self::assertFalse($token->wasTokenAuthProvidedSecurely());
        self::assertFalse($token->isSessionToken());
    }

    public function provideOverlayNavigationEndpointData(): iterable
    {
        yield 'GET token_auth alone is ignored' => [
            ['token_auth' => 'shouldBeIgnoredAccessToken'],
        ];

        yield 'GET token_auth with force_api_session is ignored' => [
            ['token_auth' => 'shouldBeIgnoredAccessToken', 'force_api_session' => 1],
        ];

        yield 'GET force_api_session without token is ignored' => [
            ['force_api_session' => 1],
        ];
    }

    public function testOverlayNavigationEndpointDoesNotTreatGetTokenAsConflict()
    {
        // A session-authenticated user reaching startOverlaySession with stale GET credentials
        // must not trigger a conflicting-auth-parameters exception, since the GET value is
        // ignored on this endpoint.
        $_GET = [
            'module' => 'Overlay',
            'action' => 'startOverlaySession',
            'token_auth' => 'staleGetToken',
        ];
        $_POST = ['token_auth' => 'differentPostToken'];
        $_SERVER['HTTP_AUTHORIZATION'] = null;

        $token = new \Piwik\Request\AuthenticationToken();

        self::assertSame('differentPostToken', $token->getAuthToken());
    }

    public function testGetAuthenticationTokenStillAcceptsGetTokenOnOtherEndpoints()
    {
        // Sanity check: the navigation-only exception is scoped to module=Overlay&action=startOverlaySession.
        // Other endpoints that legitimately accept token_auth in the URL (widgetize, tracker, etc.)
        // continue to work as before.
        $_GET = [
            'module' => 'API',
            'action' => 'index',
            'method' => 'API.getMatomoVersion',
            'token_auth' => 'someAccessToken',
        ];
        $_POST = [];
        $_SERVER['HTTP_AUTHORIZATION'] = null;

        $token = new \Piwik\Request\AuthenticationToken();

        self::assertSame('someAccessToken', $token->getAuthToken());
        self::assertFalse($token->wasTokenAuthProvidedSecurely());
    }

    private function setNestedApiInvocationCount(int $count): void
    {
        $reflectionClass = new \ReflectionClass(\Piwik\API\Request::class);
        $reflectionProperty = $reflectionClass->getProperty('nestedApiInvocationCount');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, $count);

        if ($count > 0) {
            \Piwik\API\Request::setIsRootRequestApiRequest('API.getPiwikVersion');
        } else {
            Cache::getTransientCache()->delete('API.setIsRootRequestApiRequest');
        }
    }
}
