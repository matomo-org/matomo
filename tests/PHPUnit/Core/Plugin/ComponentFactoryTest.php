<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Core\Plugin;

use PHPUnit_Framework_TestCase;
use Piwik\Config;
use Piwik\Plugin\ComponentFactory;
use Piwik\Plugin\Manager as PluginManager;

/**
 * @group Core
 */
class ComponentFactoryTest extends PHPUnit_Framework_TestCase
{
    const REPORT_CLASS_NAME = 'Piwik\\Plugin\\Report';

    public function setUp()
    {
        parent::setUp();

        Config::getInstance()->setTestEnvironment();
        Config::getInstance()->Plugins['Plugins'] = array();

        $this->unloadAllPlugins();
    }

    public function test_factory_shouldNotFindAComponentIfComponentExistsButPluginIsNotLoaded()
    {
        $this->unloadAllPlugins();

        $report = ComponentFactory::factory('ExampleReport', 'GetExampleReport', self::REPORT_CLASS_NAME);

        $this->assertNull($report);
    }

    public function test_factory_shouldFindAComponent_ThatExists()
    {
        $this->loadExampleReportPlugin();

        $module = 'ExampleReport';
        $action = 'GetExampleReport';

        $report = ComponentFactory::factory($module, $action, self::REPORT_CLASS_NAME);

        $this->assertInstanceOf('Piwik\Plugins\ExampleReport\Reports\GetExampleReport', $report);
    }

    public function test_factory_shouldNotFindAComponent_IfPluginIsActivatedButComponentNotExists()
    {
        $this->loadExampleReportPlugin();

        $module = 'ExampleReport';
        $action = 'NotExistingReport';

        $report = ComponentFactory::factory($module, $action, self::REPORT_CLASS_NAME);

        $this->assertNull($report);
    }

    public function test_factory_shouldNotFindAComponent_IfPluginIsLoadedButNotActivated()
    {
        PluginManager::getInstance()->loadPlugin('ExampleReport');

        $module = 'ExampleReport';
        $action = 'GetExampleReport';

        $report = ComponentFactory::factory($module, $action, self::REPORT_CLASS_NAME);

        $this->assertNull($report);
    }

    private function unloadAllPlugins()
    {
        PluginManager::getInstance()->loadPlugins(array());
    }

    private function loadExampleReportPlugin()
    {
        PluginManager::getInstance()->loadPlugins(array('ExampleReport'));
    }
}