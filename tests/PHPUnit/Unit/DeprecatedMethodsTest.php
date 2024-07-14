<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\CronArchive;
use Piwik\Date;
use Piwik\Version;
use ReflectionClass;

/**
 * @group DeprecatedMethodsTest
 * @group Core
 */
class DeprecatedMethodsTest extends \PHPUnit\Framework\TestCase
{
    public function testDeprecations()
    {
        $this->assertDeprecatedMethodIsRemovedInPiwik3b1('Piwik\SettingsServer', 'isApache');

        $validTill = '2015-03-10';
        $this->assertDeprecatedMethodIsRemovedBeforeDate('\Piwik\Period', 'factory', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('\Piwik\Config', 'getConfigSuperUserForBackwardCompatibility', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('\Piwik\Menu\MenuAdmin', 'addEntry', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('\Piwik\Menu\MenuAdmin', 'removeEntry', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('\Piwik\Menu\MenuTop', 'addEntry', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('\Piwik\Menu\MenuTop', 'removeEntry', $validTill);

        $validTill = '2015-03-10';
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'sanitizeIp', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'sanitizeIpRange', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'P2N', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'N2P', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'prettyPrint', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'isIPv4', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'long2ip', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'isIPv6', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'isMappedIPv4', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'getIPv4FromMappedIPv6', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'getIpsForRange', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'isIpInRange', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\IP', 'getHostByAddr', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\SettingsPiwik', 'rewriteTmpPathWithInstanceId', $validTill);

        $validTill = '2015-05-01';
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getBrowserVersion', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getBrowser', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getOS', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getOSFamily', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getBrowserType', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getMobileVsDesktop', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getResolution', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getConfiguration', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getPlugin', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getLanguage', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\API', 'getLanguageCode', $validTill);
        $this->assertDeprecatedMethodIsRemovedBeforeDate('Piwik\Plugins\UserSettings\UserSettings', 'renameDeprecatedModuleAndAction', $validTill);

        // please be aware if re-adding a plugin called userSettings, and someone updates eg from Piwik 2.13 to that version,
        // the plugin will be possibly removed in an Update during 2.14.0
        $this->assertDeprecatedClassIsRemoved('Piwik\Plugins\UserSettings\UserSettings', $validTill);

        $this->assertDeprecatedMethodIsRemovedInPiwik3('\Piwik\Menu\MenuAbstract', 'add');
        $this->assertDeprecatedMethodIsRemovedInPiwik3('\Piwik\Archive', 'getDataTableFromArchive');
        $this->assertDeprecatedMethodIsRemovedInPiwik3('\Piwik\Plugins\API\API', 'getLastDate');

        $this->assertDeprecatedMethodIsRemovedInPiwik3('Piwik\Plugins\DevicesDetection\DevicesDetection', 'renameUserSettingsModuleAndAction');
        $this->assertDeprecatedMethodIsRemovedInPiwik3('Piwik\Plugins\Resolution\Resolution', 'renameUserSettingsModuleAndAction');
        $this->assertDeprecatedMethodIsRemovedInPiwik3('Piwik\Plugins\DevicePlugins\DevicePlugins', 'renameUserSettingsModuleAndAction');
        $this->assertDeprecatedMethodIsRemovedInPiwik3('Piwik\Plugins\UserLanguage\UserLanguage', 'renameUserSettingsModuleAndAction');

        $this->assertDeprecatedMethodIsRemovedInMatomo4('\Piwik\Plugin', 'getListHooksRegistered');
        $this->assertDeprecatedMethodIsRemovedInMatomo4('Piwik\Updates', 'getSql');
        $this->assertDeprecatedMethodIsRemovedInMatomo4('Piwik\Updates', 'update');
        $this->assertDeprecatedMethodIsRemovedInMatomo4('Piwik\Updates', 'getMigrationQueries');
        $this->assertDeprecatedMethodIsRemovedInMatomo4('Piwik\Updater', 'executeMigrationQueries');

        $this->assertDeprecatedMethodIsRemovedInMatomo4('Piwik\SettingsPiwik', 'isPiwikInstalled');
        $this->assertDeprecatedMethodIsRemovedInMatomo4('Piwik\Piwik', 'doAsSuperUser');

        $validTill = '2021-03-01';
        $this->assertDeprecatedMethodIsRemovedBeforeDate(CronArchive::class, 'checkNoDanglingInvalidations', $validTill);

        $this->assertDeprecatedMethodIsRemovedInMatomo6('Piwik\Common', 'getRequestVar');
        $this->assertDeprecatedMethodIsRemovedInMatomo6('Piwik\Plugins\Overlay\API', 'getExcludedQueryParameters');
        $this->assertDeprecatedMethodIsRemovedInMatomo6('Piwik\Db', 'isOptimizeInnoDBSupported');
        $this->assertDeprecatedMethodIsRemovedInMatomo6('Piwik\Db', 'optimizeTables');
    }


    private function assertDeprecatedMethodIsRemovedBeforeDate($className, $method, $removalDate)
    {
        $now         = Date::now();
        $removalDate = Date::factory($removalDate);

        if (!class_exists($className)) {
            return;
        }

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

    private function assertDeprecatedMethodIsRemovedInPiwik3b1($className, $method)
    {
        $this->assertDeprecatedMethodIsRemovedInPiwikVersion('3.0.0-b1', $className, $method);
    }

    private function assertDeprecatedMethodIsRemovedInPiwik3($className, $method)
    {
        $this->assertDeprecatedMethodIsRemovedInPiwikVersion('3.0.0-b2', $className, $method);
    }

    private function assertDeprecatedMethodIsRemovedInMatomo4($className, $method)
    {
        $this->assertDeprecatedMethodIsRemovedInPiwikVersion('4.0.0-b1', $className, $method);
    }

    private function assertDeprecatedMethodIsRemovedInMatomo6($className, $method)
    {
        $this->assertDeprecatedMethodIsRemovedInPiwikVersion('6.0.0-b1', $className, $method);
    }

    private function assertDeprecatedMethodIsRemovedInPiwikVersion($piwikVersion, $className, $method)
    {
        $version = Version::VERSION;

        $class        = new ReflectionClass($className);
        $methodExists = $class->hasMethod($method);

        if (-1 === version_compare($version, $piwikVersion)) {
            $errorMessage = $className . '::' . $method . ' should still exists until ' . $piwikVersion . ' although it is deprecated.';
            $this->assertTrue($methodExists, $errorMessage);
            return;
        }

        $errorMessage = $className . '::' . $method . ' should be removed as the method is deprecated but it is not.';
        $this->assertFalse($methodExists, $errorMessage);
    }
}
