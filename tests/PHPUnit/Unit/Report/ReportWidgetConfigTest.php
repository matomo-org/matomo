<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Report;

use Piwik\Report\ReportWidgetConfig;

/**
 * @group Widget
 * @group Report
 * @group ReportWidgetConfig
 * @group ReportWidgetConfigTest
 */
class ReportWidgetConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReportWidgetConfig
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->config = new ReportWidgetConfig();
    }

    public function testGetViewDataTableByDefaultThereShouldBeNoDefaultView()
    {
        $this->assertNull($this->config->getViewDataTable());
    }

    public function testSetDefaultViewDataTable()
    {
        $this->config->setDefaultViewDataTable('table');

        $this->assertSame('table', $this->config->getViewDataTable());
        $this->assertFalse($this->config->isViewDataTableForced());
    }

    public function testForceViewDataTable()
    {
        $this->config->forceViewDataTable('table');

        $this->assertSame('table', $this->config->getViewDataTable());
        $this->assertTrue($this->config->isViewDataTableForced());
    }

    public function testNameSetGet()
    {
        $this->config->setName('testName');

        $this->assertSame('testName', $this->config->getName());
    }

    public function testGetNameShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getName());
    }

    public function testCategoryIdSetGet()
    {
        $this->config->setCategoryId('testCat');

        $this->assertSame('testCat', $this->config->getCategoryId());
    }

    public function testGetCategoryIdShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getCategoryId());
    }

    public function testSubcategoryIdSetGet()
    {
        $this->config->setSubcategoryId('testsubcat');

        $this->assertSame('testsubcat', $this->config->getSubcategoryId());
    }

    public function testGetSubcategoryIdShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getSubcategoryId());
    }

    public function testModuleSetGet()
    {
        $this->config->setModule('CoreHome');

        $this->assertSame('CoreHome', $this->config->getModule());
    }

    public function testGetModuleShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getModule());
    }

    public function testActionSetGet()
    {
        $this->config->setAction('get');

        $this->assertSame('get', $this->config->getAction());
    }

    public function testGetActionShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getAction());
    }

    public function testOrderSetGet()
    {
        $this->config->setOrder(99);
        $this->assertSame(99, $this->config->getOrder());

        $this->config->setOrder('98');
        $this->assertSame(98, $this->config->getOrder());
    }

    public function testGetOrderShouldReturnADefaultValue()
    {
        $this->assertSame(99, $this->config->getOrder());
    }

    public function testSetMiddlewareParametersSetGet()
    {
        $this->config->setMiddlewareParameters(array(
            'module' => 'Goals',
            'action' => 'hasConversions'
        ));

        $this->assertSame(array(
            'module' => 'Goals',
            'action' => 'hasConversions'
        ), $this->config->getMiddlewareParameters());
    }

    public function testGetMiddlewareParametersShouldReturnADefaultValue()
    {
        $this->assertSame(array(), $this->config->getMiddlewareParameters());
    }

    public function testGetParametersShouldAddModuleAndAction()
    {
        $this->setModuleAndAction();
        $this->assertSame(array('module' => 'CoreHome', 'action' => 'renderMe'), $this->config->getParameters());
    }

    public function testGetParametersShouldNotBePossibleToOverwriteModuleAndAction()
    {
        $this->setModuleAndAction();
        $this->config->setParameters(array('module' => 'Actions', 'action' => 'index'));

        $this->assertSame(array('module' => 'CoreHome', 'action' => 'renderMe'), $this->config->getParameters());
    }

    public function testGetParametersShouldNotReturnViewDataTableIfItIsNotForced()
    {
        $this->setModuleAndAction();
        $this->config->setDefaultViewDataTable('graph');

        $this->assertSame(array('module' => 'CoreHome', 'action' => 'renderMe'), $this->config->getParameters());
    }

    public function testGetParametersShouldForceViewDataTableIfSet()
    {
        $this->setModuleAndAction();
        $this->config->forceViewDataTable('graph');

        $this->assertSame(array('forceView' => '1', 'viewDataTable' => 'graph', 'module' => 'CoreHome', 'action' => 'renderMe'), $this->config->getParameters());
    }

    public function testAddParametersShouldAddMoreParams()
    {
        $this->setModuleAndAction();
        $this->config->addParameters(array('test' => '1')); // should be removed by setParameters
        $this->config->addParameters(array('forceView' => '1'));
        $this->config->addParameters(array('test' => '3'));

        $this->assertSame(array('module' => 'CoreHome', 'action' => 'renderMe', 'test' => '3', 'forceView' => '1'), $this->config->getParameters());
    }

    public function testSetParametersShouldOverwriteAnyExistingParameters()
    {
        $this->setModuleAndAction();
        $this->config->addParameters(array('test' => '1')); // should be removed by setParameters
        $this->config->setParameters(array('forceView' => '1'));

        $this->assertSame(array('module' => 'CoreHome', 'action' => 'renderMe', 'forceView' => '1'), $this->config->getParameters());
    }

    public function testShouldBeEnabledByDefault()
    {
        $this->assertTrue($this->config->isEnabled());
    }

    public function testEnableDisable()
    {
        $this->config->disable();
        $this->assertFalse($this->config->isEnabled());
        $this->config->enable();
        $this->assertTrue($this->config->isEnabled());
    }

    public function testSetIsEnabled()
    {
        $this->config->setIsEnabled(false);
        $this->assertFalse($this->config->isEnabled());
        $this->config->setIsEnabled(true);
        $this->assertTrue($this->config->isEnabled());
    }

    public function testCheckIsEnabledShouldNotThrowExceptionIfEnabled()
    {
        self::expectNotToPerformAssertions();

        $this->config->enable();
        $this->config->checkIsEnabled();
    }

    public function testCheckIsEnabledShouldThrowExceptionIfDisabled()
    {
        $this->expectException(\Exception::class);

        $this->config->disable();
        $this->config->checkIsEnabled();
    }

    public function testShouldBeWidgetizableByDefault()
    {
        $this->assertTrue($this->config->isWidgetizeable());
    }

    public function testWidgetizeable()
    {
        $this->config->setIsNotWidgetizable();
        $this->assertFalse($this->config->isWidgetizeable());
        $this->config->setIsWidgetizable();
        $this->assertTrue($this->config->isWidgetizeable());
    }

    public function testGetUniqueIdWithNoParameters()
    {
        $this->setModuleAndAction();
        $this->assertSame('widgetCoreHomerenderMe', $this->config->getUniqueId());
    }

    public function testGetUniqueIdWithParameters()
    {
        $this->setModuleAndAction();
        $this->config->addParameters(array('viewDataTable' => 'table', 'forceView' => '1', 'mtest' => array('test')));
        $this->assertSame('widgetCoreHomerenderMeviewDataTabletableforceView1mtestArray', $this->config->getUniqueId());
    }

    private function setModuleAndAction()
    {
        $this->config->setModule('CoreHome');
        $this->config->setAction('renderMe');
    }
}
