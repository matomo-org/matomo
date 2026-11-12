<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Unit\Mail\OAuth;

use PHPUnit\Framework\TestCase;
use Piwik\Mail\OAuth\ClientAssertion;
use Piwik\Mail\OAuth\TokenRequestFailedException;

/**
 * @group Core
 * @group Mail
 */
class ClientAssertionTest extends TestCase
{
    private const CLIENT_ID = 'the-client-id';
    private const TOKEN_URL = 'https://login.example.test/token';
    private const NOW = 1700000000;

    /**
     * @var CertificateFiles[]
     */
    private array $certificates = [];

    protected function tearDown(): void
    {
        foreach ($this->certificates as $certificate) {
            $certificate->delete();
        }

        parent::tearDown();
    }

    public function testCreatesAnAssertionSignedWithThePrivateKey(): void
    {
        $certificate = $this->getCertificate();

        $assertion = $this->create($certificate->certificatePath, $certificate->privateKeyPath);
        [$header, $claims, $signature] = explode('.', $assertion);

        $expectedThumbprint = openssl_x509_fingerprint($certificate->certificatePem, 'sha256', true);
        $this->assertSame(
            ['alg' => 'RS256', 'typ' => 'JWT', 'x5t#S256' => $this->base64UrlEncode($expectedThumbprint)],
            $this->decode($header)
        );

        $decodedClaims = $this->decode($claims);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $decodedClaims['jti']);
        unset($decodedClaims['jti']);
        $this->assertSame([
            'aud' => self::TOKEN_URL,
            'iss' => self::CLIENT_ID,
            'sub' => self::CLIENT_ID,
            'nbf' => self::NOW,
            'iat' => self::NOW,
            'exp' => self::NOW + 300,
        ], $decodedClaims);

        $this->assertSame(1, openssl_verify(
            $header . '.' . $claims,
            $this->base64UrlDecode($signature),
            $certificate->certificatePem,
            OPENSSL_ALGO_SHA256
        ));
    }

    public function testUsesANewIdForEachAssertion(): void
    {
        $certificate = $this->getCertificate();

        $first = $this->decode(explode('.', $this->create($certificate->certificatePath, $certificate->privateKeyPath))[1]);
        $second = $this->decode(explode('.', $this->create($certificate->certificatePath, $certificate->privateKeyPath))[1]);

        $this->assertNotSame($first['jti'], $second['jti']);
    }

    public function testReadsThePrivateKeyFromTheCertificateFileWhenNoKeyFileIsSet(): void
    {
        $certificate = $this->getCertificate();

        $assertion = $this->create($certificate->writeCombinedFile(), '');

        $this->assertCount(3, explode('.', $assertion));
    }

    public function testReadsAnEncryptedPrivateKeyWithItsPassword(): void
    {
        $certificate = $this->getCertificate('the-key-password');

        $assertion = $this->create($certificate->certificatePath, $certificate->privateKeyPath, 'the-key-password');
        $this->assertCount(3, explode('.', $assertion));

        $this->assertFailsWith('Could not read the private key', function () use ($certificate) {
            $this->create($certificate->certificatePath, $certificate->privateKeyPath, 'a-wrong-password');
        });
    }

    public function testFailsWhenTheKeyDoesNotBelongToTheCertificate(): void
    {
        $this->assertFailsWith('The private key does not belong to the certificate.', function () {
            $this->create($this->getCertificate()->certificatePath, $this->getCertificate()->privateKeyPath);
        });
    }

    public function testFailsWhenAFileCannotBeRead(): void
    {
        $this->assertFailsWith('Could not read a PEM encoded certificate from /does/not/exist.pem.', function () {
            $this->create('/does/not/exist.pem', '');
        });
    }

    public function testKeepsTheReasonAndTheKeyOutOfTheMessage(): void
    {
        $certificate = $this->getCertificate();
        $brokenKeyPath = $certificate->write("-----BEGIN PRIVATE KEY-----\nbm90IGEga2V5\n-----END PRIVATE KEY-----\n");

        $exception = $this->assertFailsWith('Could not read the private key.', function () use ($certificate, $brokenKeyPath) {
            $this->create($certificate->certificatePath, $brokenKeyPath);
        });
        $this->assertStringNotContainsString('bm90IGEga2V5', $exception->getMessage() . $exception->getDetail());

        $this->assertFailsWith('Could not read a PEM encoded certificate from', function () use ($certificate) {
            $this->create($certificate->write('not a certificate'), $certificate->privateKeyPath);
        });
    }

    private function create(string $certificatePath, string $privateKeyPath, string $privateKeyPassword = ''): string
    {
        return ClientAssertion::create(
            self::CLIENT_ID,
            self::TOKEN_URL,
            $certificatePath,
            $privateKeyPath,
            $privateKeyPassword,
            self::NOW
        );
    }

    private function assertFailsWith(string $detail, callable $create): TokenRequestFailedException
    {
        try {
            $create();
        } catch (TokenRequestFailedException $e) {
            $this->assertSame('The SMTP OAuth certificate or private key could not be used.', $e->getMessage());
            $this->assertStringStartsWith($detail, $e->getDetail());

            return $e;
        }

        $this->fail('Expected exception was not thrown');
    }

    private function getCertificate(string $keyPassword = ''): CertificateFiles
    {
        $certificate = new CertificateFiles($keyPassword);
        $this->certificates[] = $certificate;

        return $certificate;
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $part): array
    {
        return json_decode($this->base64UrlDecode($part), true);
    }

    private function base64UrlDecode(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/'));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
