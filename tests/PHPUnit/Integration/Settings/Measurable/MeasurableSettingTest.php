<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Plugin;

use Piwik\Db;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Measurable\MeasurableSetting;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\Settings\FakeMeasurableSettings;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;

/**
 * @group MeasurableSettings
 * @group Settings
 * @group MeasurableSetting
 */
class MeasurableSettingTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        foreach (array(2,3) as $idSite) {
            if (!Fixture::siteCreated($idSite)) {
                Fixture::createWebsite('2014-01-01 01:01:01');
            }
        }

        Db::destroyDatabaseObject();
    }

    protected function createSettingsInstance()
    {
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2014-01-01 01:01:01');
        }

        return new FakeMeasurableSettings($idSite = 1);
    }

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        new MeasurableSetting('name', $default = 5, FieldConfig::TYPE_INT, 'MyPlugin', $idSite = 1);

        $this->assertNotDbConnectionCreated();
    }

    public function test_save()
    {
        $site1 = $this->buildSetting('field1', null, $site = '1');
        $site1->setValue('value1');
        $site1->save();

        $site2 = $this->buildSetting('field1', null, $site = '2');
        $this->assertSame('value1', $site1->getValue());
        $this->assertSame('', $site2->getValue());
        $site2->setValue('value2');
        $site2->save();

        $site3 = $this->buildSetting('field1', null, $site = '3');
        $this->assertSame('value1', $site1->getValue());
        $this->assertSame('value2', $site2->getValue());
        $this->assertSame('', $site3->getValue());

        $site1Field2 = $this->buildSetting('field2', null, $site = '1');
        $this->assertSame('', $site1Field2->getValue());
        $site1Field2->setValue('value1Field2');
        $site1Field2->save();

        $this->assertSame('value1', $site1->getValue());
        $this->assertSame('value1Field2', $site1Field2->getValue());
    }

    private function buildSetting($name, $type = null, $idSite = null)
    {
        if (!isset($type)) {
            $type = FieldConfig::TYPE_STRING;
        }

        if (!isset($idSite)) {
            $idSite = 1;
        }

        $userSetting = new MeasurableSetting($name, $default = '', $type, 'MyPluginName', $idSite);

        return $userSetting;
    }
}
