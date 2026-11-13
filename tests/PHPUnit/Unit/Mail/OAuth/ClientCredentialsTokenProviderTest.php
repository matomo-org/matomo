<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Unit\Mail\OAuth;

use Matomo\Cache\Backend\ArrayCache;
use Matomo\Cache\Backend\Chained;
use Matomo\Cache\Lazy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Piwik\Date;
use Piwik\Mail\OAuth\ClientCredentialsTokenProvider;
use Piwik\Mail\OAuth\TokenRequestFailedException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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

    private const NOW = 1700000000;

    private Lazy&MockObject $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createMock(Lazy::class);
        $this->cache->method('fetch')->willReturn(false);

        Date::$now = self::NOW;
    }

    protected function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
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

        $withoutScope = $this->getProvider(['status' => 200, 'data' => '{"access_token":"the-token"}'], ['oauth_scope' => '']);
        $withoutScope->getAccessToken();
        $this->assertArrayNotHasKey('scope', $withoutScope->requests[0][1]);
    }

    public function testRequestsATokenWithAnAssertionSignedWithTheCertificate(): void
    {
        $certificate = new CertificateFiles();

        try {
            $provider = $this->getProvider(['status' => 200, 'data' => '{"access_token":"the-token"}'], [
                'oauth_credential_type'  => ' Certificate ',
                'oauth_certificate_path' => $certificate->certificatePath,
                'oauth_private_key_path' => $certificate->privateKeyPath,
            ]);

            $this->assertSame('the-token', $provider->getAccessToken());
        } finally {
            $certificate->delete();
        }

        $requestBody = $provider->requests[0][1];
        $this->assertArrayNotHasKey('client_secret', $requestBody);
        $this->assertSame('urn:ietf:params:oauth:client-assertion-type:jwt-bearer', $requestBody['client_assertion_type']);
        $this->assertCount(3, explode('.', $requestBody['client_assertion']));
        $this->assertSame(
            ['grant_type' => 'client_credentials', 'client_id' => 'the-client-id', 'scope' => 'https://mail.example.test/.default'],
            array_diff_key($requestBody, ['client_assertion_type' => 1, 'client_assertion' => 1])
        );
    }

    public function testRemembersThatTheCertificateCouldNotBeUsed(): void
    {
        $cache = new Lazy(new ArrayCache());
        $settings = ['oauth_credential_type' => 'certificate', 'oauth_certificate_path' => '/does/not/exist.pem'];
        $provider = new FakeHttpClientCredentialsTokenProvider($cache, $settings + self::MAIL_CONFIG, ['status' => 200]);

        foreach ([1, 2] as $attempt) {
            try {
                $provider->getAccessToken();
                $this->fail('Expected exception was not thrown');
            } catch (TokenRequestFailedException $e) {
                $this->assertSame('The SMTP OAuth certificate or private key could not be used.', $e->getMessage());
                $this->assertStringContainsString('/does/not/exist.pem', $e->getDetail());
            }
        }

        $this->assertSame([], $provider->requests);
    }

    public function testCachesTheTokenUntilShortlyBeforeItExpires(): void
    {
        $this->cache->expects($this->once())
            ->method('save')
            ->with(
                $this->stringStartsWith('MailOAuthAccessToken_'),
                ['token' => 'the-token', 'expires' => self::NOW + 3540],
                3540
            );

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

        $certificate = new CertificateFiles();
        try {
            $this->getProvider($response, [
                'oauth_credential_type'  => 'certificate',
                'oauth_certificate_path' => $certificate->certificatePath,
                'oauth_private_key_path' => $certificate->privateKeyPath,
            ])->getAccessToken();
        } finally {
            $certificate->delete();
        }

        $this->assertCount(4, array_unique($cacheIds));
        $this->assertStringNotContainsString('secret', implode('', $cacheIds));
    }

    public function testUsesTheCachedTokenUntilItIsForgotten(): void
    {
        $fetchedId = null;
        $cache = $this->createMock(Lazy::class);
        $cache->method('fetch')->willReturnCallback(function ($id) use (&$fetchedId) {
            $fetchedId = $id;

            return ['token' => 'cached-token', 'expires' => self::NOW + 10];
        });
        $deletedIds = [];
        $cache->method('delete')->willReturnCallback(function ($id) use (&$deletedIds) {
            $deletedIds[] = $id;

            return true;
        });

        $provider = new FakeHttpClientCredentialsTokenProvider($cache, self::MAIL_CONFIG, ['status' => 200]);

        $this->assertSame('cached-token', $provider->getAccessToken());
        $this->assertSame([], $provider->requests);

        $provider->forgetAccessToken();

        $this->assertSame([$fetchedId, $fetchedId . '_failed'], $deletedIds);
    }

    public function testRequestsANewTokenOnceTheCachedOneExpiredEvenIfTheCacheStillHasIt(): void
    {
        // the array backend, which is part of the default chained cache, never removes an entry
        $cache = new Lazy(new Chained([new ArrayCache()]));

        $response = ['status' => 200, 'data' => '{"access_token":"the-token","expires_in":3600}'];
        $provider = new FakeHttpClientCredentialsTokenProvider($cache, self::MAIL_CONFIG, $response);

        $provider->getAccessToken();
        Date::$now = self::NOW + 3539;
        $provider->getAccessToken();
        $this->assertCount(1, $provider->requests);

        Date::$now = self::NOW + 3540;
        $provider->getAccessToken();
        $this->assertCount(2, $provider->requests);
    }

    public function testDoesNotRepeatAFailedRequestForAShortTime(): void
    {
        $cache = new Lazy(new ArrayCache());

        $response = ['status' => 401, 'data' => '{"error":"invalid_client"}'];
        $provider = new FakeHttpClientCredentialsTokenProvider($cache, self::MAIL_CONFIG, $response);

        foreach ([1, 2] as $attempt) {
            try {
                $provider->getAccessToken();
                $this->fail('Expected exception was not thrown');
            } catch (TokenRequestFailedException $e) {
                $this->assertSame('The SMTP OAuth token request failed with HTTP status 401.', $e->getMessage());
                $this->assertSame('invalid_client', $e->getDetail());
            }
        }

        $this->assertCount(1, $provider->requests);

        Date::$now = self::NOW + 60;

        try {
            $provider->getAccessToken();
            $this->fail('Expected exception was not thrown');
        } catch (TokenRequestFailedException $e) {
            $this->assertCount(2, $provider->requests);
        }
    }

    public function testLogsTheReasonOfAFailedRequestOnce(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with(
            '{message} Reason: {detail}',
            ['message' => 'The SMTP OAuth token request failed with HTTP status 401.', 'detail' => 'invalid_client']
        );

        $response = ['status' => 401, 'data' => '{"error":"invalid_client"}'];
        $provider = new FakeHttpClientCredentialsTokenProvider(new Lazy(new ArrayCache()), self::MAIL_CONFIG, $response, $logger);

        foreach ([1, 2] as $attempt) {
            try {
                $provider->getAccessToken();
                $this->fail('Expected exception was not thrown');
            } catch (TokenRequestFailedException $e) {
                // the second attempt is answered from the remembered failure, which was already logged
            }
        }
    }

    /**
     * @dataProvider getFailedResponses
     * @param array{status: ?int, data?: ?string}|\Exception $response
     */
    public function testKeepsTheReasonGivenByTheProviderOutOfTheMessage($response, string $message, string $detail): void
    {
        try {
            $this->getProvider($response)->getAccessToken();
            $this->fail('Expected exception was not thrown');
        } catch (TokenRequestFailedException $e) {
            $this->assertSame($message, $e->getMessage());
            $this->assertSame($detail, $e->getDetail());
        }
    }

    public function getFailedResponses(): iterable
    {
        yield 'with a reason' => [
            ['status' => 401, 'data' => '{"error":"invalid_client","error_description":"Invalid client secret provided.\r\nTrace ID: 1"}'],
            'The SMTP OAuth token request failed with HTTP status 401.',
            'invalid_client: Invalid client secret provided. Trace ID: 1',
        ];
        yield 'with control characters in the reason' => [
            ['status' => 401, 'data' => '{"error":"invalid\u001b[31m_client\u0007"}'],
            'The SMTP OAuth token request failed with HTTP status 401.',
            'invalid [31m_client',
        ];
        yield 'no response body' => [['status' => 400], 'The SMTP OAuth token request failed with HTTP status 400.', ''];
        yield 'endpoint not reached' => [
            new \Exception('Unable to open https://login.example.test/token'),
            'The SMTP OAuth token endpoint could not be reached.',
            'Unable to open https://login.example.test/token',
        ];
    }

    /**
     * @dataProvider getResponsesWithoutAnAccessToken
     */
    public function testFailsWhenTheResponseHasNoAccessToken(string $responseBody): void
    {
        $this->cache->expects($this->once())->method('save')->with($this->stringEndsWith('_failed'));

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
        yield 'no client secret' => [['oauth_client_secret' => ''], 'oauth_token_url, oauth_client_id and oauth_client_secret'];
        yield 'no certificate' => [
            ['oauth_credential_type' => 'certificate', 'oauth_client_secret' => 'the-client-secret'],
            'oauth_token_url, oauth_client_id and oauth_certificate_path',
        ];
        yield 'unknown credential type' => [['oauth_credential_type' => 'password'], 'has to be either "secret" or "certificate"'];
        yield 'token URL is not https' => [['oauth_token_url' => 'http://login.example.test/token'], 'has to start with https://'];
    }

    /**
     * @param array{status: ?int, data?: ?string}|\Exception $response
     */
    private function getProvider($response, array $settings = []): FakeHttpClientCredentialsTokenProvider
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
     * @var array{status: ?int, data?: ?string}|\Exception
     */
    private $response;

    /**
     * @param array{status: ?int, data?: ?string}|\Exception $response
     */
    public function __construct(Lazy $cache, array $mailConfig, $response, ?LoggerInterface $logger = null)
    {
        parent::__construct($cache, $logger ?? new NullLogger());

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

        if ($this->response instanceof \Exception) {
            throw $this->response;
        }

        return $this->response;
    }
}
