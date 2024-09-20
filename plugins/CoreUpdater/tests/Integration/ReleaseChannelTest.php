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
        $url = urlencode(Url::getCurrentUrlWithoutQueryString());

        $urlToCheck = $this->channel->getUrlToCheckForLatestAvailableVersion();

        $this->assertStringStartsWith("https://api.matomo.org/1.0/getLatestVersion/?piwik_version=$version&php_version=$phpVersion&mysql_version=$mysqlVersion&release_channel=my_channel&url=$url&trigger=&timezone=", $urlToCheck);
    }

    public function testDoesPreferStable()
    {
        $this->assertTrue($this->channel->doesPreferStable());
    }
}
