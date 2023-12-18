<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Api;

use Piwik\Plugin;
use Piwik\Plugin\ReleaseChannels;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

/**
 * @group Plugins
 * @group Marketplace
 * @group EnvironmentTest
 * @group Environment
 */
class EnvironmentTest extends IntegrationTestCase
{
    /**
     * @var Environment
     */
    private $environment;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-01-01 02:02:02');
        Fixture::createWebsite('2014-01-01 02:02:02');
        Fixture::createWebsite('2014-01-01 02:02:02');

        $releaseChannes = new ReleaseChannels(Plugin\Manager::getInstance());
        $releaseChannes->setActiveReleaseChannelId('latest_stable');

        $this->environment = new Environment($releaseChannes);
    }

    public function test_getPhpVersion()
    {
        $phpVersion = explode('-', phpversion()); // cater for pre-release versions like 8.3.0-dev
        $this->assertTrue(version_compare($phpVersion[0], $this->environment->getPhpVersion(), '>='));
    }

    public function test_getPiwikVersion()
    {
        $this->assertEquals(Version::VERSION, $this->environment->getPiwikVersion());
    }

    public function test_setPiwikVersion_OverwritesCurrentPiwikVersion()
    {
        $this->environment->setPiwikVersion('1.12.0');
        $this->assertSame('1.12.0', $this->environment->getPiwikVersion());
    }

    public function test_getNumUsers()
    {
        $this->assertSame(1, $this->environment->getNumUsers());
    }

    public function test_getNumWebsites()
    {
        $this->assertSame(3, $this->environment->getNumWebsites());
    }

    public function test_getMySQLVersion()
    {
        $this->assertNotEmpty($this->environment->getMySQLVersion());
    }

    public function test_getReleaseChannel()
    {
        $this->assertEquals('latest_stable', $this->environment->getReleaseChannel());
    }

    public function test_doesPreferStable()
    {
        $this->assertTrue($this->environment->doesPreferStable());
    }

}
