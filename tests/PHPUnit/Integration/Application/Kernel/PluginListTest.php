<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration\Application\Kernel;

use Piwik\Application\Kernel\PluginList;
use Piwik\Container\StaticContainer;

/**
 * @group PluginListTest
 * @group Core
 */
class PluginListTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PluginList
     */
    private $pluginList = array();

    public function setUp()
    {
        parent::setUp();
        $this->pluginList = $this->makePluginList();
    }

    public function test_sortPlugins()
    {
        $pluginList = $this->makePluginList();
        $sorted = $pluginList->sortPlugins(array('UsersManager', 'CoreHome', 'MyCustomPlugin', 'ExampleCommand', 'MyCustomPlugin2', 'Abcdef'));
        $this->assertSame(array(
            'CoreHome', // core plugins loaded first
            'UsersManager',
            'ExampleCommand', // a "by default disabled plugin" is loaded before custom plugins
            'Abcdef', // then we load custom plugins
            'MyCustomPlugin',
            'MyCustomPlugin2',
        ), $sorted);
    }

    public function test_sortPlugins_onlyCorePlugins()
    {
        $pluginList = $this->makePluginList();
        $sorted = $pluginList->sortPlugins(array('UsersManager', 'CoreHome'));
        $this->assertSame(array('CoreHome','UsersManager'), $sorted);
    }

    public function test_sortPluginsAndRespectDependencies_sortsPluginsAlphabetically()
    {
        $pluginList = $this->makePluginList();
        $sorted = $pluginList->sortPluginsAndRespectDependencies(array(
            'UsersManager', 'MyCustomPlugin', 'ExampleCommand', 'MyCustomPlugin2', 'CoreHome', 'Abcdef'
        ));
        $this->assertSame(array(
            'CoreHome', // core plugins loaded first
            'UsersManager',
            'ExampleCommand', // a "by default disabled plugin" is loaded before custom plugins
            'Abcdef', // then we load custom plugins
            'MyCustomPlugin',
            'MyCustomPlugin2',
        ), $sorted);
    }

    public function test_sortPluginsAndRespectDependencies_makesSureToListRequiredDependencyFirst()
    {
        $pluginJsonInfo = array(
            'Abcdef' => array('require' => array('MyCustomPlugin2' => '2.2.1')),
            'MyCustomPlugin2' => array('require' => array('CoreHome' => '4.2.1', 'MyCustomPlugin3' => '3.0.3')),
            'fooBar' => array('require' => array('Ast' => '1.2.1', 'MyCustomPlugin3' => '3.0.3'))
        );

        $pluginList = $this->makePluginList();
        $sorted = $pluginList->sortPluginsAndRespectDependencies(array(
            'UsersManager', 'MyCustomPlugin',
            'ExampleCommand', 'MyCustomPlugin2', 'Ast',
            'Acc', 'MyCustomPlugin3', 'CoreHome', 'Abcdef', 'fooBar',
        ), $pluginJsonInfo);
        $this->assertSame(array(
            'CoreHome', // core plugins loaded first
            'UsersManager',
            'ExampleCommand', // a "by default disabled plugin" is loaded before custom plugins
            'MyCustomPlugin3',
            'MyCustomPlugin2',
            'Abcdef',
            'Acc',
            'Ast',
            'fooBar',
            'MyCustomPlugin',
        ), $sorted);
    }

    public function test_sortPluginsAndRespectDependencies_onlyCorePlugins()
    {
        $pluginList = $this->makePluginList();
        $sorted = $pluginList->sortPluginsAndRespectDependencies(array('UsersManager', 'CoreHome'));
        $this->assertSame(array('CoreHome','UsersManager'), $sorted);
    }

    private function makePluginList()
    {
        $globalSettingsProvider = StaticContainer::get('Piwik\Application\Kernel\GlobalSettingsProvider');
        $section = $globalSettingsProvider->getSection('Plugins');
       // $section['Plugins'] = $pluginsToLoad;
        $globalSettingsProvider->setSection('Plugins', $section);
        return new PluginList($globalSettingsProvider);
    }

}
