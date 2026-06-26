<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\ReleaseChannel;

use Piwik\Db;
use Piwik\Plugins\CoreUpdater\ReleaseChannel;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Url;
use Piwik\Version;

class MyReleaseChannel extends ReleaseChannel
{
    public function getId()
    {
        return 'my_channel';
    }

    public function getName()
    {
        return 'My Special Channel';
    }
}

/**
 * @group Plugins
 * @group ReleaseChannel
 * @group ReleaseChannelTest
 */
class ReleaseChannelTest extends IntegrationTestCase
{
    /**
     * @var MyReleaseChannel
     */
    private $channel;

    public function setUp(): void
    {
        parent::setUp();

        $this->channel = new MyReleaseChannel();
    }

    public function testGetDownloadUrlWithoutSchemeShouldReturnUrlWithVersionNumberButWithoutScheme()
    {
        $this->assertSame('://builds.matomo.org/matomo-2.15.0-b5.zip', $this->channel->getDownloadUrlWithoutScheme('2.15.0-b5'));
    }

    public function testGetUrlToCheckForLatestAvailableVersion()
    {
        $version = Version::VERSION;
        $phpVersion = urlencode(PHP_VERSION);
        $mysqlVersion = Db::get()->getServerVersion();
        $anonymisedUrl = ReleaseChannel::anonymiseUrl(Url::getCurrentUrlWithoutQueryString());

        $urlToCheck = $this->channel->getUrlToCheckForLatestAvailableVersion();

        $this->assertSame(
            "https://api.matomo.org/1.0/getLatestVersion/?piwik_version=$version&php_version=$phpVersion&mysql_version=$mysqlVersion&release_channel=my_channel&url=$anonymisedUrl",
            $urlToCheck
        );
        $this->assertStringNotContainsString('trigger=', $urlToCheck);
        $this->assertStringNotContainsString('timezone=', $urlToCheck);
    }

    public function testDoesPreferStable()
    {
        $this->assertTrue($this->channel->doesPreferStable());
    }

    /**
     * @dataProvider provideAnonymiseUrlCases
     */
    public function testAnonymiseUrl(string $input, string $expected)
    {
        $actual = ReleaseChannel::anonymiseUrl($input);

        if ($expected === 'HASH') {
            $this->assertRegExp('/^[a-f0-9]{64}$/', $actual);
            return;
        }

        $this->assertSame($expected, $actual);
    }

    public function provideAnonymiseUrlCases(): iterable
    {
        yield 'empty input' => ['', ''];
        yield 'malformed' => ['not a url', ''];
        yield 'no host' => ['/relative/path', ''];
        yield 'example.org excluded' => ['https://example.org/index.php', ''];
        yield 'localhost (no dot) excluded' => ['http://localhost/index.php', ''];
        yield 'private IPv4 excluded' => ['http://192.168.1.1/index.php', ''];
        yield 'reserved IPv4 (loopback) excluded' => ['http://127.0.0.1/index.php', ''];
        yield 'link-local IPv6 excluded' => ['http://[fe80::1]/', ''];
        yield 'public hostname produces hash' => ['https://stats.acme.com/matomo/', 'HASH'];
        yield 'public IPv4 produces hash' => ['http://8.8.8.8/index.php', 'HASH'];
        yield 'public IPv6 produces hash' => ['http://[2001:4860:4860::8888]/', 'HASH'];
    }

    public function testAnonymiseUrlIsStable()
    {
        $url = 'https://stats.acme.com/matomo/index.php';
        $this->assertSame(ReleaseChannel::anonymiseUrl($url), ReleaseChannel::anonymiseUrl($url));
    }

    public function testAnonymiseUrlIsCaseInsensitiveOnHost()
    {
        $this->assertSame(
            ReleaseChannel::anonymiseUrl('https://stats.acme.com/matomo/index.php'),
            ReleaseChannel::anonymiseUrl('HTTPS://Stats.Acme.COM/matomo/index.php')
        );
    }

    public function testAnonymiseUrlIgnoresSchemeAndPath()
    {
        // Two installs at the same host but different scheme/path collapse to the
        // same hash, matching the historical "one host = one install" API semantics.
        $reference = ReleaseChannel::anonymiseUrl('https://stats.acme.com/matomo/');
        $this->assertSame($reference, ReleaseChannel::anonymiseUrl('http://stats.acme.com/'));
        $this->assertSame($reference, ReleaseChannel::anonymiseUrl('https://stats.acme.com/analytics/index.php'));
    }

    public function testAnonymiseUrlHashMatchesHostHash()
    {
        $this->assertSame(
            hash('sha256', 'stats.acme.com'),
            ReleaseChannel::anonymiseUrl('https://stats.acme.com/matomo/index.php')
        );
    }

    public function testAnonymiseUrlStripsIpv6BracketsBeforeHashing()
    {
        $this->assertSame(
            hash('sha256', '2001:4860:4860::8888'),
            ReleaseChannel::anonymiseUrl('http://[2001:4860:4860::8888]/')
        );
    }
}
