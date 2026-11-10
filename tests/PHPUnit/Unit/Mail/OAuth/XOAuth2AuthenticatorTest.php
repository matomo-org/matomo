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
use Piwik\Mail\OAuth\XOAuth2Authenticator;

/**
 * @group Core
 * @group Mail
 */
class XOAuth2AuthenticatorTest extends TestCase
{
    public function testGetOauth64ReturnsEncodedXOAuth2Credentials(): void
    {
        $authenticator = new XOAuth2Authenticator('reports@example.test', 'the-access-token');

        $this->assertSame(
            "user=reports@example.test\001auth=Bearer the-access-token\001\001",
            base64_decode($authenticator->getOauth64(), true)
        );
    }
}
