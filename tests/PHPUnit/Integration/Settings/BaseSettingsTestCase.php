<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Settings\FieldConfig;

/**
 * @group PluginSettings
 * @group SystemSettings
 */
class BaseSettingsTestCase extends IntegrationTestCase
{
    protected $updateEventName;

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        Db::destroyDatabaseObject();

        $this->assertNotDbConnectionCreated();

        $this->createSettingsInstance();

        $this->assertNotDbConnectionCreated();
    }

    public function test_makeSetting_ShouldAlsoAddTheSetting()
    {
        $this->assertNull($this->settings->getSetting('myName'));

        $this->makeSetting('myName');

        $this->assertNotNull($this->settings->getSetting('myName'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage A setting with name "myName" does already exist for plugin "ExampleSettingsPlugin"
     */
    public function test_makeSetting_ShouldFailWhenAdingSameSettingTwice()
    {
        $this->makeSetting('myName');
        $this->makeSetting('myName');
    }

    public function test_getSetting_CanRetrieveAspecificSetting()
    {
        $this->makeSetting('myName');

        $this->assertSame('myName', $this->settings->getSetting('myName')->getName());
    }

    public function test_getSetting_IsCaseSensitive()
    {
        $this->makeSetting('myName');

        $this->assertNull($this->settings->getSetting('myname'));
    }

    public function test_getSetting_ReturnsNullWhenNoSuchSettingFound()
    {
        $this->assertNull($this->settings->getSetting('myName'));
    }

    public function test_getSettingsWritableByCurrentUser_returnsOnlySettingsThatAreWritable()
    {
        $this->assertSame(array(), $this->settings->getSettingsWritableByCurrentUser());

        $setting1 = $this->makeSetting('myName1');
        $setting1->setIsWritableByCurrentUser(true);

        $setting2 = $this->makeSetting('myName2');
        $setting2->setIsWritableByCurrentUser(false);

        $setting3 = $this->makeSetting('myName3');
        $setting3->setIsWritableByCurrentUser(true);

        $expected = array(
            'myName1' => $setting1,
            'myName3' => $setting3
        );
        $this->assertSame($expected, $this->settings->getSettingsWritableByCurrentUser());
    }

    public function test_save_triggersAnEvent()
    {
        $settings = null;

        Piwik::addAction($this->updateEventName, function ($instance) use (&$settings) {
            $settings = $instance;
        });

        $this->settings->save();

        $this->assertSame($settings, $this->settings);
    }

    public function test_getTitle_shouldDefaultToPluginName()
    {
        $this->assertNotEmpty($this->settings->getTitle());
        $this->assertSame($this->settings->getTitle(), $this->settings->getPluginName());
    }

    public function test_getTitle_PrefersSetTitleOverPluginName()
    {
        if (method_exists($this->settings, 'setTitle')) {
            $this->settings->setTitle('title');
            $this->assertSame('title', $this->settings->getTitle());
        }
    }

    protected function makeSetting($name)
    {
        $type = FieldConfig::TYPE_STRING;
        return $this->settings->makeSetting($name, $default = '', $type, function () {});
    }

}
