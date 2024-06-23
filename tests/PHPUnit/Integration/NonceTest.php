<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Config;
use Piwik\Nonce;
use Piwik\Session\SessionNamespace;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group NonceTest
 */
class NonceTest extends IntegrationTestCase
{
    protected $preTestServerHttpReferrer;

    public function setUp(): void
    {
        parent::setUp();

        $ns        = new SessionNamespace(1);
        $ns->nonce = 'abc';

        $this->preTestServerHttpReferrer = $_SERVER['HTTP_REFERER'];
    }

    public function tearDown(): void
    {
        $this->setReferrer($this->preTestServerHttpReferrer);
        parent::tearDown();
    }

    protected function setReferrer(string $referrer): void
    {
        $_SERVER['HTTP_REFERER'] = $referrer;
    }

    public function testVerifyNonceWithErrorMessageInvalidNonceExpectErrorString()
    {
        $this->assertSame(
            'Login_InvalidNonceToken',
            Nonce::verifyNonceWithErrorMessage(1, 'abcd')
        );
    }

    public function testVerifyNonceWithErrorMessageValidNonceAndAllowedReferrerWithNoReferrerExpectEmptyString()
    {
        $this->assertSame(
            '',
            Nonce::verifyNonceWithErrorMessage(1, 'abc', 'example.com')
        );
    }

    public function testVerifyNonceWithErrorMessageValidNonceAndAllowedReferrerWithMatchingReferrerExpectEmptyString()
    {
        $this->setReferrer('https://example.com');
        $this->assertSame(
            '',
            Nonce::verifyNonceWithErrorMessage(1, 'abc', 'example.com')
        );
    }

    public function testVerifyNonceWithErrorMessageValidNonceAndNoAllowedReferrerWithReferrerExpectErrorString()
    {
        $this->setReferrer('https://example.net');
        $this->assertSame(
            'Login_InvalidNonceReferrer',
            Nonce::verifyNonceWithErrorMessage(1, 'abc')
        );
    }

    public function testVerifyNonceWithErrorMessageValidNonceAndLocalReferrerWithNoAllowedReferrerExpectEmptyString()
    {
        $this->setReferrer('http://' . Config::getHostname()); // The "local" host when running via CLI.
        $this->assertSame(
            '',
            Nonce::verifyNonceWithErrorMessage(1, 'abc')
        );
    }

    public function testVerifyNonceWithErrorMessageValidNonceAndAllowedReferrerWithMismatchedReferrerExpectError()
    {
        $this->setReferrer('https://example.net');
        $this->assertSame(
            'Login_InvalidNonceUnexpectedReferrer',
            Nonce::verifyNonceWithErrorMessage(1, 'abc', 'example.com')
        );
    }
}
