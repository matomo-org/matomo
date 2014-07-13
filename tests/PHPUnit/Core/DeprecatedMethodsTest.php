<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\AssetManager;
use Piwik\AssetManager\UIAsset;
use Piwik\Plugin;

/**
 * @group Core
 */
class DeprecatedMethodsTest extends PHPUnit_Framework_TestCase
{

    public function test_version2_0_4()
    {
        $validTill = '2014-09-15';
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Period', 'factory', $validTill);

        $validTill = '2014-10-01';
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Config', 'getConfigSuperUserForBackwardCompatibility', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Menu\MenuAdmin', 'addEntry', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Menu\MenuAdmin', 'removeEntry', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Menu\MenuTop', 'addEntry', $validTill);
        $this->assertDeprecatedMethodIsRemoved('\Piwik\Menu\MenuTop', 'removeEntry', $validTill);

        $validTill = '2014-10-15';
        $this->assertDeprecatedMethodIsRemoved('\Piwik\SettingsPiwik', 'rewriteTmpPathWithHostname', $validTill);
    }

    private function assertDeprecatedMethodIsRemoved($className, $method, $removalDate)
    {
        $now         = \Piwik\Date::now();
        $removalDate = \Piwik\Date::factory($removalDate);

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
}