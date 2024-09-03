<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin\tests\Integration;

use Piwik\Plugins\CustomDirPlugin\SystemSettings;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomDirPlugin
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

    public function testGetPluginName()
    {
        $this->assertSame('CustomDirPlugin', $this->settings->getPluginName());
    }

    public function testGetDefault()
    {
        $this->assertSame('', $this->settings->custom->getValue());
    }

    public function testSetValue()
    {
        $this->settings->custom->setValue('%');
        $this->assertSame('%', $this->settings->custom->getValue());
    }

}
