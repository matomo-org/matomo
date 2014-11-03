<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\AssetManager;
use Piwik\AssetManager\UIAsset;
use Piwik\Date;
use Piwik\Plugin;
use Piwik\Version;
use ReflectionClass;

/**
 * @group Core
 */
class DeprecatedMethodsTest extends \PHPUnit_Framework_TestCase
{
    public function test_version2_0_4()
    {
        $validTill = '2014-11-25';
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Period', 'factory', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Config', 'getConfigSuperUserForBackwardCompatibility', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Menu\MenuAdmin', 'addEntry', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Menu\MenuAdmin', 'removeEntry', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Menu\MenuTop', 'addEntry', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Menu\MenuTop', 'removeEntry', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\SettingsPiwik', 'rewriteTmpPathWithHostname', $validTill);

        $validTill = '2015-02-06';
        $this->assertDeprecatedClassIsRemoved('\IntegrationTestCase', $validTill);
        $this->assertDeprecatedClassIsRemoved('\DatabaseTestCase', $validTill);
        $this->assertDeprecatedClassIsRemoved('\BenchmarkTestCase', $validTill);
        $this->assertDeprecatedClassIsRemoved('\FakeAccess', $validTill);
        $this->assertDeprecatedClassIsRemoved('\Piwik\Tests\ConsoleCommandTestCase', $validTill);
        $this->assertDeprecatedClassIsRemoved('\Piwik\Tests\Fixture', $validTill);
        $this->assertDeprecatedClassIsRemoved('\Piwik\Tests\OverrideLogin', $validTill);

        $validTill = '2015-03-01';
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'sanitizeIp', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'sanitizeIpRange', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'P2N', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'N2P', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'prettyPrint', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'isIPv4', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'long2ip', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'isIPv6', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'isMappedIPv4', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'getIPv4FromMappedIPv6', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'getIpsForRange', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'isIpInRange', $validTill);
        $this->assertDeprecatedMethodIsRemoved('Piwik\IP', 'getHostByAddr', $validTill);

        $this->assertDeprecatedMethodIsRemovedInPiwik3('\Piwik\Menu\MenuAbstract', 'add');
    }

    private function assertDeprecatedMethodIsRemoved($className, $method, $removalDate)
    {
        $now         = Date::now();
        $removalDate = Date::factory($removalDate);

        $class        = new ReflectionClass($className);
        $methodExists = $class->hasMethod($method);

        if (!$now->isLater($removalDate)) {

            $errorMessage = $className . '::' . $method . ' should still exists until ' . $removalDate . ' although it is deprecated.';
            $this->assertTrue($methodExists, $errorMessage);
            return;
        }

        $errorMessage = $className . '::' . $method . ' should be removed as the method is deprecated but it is not.';
        $this->assertFalse($methodExists, $errorMessage);
    }


    private function assertDeprecatedClassIsRemoved($className, $removalDate)
    {
        $now         = Date::now();
        $removalDate = Date::factory($removalDate);

        $classExists = class_exists($className);

        if (!$now->isLater($removalDate)) {

            $errorMessage = $className . ' should still exists until ' . $removalDate . ' although it is deprecated.';
            $this->assertTrue($classExists, $errorMessage);
            return;
        }

        $errorMessage = $className . ' should be removed as the method is deprecated but it is not.';
        $this->assertFalse($classExists, $errorMessage);
    }

    private function assertDeprecatedMethodIsRemovedInPiwik3($className, $method)
    {
        $version = Version::VERSION;

        $class        = new ReflectionClass($className);
        $methodExists = $class->hasMethod($method);

        if (-1 === version_compare($version, '3.0.0')) {

            $errorMessage = $className . '::' . $method . ' should still exists until 3.0 although it is deprecated.';
            $this->assertTrue($methodExists, $errorMessage);
            return;
        }

        $errorMessage = $className . '::' . $method . ' should be removed as the method is deprecated but it is not.';
        $this->assertFalse($methodExists, $errorMessage);
    }
}