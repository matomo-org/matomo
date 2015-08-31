<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Test\ReleaseChannel;

use Piwik\Config;
use Piwik\Plugins\CoreUpdater\ReleaseChannel;
use Piwik\UpdateCheck;
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

    public function setUp()
    {
        parent::setUp();

        $this->channel = new MyReleaseChannel();
    }

    public function test_getDownloadUrlWithoutScheme_shouldReturnUrlWithVersionNumberButWithoutScheme()
    {
        $this->assertSame('://builds.piwik.org/piwik-2.15.0-b5.zip', $this->channel->getDownloadUrlWithoutScheme('2.15.0-b5'));
    }

    public function test_getUrlToCheckForLatestAvailableVersion()
    {
        $version = Version::VERSION;
        $phpVersion = urlencode(PHP_VERSION);
        $url = urlencode(Url::getCurrentUrlWithoutQueryString());

        $urlToCheck = $this->channel->getUrlToCheckForLatestAvailableVersion();

        $this->assertStringStartsWith("http://api.piwik.org/1.0/getLatestVersion/?piwik_version=$version&php_version=$phpVersion&release_channel=my_channel&url=$url&trigger=&timezone=", $urlToCheck);
    }

}
