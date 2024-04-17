<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\Plugin;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugin\ComponentFactory;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugin\Report;

/**
 * @group Core
 */
class ComponentFactoryTest extends TestCase
{
    const REPORT_CLASS_NAME = 'Piwik\\Plugin\\Report';

    public function setUp(): void
    {
        parent::setUp();

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

    public function test_getComponentIf_shouldNotFindAComponentIfComponentExistsButPluginIsNotLoaded()
    {
        $this->unloadAllPlugins();

        $report = ComponentFactory::getComponentIf(self::REPORT_CLASS_NAME, 'ExampleReport', function (Report $report) {
            return $report->getAction() == 'getExampleReport';
        });

        $this->assertNull($report);
    }

    public function test_getComponentIf_shouldFindAComponent_ThatExists()
    {
        $this->loadExampleReportPlugin();

        $report = ComponentFactory::getComponentIf(self::REPORT_CLASS_NAME, 'ExampleReport', function (Report $report) {
            return $report->getAction() == 'getExampleReport';
        });

        $this->assertInstanceOf('Piwik\Plugins\ExampleReport\Reports\GetExampleReport', $report);
    }

    public function test_getComponentIf_shouldNotFindAComponent_IfPluginIsActivatedButComponentNotExists()
    {
        $this->loadExampleReportPlugin();

        $report = ComponentFactory::getComponentIf(self::REPORT_CLASS_NAME, 'ExampleReport', function (Report $report) {
            return false;
        });

        $this->assertNull($report);
    }

    public function test_getComponentIf_shouldNotFindAComponent_IfPluginIsLoadedButNotActivated()
    {
        PluginManager::getInstance()->loadPlugin('ExampleReport');

        $report = ComponentFactory::getComponentIf(self::REPORT_CLASS_NAME, 'ExampleReport', function (Report $report) {
            return $report->getAction() == 'getExampleReport';
        });

        $this->assertNull($report);
    }

    public function test_getComponentIf_shouldSearchThroughAllPlugins_IfNoPluginNameIsSupplied()
    {
        PluginManager::getInstance()->loadPlugins(array('ExampleReport', 'Referrers'));

        $reports = array();
        ComponentFactory::getComponentIf(self::REPORT_CLASS_NAME, null, function (Report $report) use (&$reports) {
            $reports[] = $report;
        });

        $this->assertGreaterThan(1, count($reports));
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
