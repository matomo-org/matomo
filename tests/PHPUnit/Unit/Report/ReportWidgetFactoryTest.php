<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Report;

use Piwik\Plugin\Report;
use Piwik\Report\ReportWidgetConfig;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetContainerConfig;

class GetBasicReport extends Report
{
    protected function init()
    {
        parent::init();

        $this->name = 'Report_MyCustomReportName';
        $this->order  = 20;
        $this->module = 'TestPlugin';
        $this->action = 'getBasicReport';
        $this->categoryId = 'Goals_Goals';
        $this->subcategoryId = 'General_Overview';
        $this->actionToLoadSubTables = 'invalidReport';
        $this->parameters = array('idGoal' => '1');
    }

    public function getDefaultTypeViewDataTable()
    {
        return 'graph';
    }
}

/**
 * @group Widget
 * @group Report
 * @group ReportWidgetFactory
 * @group ReportWidgetFactoryTest
 */
class ReportWidgetFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReportWidgetFactory
     */
    private $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new ReportWidgetFactory(new GetBasicReport());
    }

    public function test_createContainerWidget_ShouldCreateAContainerBasedOnReportWithGivenId()
    {
        $config = $this->factory->createContainerWidget('myId');

        $this->assertTrue($config instanceof WidgetContainerConfig);
        $this->assertSame('myId', $config->getId());
        $this->assertSame('Goals_Goals', $config->getCategoryId());
        $this->assertSame('General_Overview', $config->getSubcategoryId());
        $this->assertSame(100 + 20, $config->getOrder());
    }

    public function test_createWidget_ShouldCreateAContainerBasedOnReportWithGivenId()
    {
        $config = $this->factory->createWidget();

        $this->assertTrue($config instanceof ReportWidgetConfig);
        $this->assertSame('Report_MyCustomReportName', $config->getName());
        $this->assertSame('Goals_Goals', $config->getCategoryId());
        $this->assertSame('General_Overview', $config->getSubcategoryId());
        $this->assertSame('graph', $config->getViewDataTable());
        $this->assertSame(100 + 20, $config->getOrder());
        $this->assertSame('TestPlugin', $config->getModule());
        $this->assertSame('getBasicReport', $config->getAction());

        $this->assertSame(array(
            'module' => 'TestPlugin',
            'action' => 'getBasicReport',
            'idGoal' => '1'
        ), $config->getParameters());
    }

    public function test_createCustomWidget_ShouldCreateAContainerBasedOnReportWithGivenId()
    {
        $config = $this->factory->createCustomWidget('customAction');

        $this->assertTrue($config instanceof ReportWidgetConfig);
        $this->assertSame('Report_MyCustomReportName', $config->getName());
        $this->assertSame('Goals_Goals', $config->getCategoryId());
        $this->assertSame('General_Overview', $config->getSubcategoryId());
        $this->assertNull($config->getViewDataTable());
        $this->assertSame(100 + 20, $config->getOrder());
        $this->assertSame('TestPlugin', $config->getModule());
        $this->assertSame('customAction', $config->getAction());

        $this->assertSame(array(
            'module' => 'TestPlugin',
            'action' => 'customAction',
            'idGoal' => '1'
        ), $config->getParameters());
    }


}
