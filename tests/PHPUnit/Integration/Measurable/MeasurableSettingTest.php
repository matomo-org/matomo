<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Measurable;

use Piwik\Measurable\MeasurableSetting;
use Piwik\Settings\Storage;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class MeasurableSettingTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        FakeAccess::$superUser = true;
    }

    private function createSetting()
    {
        $setting = new MeasurableSetting('name', 'test');
        $storage = new Storage('test');
        $setting->setStorage($storage);
        return $setting;
    }

    public function test_setValue_getValue_shouldSucceed_IfEnoughPermission()
    {
        $setting = $this->createSetting();
        $setting->setValue('test');
        $value = $setting->getValue();

        $this->assertSame('test', $value);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function testSetValue_shouldThrowException_IfOnlyViewPermission()
    {
        FakeAccess::clearAccess();
        FakeAccess::setIdSitesView(array(1, 2, 3));
        $this->createSetting()->setValue('test');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function testSetValue_shouldThrowException_IfNoPermissionAtAll()
    {
        FakeAccess::clearAccess();
        $this->createSetting()->setValue('test');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingReadNotAllowed
     */
    public function testGetSettingValue_shouldThrowException_IfNoPermissionToRead()
    {
        FakeAccess::clearAccess();
        $this->createSetting()->getValue();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }

}
