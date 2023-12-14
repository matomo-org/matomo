<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\Integration;

use Piwik\Plugins\TwoFactorAuth\SystemSettings;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Url;

/**
 * @group TwoFactorAuth
 * @group SystemSettingsTest
 * @group Plugins
 */
class SystemSettingsTest extends IntegrationTestCase
{
    /**
     * @var SystemSettings
     */
    private $settings;

    public function setUp(): void
    {
        parent::setUp();

        $this->settings = new SystemSettings();
    }

    public function test_twoFactorAuthRequired_defaultDisabled()
    {
        $this->assertFalse($this->settings->twoFactorAuthRequired->getValue());
    }

    public function test_twoFactorAuthTitle_defaultTitle()
    {
        $this->assertEquals('Analytics - ' . Url::getCurrentHost(), $this->settings->twoFactorAuthTitle->getValue());
    }

}
