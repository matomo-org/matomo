<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Http\ControllerResolver;
use Piwik\Plugin;
use Piwik\Settings\Storage;
use Piwik\Cache as PiwikCache;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;
use Piwik\Widget\WidgetsList;

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

    public function setUp(): void
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

    /** @see Issue https://github.com/piwik/piwik/issues/8422 */
    public function test_ListenNotToControllerMethodEventsThatDoesNotExists()
    {
        foreach ($this->manager->getLoadedPlugins() as $plugin) {
            $hooks = $plugin->registerEvents();
            foreach ($hooks as $hook => $callback) {
                if (0 === strpos($hook, 'Controller.')) {
                    list($controller, $module, $action) = explode('.', $hook);

                    try {
                        $resolver   = new ControllerResolver(StaticContainer::getContainer(), new Plugin\WidgetsProvider($this->manager));
                        $params = array();
                        $controller = $resolver->getController($module, $action, $params);
                    } catch (\Exception $e) {
                        $this->fail("$hook is listening to a controller method that does not exist");
                    }

                    $this->assertNotEmpty($controller);
                }
            }
        }
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
            array(true, 'a_ererer'),
            array(true, 'a_'),
            array(false, ''),
            array(false, '0'),
            array(false, '0a'),
            array(false, 'a.'),
            array(false, 'a-'),
            array(false, 'a-ererer'),
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
        // should currently load between 10 and 35 plugins
        $this->assertLessThan(35, count($this->manager->getLoadedPlugins()));
        $this->assertGreaterThan(10, count($this->manager->getLoadedPlugins()));

        // we need to make sure it actually only loaded the correct ones
        $this->assertEquals($expectedPluginNamesLoaded, array_keys($this->manager->getLoadedPlugins()));
    }
}
