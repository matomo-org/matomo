<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Measurable;

use Piwik\Plugin;
use Piwik\Plugins\WebsiteMeasurable\Type as WebsiteType;
use Piwik\Plugins\WebsiteMeasurable\MeasurableSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class MeasurableSettingsTest extends IntegrationTestCase
{
    private $idSite = 1;

    /**
     * @var MeasurableSettings
     */
    private $settings;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;

        if (!Fixture::siteCreated($this->idSite)) {
            $type = WebsiteType::ID;
            Fixture::createWebsite('2015-01-01 00:00:00',
                $ecommerce = 0, $siteName = false, $siteUrl = false,
                $siteSearch = 1, $searchKeywordParameters = null,
                $searchCategoryParameters = null, $timezone = null, $type);
        }

        $this->settings = $this->createSettings();
    }

    public function test_save_shouldActuallyStoreValues()
    {
        $this->settings->siteSearchKeywords->setValue(array('value2'));
        $this->settings->siteSearchCategory->setValue(array('value3'));
        $this->settings->save();

        $this->assertStoredSettingsValue(array('value2'), 'sitesearch_keyword_parameters');
        $this->assertStoredSettingsValue(array('value3'), 'sitesearch_category_parameters');
    }

    public function test_save_shouldCheckAdminPermissionsForThatSite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CoreAdminHome_PluginSettingChangeNotAllowed');

        FakeAccess::clearAccess();

        $this->settings = $this->createSettings();
        $this->settings->siteSearchKeywords->setValue(array('value4'));
        $this->settings->save();
    }

    private function createSettings()
    {
        $provider = new Plugin\SettingsProvider(Plugin\Manager::getInstance());
        $settings = $provider->getMeasurableSettings('WebsiteMeasurable', $this->idSite, WebsiteType::ID);

        return $settings;
    }

    private function assertStoredSettingsValue($expectedValue, $settingName)
    {
        $settings = $this->createSettings();
        $value    = $settings->getSetting($settingName)->getValue();

        $this->assertSame($expectedValue, $value);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}
