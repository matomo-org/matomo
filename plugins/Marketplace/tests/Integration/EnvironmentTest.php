<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration\Api;

use Piwik\Config;
use Piwik\Plugin;
use Piwik\Plugin\ReleaseChannels;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\SettingsPiwik;
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

        // Set a known salt before anything triggers SettingsPiwik::getSalt() (which caches
        // its value statically), so the salt-derived unique id can be exercised. Guarded to
        // the dedicated test, which runs in a separate process, to avoid poisoning the static
        // salt cache for other tests sharing this process (they expect no salt / no uid).
        if ($this->getName() === 'testGetUniqueIdReturnsSha256HashOfSaltWhenSaltConfigured') {
            Config::getInstance()->General['salt'] = 'a1b2c3d4e5f6g7h8';
        }

        Fixture::createSuperUser();
        Fixture::createWebsite('2014-01-01 02:02:02');
        Fixture::createWebsite('2014-01-01 02:02:02');
        Fixture::createWebsite('2014-01-01 02:02:02');

        $releaseChannes = new ReleaseChannels(Plugin\Manager::getInstance());
        $releaseChannes->setActiveReleaseChannelId('latest_stable');

        $this->environment = new Environment($releaseChannes);
    }

    public function testGetPhpVersion()
    {
        $phpVersion = explode('-', phpversion()); // cater for pre-release versions like 8.3.0-dev
        $this->assertTrue(version_compare($phpVersion[0], $this->environment->getPhpVersion(), '>='));
    }

    public function testGetPiwikVersion()
    {
        $this->assertEquals(Version::VERSION, $this->environment->getPiwikVersion());
    }

    public function testSetPiwikVersionOverwritesCurrentPiwikVersion()
    {
        $this->environment->setPiwikVersion('1.12.0');
        $this->assertSame('1.12.0', $this->environment->getPiwikVersion());
    }

    public function testGetNumUsers()
    {
        $this->assertSame(1, $this->environment->getNumUsers());
    }

    public function testGetNumWebsites()
    {
        $this->assertSame(3, $this->environment->getNumWebsites());
    }

    public function testGetMySQLVersion()
    {
        $this->assertNotEmpty($this->environment->getMySQLVersion());
    }

    public function testGetReleaseChannel()
    {
        $this->assertEquals('latest_stable', $this->environment->getReleaseChannel());
    }

    public function testDoesPreferStable()
    {
        $this->assertTrue($this->environment->doesPreferStable());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetUniqueIdReturnsSha256HashOfSaltWhenSaltConfigured()
    {
        $salt = SettingsPiwik::getSalt();
        $this->assertNotEmpty($salt, 'salt should be readable so the active path is exercised');

        $this->assertSame(hash('sha256', $salt), $this->environment->getUniqueId());
    }
}
