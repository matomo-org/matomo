<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Nonce;
use Piwik\Session\SessionNamespace;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackerTest
 * @group Tracker
 */
class NonceTest extends IntegrationTestCase
{
    protected $preTestServerHttpReferrer;

    public function setUp(): void
    {
        parent::setUp();

        $ns = new SessionNamespace(1);
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

    public function testVerifyNonceWithErrorMessage_invalidNonce_expectErrorString()
    {
        $this->assertSame(
            'Login_InvalidNonceToken',
            Nonce::verifyNonceWithErrorMessage(1, 'abcd')
        );
    }

    public function testVerifyNonceWithErrorMessage__validNonceAndExpectedReferrerWithNoReferrer_expectEmptyString()
    {
        $this->assertSame(
            '',
            Nonce::verifyNonceWithErrorMessage(1, 'abc', 'example.com')
        );
    }

    public function testVerifyNonceWithErrorMessage_validNonceAndExpectedReferrerWithMatchingReferrer_expectEmptyString()
    {
        $this->setReferrer('https://example.com');
        $this->assertSame(
            '',
            Nonce::verifyNonceWithErrorMessage(1, 'abc', 'example.com')
        );
    }

    public function testVerifyNonceWithErrorMessage_validNonceAndExpectedReferrerWithMismatchedReferrer_expectErrorString()
    {
        $this->setReferrer('https://example.net');
        $this->assertSame(
            'Login_InvalidNonceUnexpectedReferrer',
            Nonce::verifyNonceWithErrorMessage(1, 'abc', 'example.com')
        );
    }
}
