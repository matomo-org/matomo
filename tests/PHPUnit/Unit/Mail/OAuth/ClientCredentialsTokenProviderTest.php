<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Unit\Mail\OAuth;

use Matomo\Cache\Lazy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Piwik\Mail\OAuth\ClientCredentialsTokenProvider;
use Piwik\Mail\OAuth\TokenRequestFailedException;

/**
 * @group Core
 * @group Mail
 */
class ClientCredentialsTokenProviderTest extends TestCase
{
    private const MAIL_CONFIG = [
        'oauth_token_url'     => 'https://login.example.test/token',
        'oauth_client_id'     => 'the-client-id',
        'oauth_client_secret' => 'the-client-secret',
        'oauth_scope'         => 'https://mail.example.test/.default',
    ];

    /**
     * @var Lazy&MockObject
     */
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(Lazy::class);
        $this->cache->method('fetch')->willReturn(false);
    }

    public function testRequestsATokenWithTheClientCredentials(): void
    {
        $provider = $this->getProvider(['status' => 200, 'data' => '{"access_token":"the-token","expires_in":3600}']);

        $this->assertSame('the-token', $provider->getAccessToken());
        $this->assertSame([[
            'https://login.example.test/token',
            [
                'grant_type'    => 'client_credentials',
                'client_id'     => 'the-client-id',
                'client_secret' => 'the-client-secret',
                'scope'         => 'https://mail.example.test/.default',
            ],
        ]], $provider->requests);
    }

    public function testLeavesOutAnEmptyScope(): void
    {
        $provider = $this->getProvider(
            ['status' => 200, 'data' => '{"access_token":"the-token"}'],
            ['oauth_scope' => '']
        );

        $provider->getAccessToken();

        $this->assertArrayNotHasKey('scope', $provider->requests[0][1]);
    }

    public function testCachesTheTokenUntilShortlyBeforeItExpires(): void
    {
        $this->cache->expects($this->once())
            ->method('save')
            ->with($this->stringStartsWith('MailOAuthAccessToken_'), 'the-token', 3540);

        $this->getProvider(['status' => 200, 'data' => '{"access_token":"the-token","expires_in":3600}'])->getAccessToken();
    }

    /**
     * @dataProvider getResponsesThatShouldNotBeCached
     */
    public function testDoesNotCacheATokenWithoutAUsableLifetime(string $responseBody): void
    {
        $this->cache->expects($this->never())->method('save');

        $this->assertSame('the-token', $this->getProvider(['status' => 200, 'data' => $responseBody])->getAccessToken());
    }

    public function getResponsesThatShouldNotBeCached(): iterable
    {
        yield 'no lifetime' => ['{"access_token":"the-token"}'];
        yield 'lifetime shorter than the margin' => ['{"access_token":"the-token","expires_in":30}'];
    }

    public function testUsesTheCachedTokenWithoutARequest(): void
    {
        $cache = $this->createMock(Lazy::class);
        $cache->method('fetch')->willReturn('the-cached-token');
        $cache->expects($this->never())->method('save');

        $provider = new FakeHttpClientCredentialsTokenProvider($cache, self::MAIL_CONFIG, []);

        $this->assertSame('the-cached-token', $provider->getAccessToken());
        $this->assertSame([], $provider->requests);
    }

    public function testUsesADifferentCacheEntryWhenASettingChanges(): void
    {
        $cacheIds = [];
        $this->cache->method('save')->willReturnCallback(function ($id) use (&$cacheIds) {
            $cacheIds[] = $id;
            return true;
        });

        $response = ['status' => 200, 'data' => '{"access_token":"the-token","expires_in":3600}'];
        $this->getProvider($response)->getAccessToken();
        $this->getProvider($response, ['oauth_client_secret' => 'another-secret'])->getAccessToken();
        $this->getProvider($response, ['oauth_scope' => 'another-scope'])->getAccessToken();

        $this->assertCount(3, array_unique($cacheIds));
        $this->assertStringNotContainsString('secret', implode('', $cacheIds));
    }

    public function testFailsWithTheReasonGivenByTheProvider(): void
    {
        $provider = $this->getProvider([
            'status' => 401,
            'data'   => '{"error":"invalid_client","error_description":"Invalid client secret provided.\r\nTrace ID: 1"}',
        ]);

        try {
            $provider->getAccessToken();
            $this->fail('Expected exception was not thrown');
        } catch (TokenRequestFailedException $e) {
            $this->assertSame(
                'The SMTP OAuth token request failed with HTTP status 401'
                . ' (invalid_client: Invalid client secret provided. Trace ID: 1).',
                $e->getMessage()
            );
        }
    }

    public function testFailsWithTheStatusAloneWhenThereIsNoResponseBody(): void
    {
        $this->expectException(TokenRequestFailedException::class);
        $this->expectExceptionMessage('The SMTP OAuth token request failed with HTTP status 400.');

        $this->getProvider(['status' => 400])->getAccessToken();
    }

    /**
     * @dataProvider getResponsesWithoutAnAccessToken
     */
    public function testFailsWhenTheResponseHasNoAccessToken(string $responseBody): void
    {
        $this->cache->expects($this->never())->method('save');

        $this->expectException(TokenRequestFailedException::class);
        $this->expectExceptionMessage('The SMTP OAuth token response did not contain an access token.');

        $this->getProvider(['status' => 200, 'data' => $responseBody])->getAccessToken();
    }

    public function getResponsesWithoutAnAccessToken(): iterable
    {
        yield 'not JSON' => ['<html>Sign in</html>'];
        yield 'no token' => ['{"token_type":"Bearer"}'];
        yield 'empty token' => ['{"access_token":""}'];
        yield 'token is not a string' => ['{"access_token":["the-token"]}'];
    }

    /**
     * @dataProvider getIncompleteSettings
     */
    public function testFailsWithoutARequestWhenTheSettingsAreIncomplete(array $settings, string $expectedMessage): void
    {
        $provider = $this->getProvider(['status' => 200, 'data' => '{"access_token":"the-token"}'], $settings);

        try {
            $provider->getAccessToken();
            $this->fail('Expected exception was not thrown');
        } catch (TokenRequestFailedException $e) {
            $this->assertStringContainsString($expectedMessage, $e->getMessage());
            $this->assertSame([], $provider->requests);
        }
    }

    public function getIncompleteSettings(): iterable
    {
        yield 'no token URL' => [['oauth_token_url' => ''], 'settings are incomplete'];
        yield 'no client ID' => [['oauth_client_id' => ''], 'settings are incomplete'];
        yield 'no client secret' => [['oauth_client_secret' => ''], 'settings are incomplete'];
        yield 'token URL is not https' => [['oauth_token_url' => 'http://login.example.test/token'], 'has to start with https://'];
    }

    private function getProvider(array $response, array $settings = []): FakeHttpClientCredentialsTokenProvider
    {
        return new FakeHttpClientCredentialsTokenProvider($this->cache, $settings + self::MAIL_CONFIG, $response);
    }
}

class FakeHttpClientCredentialsTokenProvider extends ClientCredentialsTokenProvider
{
    /**
     * @var array<int, array{0: string, 1: array<string, string>}>
     */
    public $requests = [];

    /**
     * @var array<string, string>
     */
    private $mailConfig;

    /**
     * @var array{status: ?int, data?: ?string}
     */
    private $response;

    public function __construct(Lazy $cache, array $mailConfig, array $response)
    {
        parent::__construct($cache);

        $this->mailConfig = $mailConfig;
        $this->response = $response;
    }

    protected function getMailConfig(): array
    {
        return $this->mailConfig;
    }

    protected function sendTokenRequest(string $tokenUrl, array $requestBody): array
    {
        $this->requests[] = [$tokenUrl, $requestBody];

        return $this->response;
    }
}
