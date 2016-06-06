<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Measurable;

use Piwik\Db;
use Piwik\Plugins\MobileAppMeasurable\tests\Framework\Mock\Type;
use Piwik\Plugins\MobileAppMeasurable\Type as MobileAppType;
use Piwik\Plugin;
use Piwik\Measurable\Measurable;
use Piwik\Measurable\MeasurableSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class MeasurableTest extends IntegrationTestCase
{
    private $idSite = 1;

    /**
     * @var Measurable
     */
    private $measurable;

    private $settingName = 'app_id';

    public function setUp()
    {
        parent::setUp();

        Plugin\Manager::getInstance()->activatePlugin('MobileAppMeasurable');

        if (!Fixture::siteCreated($this->idSite)) {
            $type = MobileAppType::ID;
            Fixture::createWebsite('2015-01-01 00:00:00',
                $ecommerce = 0, $siteName = false, $siteUrl = false,
                $siteSearch = 1, $searchKeywordParameters = null,
                $searchCategoryParameters = null, $timezone = null, $type);
        }

        $this->measurable = new Measurable($this->idSite);
    }

    public function testGetSettingValue_shouldReturnValue_IfSettingExistsAndIsReadable()
    {
        $setting = new MeasurableSettings($this->idSite, Measurable::getTypeFor($this->idSite));
        $setting->getSetting($this->settingName)->setValue('mytest');

        $value = $this->measurable->getSettingValue($this->settingName);
        $this->assertNull($value);

        $setting->save(); // actually save value

        $value = $this->measurable->getSettingValue($this->settingName);
        $this->assertSame('mytest', $value);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage does not exist
     */
    public function testGetSettingValue_shouldThrowException_IfSettingDoesNotExist()
    {;
        $this->measurable->getSettingValue('NoTeXisTenT');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingReadNotAllowed
     */
    public function testGetSettingValue_shouldThrowException_IfNoPermissionToRead()
    {
        FakeAccess::clearAccess();
        $this->measurable->getSettingValue('app_id');
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
            'Piwik\Plugins\MobileAppMeasurable\Type' => new Type()
        );
    }

}
