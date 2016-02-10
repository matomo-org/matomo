<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Db;
use Piwik\Plugin;
use Piwik\Settings\Storage;
use Piwik\Cache as PiwikCache;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;

/**
 * @group Plugin
 * @group PluginManager
 */
class ManagerTest extends IntegrationTestCase
{
    private $trackerCacheId = 'PluginsTracker';

    /**
     * @var Plugin\Manager
     */
    private $manager;

    public function setUp()
    {
        parent::setUp();
        $this->manager = Plugin\Manager::getInstance();
    }

    public function test_loadTrackerPlugins_shouldDetectTrackerPlugins()
    {
        $this->assertGreaterThan(50, count($this->manager->getLoadedPlugins())); // make sure all plugins are loaded

        $pluginsToLoad = $this->manager->loadTrackerPlugins();

        $this->assertOnlyTrackerPluginsAreLoaded($pluginsToLoad);
    }

    public function test_loadTrackerPlugins_shouldCacheListOfPlugins()
    {
        $cache = $this->getCacheForTrackerPlugins();
        $this->assertFalse($cache->contains($this->trackerCacheId));

        $pluginsToLoad = $this->manager->loadTrackerPlugins();

        $this->assertTrue($cache->contains($this->trackerCacheId));
        $this->assertEquals($pluginsToLoad, $cache->fetch($this->trackerCacheId));
    }

    public function test_loadTrackerPlugins_shouldBeAbleToLoadPluginsCorrectWhenItIsCached()
    {
        $pluginsToLoad = array('CoreAdminHome', 'CoreHome', 'UserLanguage', 'Login');
        $this->getCacheForTrackerPlugins()->save($this->trackerCacheId, $pluginsToLoad);

        $pluginsToLoad = $this->manager->loadTrackerPlugins();

        $this->assertCount(4, $this->manager->getLoadedPlugins());
        $this->assertEquals($pluginsToLoad, array_keys($this->manager->getLoadedPlugins()));
    }

    public function test_loadTrackerPlugins_shouldUnloadAllPlugins_IfThereAreNoneToLoad()
    {
        $pluginsToLoad = array();
        $this->getCacheForTrackerPlugins()->save($this->trackerCacheId, $pluginsToLoad);

        $pluginsToLoad = $this->manager->loadTrackerPlugins();

        $this->assertEquals(array(), $pluginsToLoad);
        $this->assertEquals(array(), $this->manager->getLoadedPlugins());
    }

    public function test_deactivatePlugin()
    {
        $this->assertFalse($this->manager->isPluginActivated('ExampleTheme'));
        $this->manager->activatePlugin('ExampleTheme');
        $this->assertTrue($this->manager->isPluginActivated('ExampleTheme'));
        $this->manager->deactivatePlugin('ExampleTheme');
        $this->assertFalse($this->manager->isPluginActivated('ExampleTheme'));
    }

    /**
     * @dataProvider getPluginNameProvider
     */
    public function test_isValidPluginName($expectedIsValid, $pluginName)
    {
        $valid = $this->manager->isValidPluginName($pluginName);
        $this->assertSame($expectedIsValid, $valid);
    }

    public function getPluginNameProvider()
    {
        return array(
            array(true, 'a'),
            array(true, 'a0'),
            array(true, 'pluginNameTest'),
            array(true, 'PluginNameTest'),
            array(true, 'PluginNameTest92323232eerwrwere938'),
            array(false, ''),
            array(false, '0'),
            array(false, '0a'),
            array(false, 'a.'),
            array(false, 'a-'),
            array(false, 'a_'),
            array(false, 'a-ererer'),
            array(false, 'a_ererer'),
            array(false, '..'),
            array(false, '/'),
        );
    }

    private function getCacheForTrackerPlugins()
    {
        return PiwikCache::getEagerCache();
    }

    private function assertOnlyTrackerPluginsAreLoaded($expectedPluginNamesLoaded)
    {
        // should currently load between 10 and 25 plugins
        $this->assertLessThan(25, count($this->manager->getLoadedPlugins()));
        $this->assertGreaterThan(10, count($this->manager->getLoadedPlugins()));

        // we need to make sure it actually only loaded the correct ones
        $this->assertEquals($expectedPluginNamesLoaded, array_keys($this->manager->getLoadedPlugins()));
    }
}
