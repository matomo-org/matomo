<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\SupportedBrowser;
use Piwik\Exception\NotSupportedBrowserException;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class SupportedBrowserTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testNewFirefoxIsSupported()
    {
        $firefoxUserAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 11.2; rv:85.0) Gecko/20100101 Firefox/85.0";
        $_SERVER['HTTP_USER_AGENT'] = $firefoxUserAgent;

        $this->assertNull(SupportedBrowser::checkIfBrowserSupported());
    }

    public function testOldIeIsNotSupported()
    {
        $this->expectException(NotSupportedBrowserException::class);

        $ie10UserAgent = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)";
        $_SERVER['HTTP_USER_AGENT'] = $ie10UserAgent;

        SupportedBrowser::checkIfBrowserSupported();
    }

    public function testEmptyUserAgentIsSupported()
    {
        $_SERVER['HTTP_USER_AGENT'] = '';

        $this->assertNull(SupportedBrowser::checkIfBrowserSupported());
    }
}
