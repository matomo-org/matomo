<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\NoAccessException;
use Piwik\Plugins\CoreHome\LoginAllowlist;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CustomLoginAllowlist extends LoginAllowlist
{
    public function getAllowlistedLoginIps()
    {
        return parent::getAllowlistedLoginIps();
    }

    public function isIpAllowed($ip)
    {
        return parent::isIpAllowed($ip);
    }
}

/**
 * @group Plugins
 * @group LoginAllowlist
 * @group LoginAllowlistTest
 */
class LoginAllowlistTest extends IntegrationTestCase
{
    /**
     * @var CustomLoginAllowlist
     */
    private $allowlist;

    private $cliMode;

    public function setUp(): void
    {
        parent::setUp();

        $this->cliMode = Common::$isCliMode;
        Common::$isCliMode = false;

        $this->allowlist = new CustomLoginAllowlist();
    }

    public function tearDown(): void
    {
        Common::$isCliMode = $this->cliMode;
        parent::tearDown();
    }

    public function testShouldAllowlistApplyToAPIShouldBeEnabledByDefault()
    {
        $this->assertTrue($this->allowlist->shouldAllowlistApplyToAPI());
    }

    public function testShouldAllowlistApplyToAPICanBeDisabled()
    {
        $this->setGeneralConfig('login_allowlist_apply_to_reporting_api_requests', '0');
        $this->assertFalse($this->allowlist->shouldAllowlistApplyToAPI());
    }

    public function testShouldAllowlistApplyToAPIEnabled()
    {
        $this->setGeneralConfig('login_allowlist_apply_to_reporting_api_requests', '1');
        $this->assertTrue($this->allowlist->shouldAllowlistApplyToAPI());
    }

    public function testShouldWhitelistApplyToAPIEnabledBC()
    {
        $this->setGeneralConfig('login_whitelist_apply_to_reporting_api_requests', '1');
        $this->assertTrue($this->allowlist->shouldAllowlistApplyToAPI());
    }

    public function testShouldCheckWhitelistShouldNotBeCheckedByDefaultAndNotHaveAnyIps()
    {
        $this->assertFalse($this->allowlist->shouldCheckAllowlist());
    }

    public function testShouldCheckAllowlistShouldBeCheckedIfHasAtLeastOneIp()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1']);
        $this->assertTrue($this->allowlist->shouldCheckAllowlist());
    }

    public function testShouldCheckAllowlistShouldNotBeCheckedIfExecutedFromCLI()
    {
        Common::$isCliMode = true;
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1']);
        $this->assertFalse($this->allowlist->shouldCheckAllowlist());
    }

    public function testShouldCheckWhitelistShouldBeCheckedIfHasAtLeastOneIpForBC()
    {
        $this->setGeneralConfig('login_whitelist_ip', ['192.168.33.1']);
        $this->assertTrue($this->allowlist->shouldCheckAllowlist());
    }

    public function testShouldCheckWhitelistShouldNotBeCheckedIfExecutedFromCLIForBC()
    {
        Common::$isCliMode = true;
        $this->setGeneralConfig('login_whitelist_ip', ['192.168.33.1']);
        $this->assertFalse($this->allowlist->shouldCheckAllowlist());
    }

    public function testShouldCheckWhitelistShouldNotBeCheckedIfOnlyEmptyEntries()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['', ' ']);
        $this->assertFalse($this->allowlist->shouldCheckAllowlist());
    }

    public function testGetAllowlistedLoginIpsShouldReturnEmptyArrayByDefault()
    {
        $this->assertSame($this->allowlist->getAllowlistedLoginIps(), []);
    }

    public function testGetAllowlistedLoginIpsShouldReturnIpsAndTrimIfNeeded()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1', ' 127.0.0.1 ', '2001:0db8:85a3:0000:0000:8a2e:0370:7334']);
        $this->assertSame(['192.168.33.1', '127.0.0.1', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function testGetAllowlistedLoginIpsShouldResolveIp()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1', 'origin.matomo.org', '127.0.0.1']);
        $this->assertSame(['192.168.33.1', '185.31.40.177', '2a00:b6e0:1:200:177::1', '127.0.0.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function testGetAllowlistedLoginIpsShouldResolveIpv6Only()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1', 'integration-test.matomo.org', '127.0.0.1']);
        $this->assertSame(['192.168.33.1', '::1', '127.0.0.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function testGetAllowlistedLoginIpsShouldReturnRanges()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1', '204.93.177.0/25', '2001:db9::/48', '127.0.0.1']);
        $this->assertSame(['192.168.33.1', '204.93.177.0/25', '2001:db9::/48', '127.0.0.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function testGetAllowlistedLoginIpsShouldNotBeCheckedIfOnlyEmptyEntries()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['', '192.168.33.1 ', ' ']);
        $this->assertSame(['192.168.33.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function testGetAllowlistedLoginIpsShouldNotReturnDuplicates()
    {
        $this->setGeneralConfig('login_allowlist_ip', [' 192.168.33.1', '192.168.33.1 ', ' 192.168.33.1 ', '192.168.33.1']);
        $this->assertSame(['192.168.33.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    /**
     * @dataProvider getIpAllowlistedTests
     */
    public function testIsIpAllowlisted($expectedIsAllowlisted, $ipString)
    {
        $ipsAllowlisted = [
            '127.0.0.1',
            '192.168.33.1',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            '204.93.240.*',
            '204.93.177.0/25',
            '2001:db9::/48'
        ];
        $this->setGeneralConfig('login_allowlist_ip', $ipsAllowlisted);
        $this->assertSame($expectedIsAllowlisted, $this->allowlist->isIpAllowed($ipString));
    }

    /**
     * @dataProvider getIpAllowlistedTests
     */
    public function testIsIpAllowedWhenNoIpsConfiguredAllIpsAreAllowed($expectedIsWhitelisted, $ipString)
    {
        $this->assertFalse($this->allowlist->isIpAllowed($ipString));
    }

    /**
     * @dataProvider getIpAllowlistedTests
     */
    public function testCheckIsAllowed($expectedIsAllowed, $ipString)
    {
        $ipsAllowed = [
            '127.0.0.1',
            '192.168.33.1',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            '204.93.240.*',
            '204.93.177.0/25',
            '2001:db9::/48'
        ];
        $this->setGeneralConfig('login_allowlist_ip', $ipsAllowed);

        if ($expectedIsAllowed) {
            self::expectNotToPerformAssertions();

            $this->allowlist->checkIsAllowed($ipString);
        } else {
            self::expectException(NoAccessException::class);

            $this->allowlist->checkIsAllowed($ipString);
        }
    }

    public function getIpAllowlistedTests()
    {
        return array(
            array(true, '127.0.0.1'),
            array(true, '192.168.33.1'),
            array(true, '2001:0db8:85a3:0000:0000:8a2e:0370:7334'),
            array(true, '204.93.240.5'),
            array(true, '204.93.177.5'),
            array(true, '2001:db9:0000:ffff:ffff:ffff:ffff:ffff'),


            array(false, '127.0.0.2'),
            array(false, '192.168.33.2'),
            array(false, '2001:0db8:85a3:0000:0000:8a2e:0370:7333'),
            array(false, '204.93.239.5'),
            array(false, '204.93.177.255'),
            array(false, '2001:db8:0000:ffff:ffff:ffff:ffff:ffff'),
        );
    }

    private function setGeneralConfig($name, $value)
    {
        $config = Config::getInstance();
        $general = $config->General;
        $general[$name] = $value;
        $config->General = $general;
        $config->forceSave();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
