<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage;

use Piwik\Settings\Storage;
use Piwik\Settings\Storage\Factory;
use Piwik\SettingsServer;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\SettingsStorage;

/**
 * @group Tracker
 * @group Handler
 * @group Visit
 * @group Factory
 * @group FactoryTest
 */
class FactoryTest extends IntegrationTestCase
{

    public function test_make_shouldCreateDefaultInstance()
    {
        $storage = Factory::make('PluginName');
        $this->assertTrue($storage instanceof Storage);
    }

    public function test_make_shouldCreateTrackerInstance_IfInTrackerMode()
    {
        $storage = $this->makeTrackerInstance();

        $this->assertTrue($storage instanceof SettingsStorage);
    }

    public function test_make_shouldPassThePluginNameToTheStorage()
    {
        $storage = Factory::make('PluginName');
        $this->assertEquals('Plugin_PluginName_Settings', $storage->getOptionKey());
    }

    public function test_make_shouldPassThePluginNameToTheSettingsStorage()
    {
        $storage = $this->makeTrackerInstance();

        $this->assertEquals('Plugin_PluginName_Settings', $storage->getOptionKey());
    }

    private function makeTrackerInstance()
    {
        SettingsServer::setIsTrackerApiRequest();

        $storage = Factory::make('PluginName');

        SettingsServer::setIsNotTrackerApiRequest();

        return $storage;
    }
}
