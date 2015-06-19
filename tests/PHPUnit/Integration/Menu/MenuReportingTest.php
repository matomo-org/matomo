<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Menu;

use Piwik\Plugin\Report;
use Piwik\Piwik;
use Piwik\Metrics;
use Piwik\Menu\MenuReporting;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class ReportingTest extends IntegrationTestCase
{
    /**
     * @var MenuReporting
     */
    private $menu;

    public function setUp()
    {
        parent::setUp();

        PluginManager::getInstance()->unloadPlugins();
        $this->menu = MenuReporting::getInstance();
    }

    public function tearDown()
    {
        MenuReporting::getInstance()->unsetInstance();
        parent::tearDown();
    }

    public function test_getMenu_shouldBeNull_IfNoItems()
    {
        $this->assertNull($this->menu->getMenu());
    }

    public function test_getMenu_shouldTriggerAddItemsEvent_toBeBackwardsCompatible()
    {
        $this->loadSomePlugins();

        $triggered = false;
        Piwik::addAction('Menu.Reporting.addItems', function () use (&$triggered) {
            $triggered = true;
        });

        $this->menu->getMenu();

        $this->assertTrue($triggered);
    }

    public function test_getMenu_shouldAddMenuItemsOfReports()
    {
        $this->loadSomePlugins();

        $items = $this->menu->getMenu();

        $this->assertNotEmpty($items);
        $this->assertGreaterThan(20, $items);
        $this->assertEquals(array('General_Actions', 'General_Visitors'), array_keys($items));
        $this->assertNotEmpty($items['General_Actions']['General_Pages']);
        $this->assertEquals('menuGetPageUrls', $items['General_Actions']['General_Pages']['_url']['action']);
    }

    private function loadSomePlugins()
    {
        PluginManager::getInstance()->loadPlugins(array(
            'Actions', 'DevicesDetection', 'CoreVisualizations', 'API', 'Morpheus'
        ));
    }
}
