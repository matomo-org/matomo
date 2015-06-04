<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LeftMenu\tests\Integration;

use Piwik\Plugins\LeftMenu\API;
use Piwik\Plugins\LeftMenu\Settings;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group LeftMenu
 * @group APITest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var Settings
     */
    private $settings;

    public function setUp()
    {
        parent::setUp();

        $this->api = API::getInstance();
        $this->createSettings();
    }

    public function test_isEnabled_shouldReturnFalse_ByDefault()
    {
        $this->assertLeftMenuIsDisabled();

        $this->setUser();
        $this->assertLeftMenuIsDisabled();

        $this->setSuperUser();
        $this->assertLeftMenuIsDisabled();
    }

    public function test_isEnabled_shouldReturnTrue_IfEnabledSystemWideAndNoUserPreference()
    {
        $this->enableLeftMenuForAll();

        $this->assertLeftMenuIsEnabled();

        $this->setUser();
        $this->assertLeftMenuIsEnabled();

        $this->setAnonymous();
        $this->assertLeftMenuIsEnabled();
    }

    public function test_isEnabled_AUserPreferenceShouldOverwriteASystemPreference_DefaultDisabled()
    {
        $this->assertLeftMenuIsDisabled();

        $this->setUser();
        $this->setUserSettingValue('yes');

        $this->assertLeftMenuIsEnabled();

        $this->setAnonymous();
        $this->assertLeftMenuIsDisabled();
    }

    public function test_isEnabled_AUserPreferenceShouldOverwriteASystemPreference_DefaultEnabled()
    {
        $this->enableLeftMenuForAll();

        $this->assertLeftMenuIsEnabled();

        $this->setUser();
        $this->setUserSettingValue('no');

        $this->assertLeftMenuIsDisabled();

        $this->setAnonymous();
        $this->assertLeftMenuIsEnabled();
    }

    private function assertLeftMenuIsEnabled()
    {
        $this->assertTrue($this->api->isEnabled());
    }

    private function assertLeftMenuIsDisabled()
    {
        $this->assertFalse($this->api->isEnabled());
    }

    private function setSuperUser()
    {
        FakeAccess::$superUser = true;
        FakeAccess::$superUserLogin = 'superUserLogin';

        $this->createSettings();
    }

    private function setAnonymous()
    {
        FakeAccess::clearAccess();
        $this->createSettings();
    }

    private function setUser()
    {
        FakeAccess::$idSitesView = array(1);
        FakeAccess::$identity    = 'userLogin';

        $this->createSettings();
    }

    private function enableLeftMenuForAll()
    {
        $this->setSuperUser();
        $this->settings->globalEnabled->setValue(true);
        $this->settings->save();
    }

    private function createSettings()
    {
        $this->settings = new Settings('LeftMenu');
    }

    private function setUserSettingValue($value)
    {
        $this->settings->userEnabled->setValue($value);
        $this->settings->save();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
