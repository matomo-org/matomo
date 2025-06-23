<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Request;

class AuthenticationToken extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        $_GET = $_POST = [];
        unset($_SERVER['HTTP_AUTHORIZATION']);
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
            ['token_auth' => 'randomGetAccessToken'],
            ['token_auth' => 'randomPostAccessToken'],
            null,
            null,
            'randomPostAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in POST request overwrites GET session token' => [
            ['token_auth' => 'randomGetAccessToken', 'force_api_session' => 1],
            ['token_auth' => 'randomPostAccessToken'],
            null,
            null,
            'randomPostAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET token' => [
            ['token_auth' => 'randomGetAccessToken'],
            [],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET session token' => [
            ['token_auth' => 'randomGetAccessToken', 'force_api_session' => 1],
            [],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites POST token' => [
            [],
            ['token_auth' => 'randomPostAccessToken'],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites POST session token' => [
            [],
            ['token_auth' => 'randomPostAccessToken', 'force_api_session' => 1],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET and POST token' => [
            ['token_auth' => 'randomGetAccessToken'],
            ['token_auth' => 'randomPostAccessToken'],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET session and POST token' => [
            ['token_auth' => 'randomGetAccessToken', 'force_api_session' => 1],
            ['token_auth' => 'randomPostAccessToken'],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET and POST session token' => [
            ['token_auth' => 'randomGetAccessToken'],
            ['token_auth' => 'randomPostAccessToken', 'force_api_session' => 1],
            'Bearer randomHeaderAccessToken',
            null,
            'randomHeaderAccessToken',
            true, // secure
            false, // no session token
        ];

        yield 'token in header overwrites GET session and POST session token' => [
            ['token_auth' => 'randomGetAccessToken', 'force_api_session' => 1],
            ['token_auth' => 'randomPostAccessToken', 'force_api_session' => 1],
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
}
