<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\NoAccessException;
use Piwik\Plugins\CoreHome\LoginWhitelist;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CustomLoginWhitelist extends LoginWhitelist {

    public function getWhitelistedLoginIps()
    {
        return parent::getWhitelistedLoginIps();
    }

    public function isIpWhitelisted($ip)
    {
        return parent::isIpWhitelisted($ip);
    }
}

/**
 * @group Plugins
 * @group LoginWhitelist
 * @group LoginWhitelistTest
 */
class LoginWhitelistTest extends IntegrationTestCase
{
    /**
     * @var CustomLoginWhitelist
     */
    private $whitelist;

    private $cliMode;

    public function setUp()
    {
        parent::setUp();

        $this->cliMode = Common::$isCliMode;
        Common::$isCliMode = false;

        $this->whitelist = new CustomLoginWhitelist();
    }

    public function tearDown()
    {
        Common::$isCliMode = $this->cliMode;
        parent::tearDown();
    }

    public function test_shouldWhitelistApplyToAPI_shouldBeEnabledByDefault()
    {
        $this->assertTrue($this->whitelist->shouldWhitelistApplyToAPI());
    }

    public function test_shouldWhitelistApplyToAPI_canBeDisabled()
    {
        $this->setGeneralConfig('login_whitelist_apply_to_reporting_api_requests', '0');
        $this->assertFalse($this->whitelist->shouldWhitelistApplyToAPI());
    }

    public function test_shouldWhitelistApplyToAPI_enabled()
    {
        $this->setGeneralConfig('login_whitelist_apply_to_reporting_api_requests', '1');
        $this->assertTrue($this->whitelist->shouldWhitelistApplyToAPI());
    }

    public function test_shouldCheckWhitelist_shouldNotBeCheckedByDefaultAndNotHaveAnyIps()
    {
        $this->assertFalse($this->whitelist->shouldCheckWhitelist());
    }

    public function test_shouldCheckWhitelist_shouldBeCheckedIfHasAtLeastOneIp()
    {
        $this->setGeneralConfig('login_whitelist_ip', ['192.168.33.1']);
        $this->assertTrue($this->whitelist->shouldCheckWhitelist());
    }

    public function test_shouldCheckWhitelist_shouldNotBeCheckedIfExecutedFromCLI()
    {
        Common::$isCliMode = true;
        $this->setGeneralConfig('login_whitelist_ip', ['192.168.33.1']);
        $this->assertFalse($this->whitelist->shouldCheckWhitelist());
    }

    public function test_shouldCheckWhitelist_shouldNotBeCheckedIfOnlyEmptyEntries()
    {
        $this->setGeneralConfig('login_whitelist_ip', ['', ' ']);
        $this->assertFalse($this->whitelist->shouldCheckWhitelist());
    }

    public function test_getWhitelistedLoginIps_shouldReturnEmptyArrayByDefault()
    {
        $this->assertSame($this->whitelist->getWhitelistedLoginIps(), []);
    }

    public function test_getWhitelistedLoginIps_shouldReturnIpsAndTrimIfNeeded()
    {
        $this->setGeneralConfig('login_whitelist_ip', ['192.168.33.1', ' 127.0.0.1 ', '2001:0db8:85a3:0000:0000:8a2e:0370:7334']);
        $this->assertSame(['192.168.33.1', '127.0.0.1', '2001:0db8:85a3:0000:0000:8a2e:0370:7334'], $this->whitelist->getWhitelistedLoginIps());
    }

    public function test_getWhitelistedLoginIps_shouldNotBeCheckedIfOnlyEmptyEntries()
    {
        $this->setGeneralConfig('login_whitelist_ip', ['', '192.168.33.1 ', ' ']);
        $this->assertSame(['192.168.33.1'], $this->whitelist->getWhitelistedLoginIps());
    }

    public function test_getWhitelistedLoginIps_shouldNotReturnDuplicates()
    {
        $this->setGeneralConfig('login_whitelist_ip', [' 192.168.33.1', '192.168.33.1 ', ' 192.168.33.1 ', '192.168.33.1']);
        $this->assertSame(['192.168.33.1'], $this->whitelist->getWhitelistedLoginIps());
    }

    /**
     * @dataProvider getIpWhitelistedTests
     */
    public function test_isIpWhitelisted($expectedIsWhitelisted, $ipString)
    {
        $ipsWhitelisted = [
            '127.0.0.1',
            '192.168.33.1',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            '204.93.240.*',
            '204.93.177.0/25',
            '2001:db9::/48'
        ];
        $this->setGeneralConfig('login_whitelist_ip', $ipsWhitelisted);
        $this->assertSame($expectedIsWhitelisted, $this->whitelist->isIpWhitelisted($ipString));
    }

    /**
     * @dataProvider getIpWhitelistedTests
     */
    public function test_isIpWhitelisted_WhenNoIpsConfigured_AllIpsAreWhitelisted($expectedIsWhitelisted, $ipString)
    {
        $this->assertFalse($this->whitelist->isIpWhitelisted($ipString));
    }

    /**
     * @dataProvider getIpWhitelistedTests
     */
    public function test_checkIsWhitelisted($expectedIsWhitelisted, $ipString)
    {
        $ipsWhitelisted = [
            '127.0.0.1',
            '192.168.33.1',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            '204.93.240.*',
            '204.93.177.0/25',
            '2001:db9::/48'
        ];
        $this->setGeneralConfig('login_whitelist_ip', $ipsWhitelisted);

        if ($expectedIsWhitelisted) {
            $this->whitelist->checkIsWhitelisted($ipString);
            $this->assertTrue(true);
        } else {
            try {
                $this->whitelist->checkIsWhitelisted($ipString);
                $this->fail('An expected exception has not been thrown');
            } catch (NoAccessException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function getIpWhitelistedTests()
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
