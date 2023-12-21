<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function test_shouldAllowlistApplyToAPI_shouldBeEnabledByDefault()
    {
        $this->assertTrue($this->allowlist->shouldAllowlistApplyToAPI());
    }

    public function test_shouldAllowlistApplyToAPI_canBeDisabled()
    {
        $this->setGeneralConfig('login_allowlist_apply_to_reporting_api_requests', '0');
        $this->assertFalse($this->allowlist->shouldAllowlistApplyToAPI());
    }

    public function test_shouldAllowlistApplyToAPI_enabled()
    {
        $this->setGeneralConfig('login_allowlist_apply_to_reporting_api_requests', '1');
        $this->assertTrue($this->allowlist->shouldAllowlistApplyToAPI());
    }

    public function test_shouldWhitelistApplyToAPI_enabledBC()
    {
        $this->setGeneralConfig('login_whitelist_apply_to_reporting_api_requests', '1');
        $this->assertTrue($this->allowlist->shouldAllowlistApplyToAPI());
    }

    public function test_shouldCheckWhitelist_shouldNotBeCheckedByDefaultAndNotHaveAnyIps()
    {
        $this->assertFalse($this->allowlist->shouldCheckAllowlist());
    }

    public function test_shouldCheckAllowlist_shouldBeCheckedIfHasAtLeastOneIp()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1']);
        $this->assertTrue($this->allowlist->shouldCheckAllowlist());
    }

    public function test_shouldCheckAllowlist_shouldNotBeCheckedIfExecutedFromCLI()
    {
        Common::$isCliMode = true;
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1']);
        $this->assertFalse($this->allowlist->shouldCheckAllowlist());
    }

    public function test_shouldCheckWhitelist_shouldBeCheckedIfHasAtLeastOneIp_forBC()
    {
        $this->setGeneralConfig('login_whitelist_ip', ['192.168.33.1']);
        $this->assertTrue($this->allowlist->shouldCheckAllowlist());
    }

    public function test_shouldCheckWhitelist_shouldNotBeCheckedIfExecutedFromCLI_forBC()
    {
        Common::$isCliMode = true;
        $this->setGeneralConfig('login_whitelist_ip', ['192.168.33.1']);
        $this->assertFalse($this->allowlist->shouldCheckAllowlist());
    }

    public function test_shouldCheckWhitelist_shouldNotBeCheckedIfOnlyEmptyEntries()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['', ' ']);
        $this->assertFalse($this->allowlist->shouldCheckAllowlist());
    }

    public function test_getAllowlistedLoginIps_shouldReturnEmptyArrayByDefault()
    {
        $this->assertSame($this->allowlist->getAllowlistedLoginIps(), []);
    }

    public function test_getAllowlistedLoginIps_shouldReturnIpsAndTrimIfNeeded()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1', ' 127.0.0.1 ', '2001:0db8:85a3:0000:0000:8a2e:0370:7334']);
        $this->assertSame(['192.168.33.1', '127.0.0.1', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function test_getAllowlistedLoginIps_shouldResolveIp()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1', 'origin.matomo.org', '127.0.0.1']);
        $this->assertSame(['192.168.33.1', '185.31.40.177', '2a00:b6e0:1:200:177::1', '127.0.0.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function test_getAllowlistedLoginIps_shouldResolveIpv6Only()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1', 'integration-test.matomo.org', '127.0.0.1']);
        $this->assertSame(['192.168.33.1', '::1', '127.0.0.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function test_getAllowlistedLoginIps_shouldReturnRanges()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['192.168.33.1', '204.93.177.0/25', '2001:db9::/48', '127.0.0.1']);
        $this->assertSame(['192.168.33.1', '204.93.177.0/25', '2001:db9::/48', '127.0.0.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function test_getAllowlistedLoginIps_shouldNotBeCheckedIfOnlyEmptyEntries()
    {
        $this->setGeneralConfig('login_allowlist_ip', ['', '192.168.33.1 ', ' ']);
        $this->assertSame(['192.168.33.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    public function test_getAllowlistedLoginIps_shouldNotReturnDuplicates()
    {
        $this->setGeneralConfig('login_allowlist_ip', [' 192.168.33.1', '192.168.33.1 ', ' 192.168.33.1 ', '192.168.33.1']);
        $this->assertSame(['192.168.33.1'], $this->allowlist->getAllowlistedLoginIps());
    }

    /**
     * @dataProvider getIpAllowlistedTests
     */
    public function test_isIpAllowlisted($expectedIsAllowlisted, $ipString)
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
    public function test_isIpAllowed_WhenNoIpsConfigured_AllIpsAreAllowed($expectedIsWhitelisted, $ipString)
    {
        $this->assertFalse($this->allowlist->isIpAllowed($ipString));
    }

    /**
     * @dataProvider getIpAllowlistedTests
     */
    public function test_checkIsAllowed($expectedIsAllowed, $ipString)
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
