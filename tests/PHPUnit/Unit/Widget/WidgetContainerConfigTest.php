<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Widget;

use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetContainerConfig;

/**
 * @group Widget
 * @group WidgetContainerConfig
 * @group WidgetContainerConfigTest
 */
class WidgetContainerConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WidgetContainerConfig
     */
    private $config;

    private $id = 'MyTestContainer';

    public function setUp(): void
    {
        parent::setUp();
        $this->config = new WidgetContainerConfig();
        $this->config->setId($this->id);
    }

    public function testIdSetGet()
    {
        $this->config->setId('testId');

        $this->assertSame('testId', $this->config->getId());
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

    public function testLayoutSetGet()
    {
        $this->config->setLayout('ByDimension');

        $this->assertSame('ByDimension', $this->config->getLayout());
    }

    public function testGetLayoutShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getLayout());
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

    public function testGetModuleShouldBeTheModuleToRenderItByDefault()
    {
        $this->assertSame('CoreHome', $this->config->getModule());
    }

    public function testActionSetGet()
    {
        $this->config->setAction('get');

        $this->assertSame('get', $this->config->getAction());
    }

    public function testGetActionShouldBeTheActionToRenderItByDefault()
    {
        $this->assertSame('renderWidgetContainer', $this->config->getAction());
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
        $this->assertSame(array(
            'module' => 'CoreHome',
            'action' => 'renderWidgetContainer',
            'containerId' => $this->id
        ), $this->config->getParameters());
    }

    public function testGetParametersShouldNotBePossibleToOverwriteModuleAndAction()
    {
        $this->config->setParameters(array('module' => 'Actions', 'action' => 'index', 'containerId' => 'test'));

        $this->assertSame(array(
            'module' => 'CoreHome',
            'action' => 'renderWidgetContainer',
            'containerId' => $this->id
        ), $this->config->getParameters());
    }

    public function testAddParametersShouldAddMoreParams()
    {
        $this->config->addParameters(array('test' => '1')); // should be removed by setParameters
        $this->config->addParameters(array('forceView' => '1'));
        $this->config->addParameters(array('test' => '3'));

        $this->assertSame(array(
            'module' => 'CoreHome',
            'action' => 'renderWidgetContainer',
            'test' => '3',
            'forceView' => '1',
            'containerId' => $this->id
        ), $this->config->getParameters());
    }

    public function testSetParametersShouldOverwriteAnyExistingParameters()
    {
        $this->config->addParameters(array('test' => '1')); // should be removed by setParameters
        $this->config->setParameters(array('forceView' => '1'));

        $this->assertSame(array(
            'module' => 'CoreHome',
            'action' => 'renderWidgetContainer',
            'forceView' => '1',
            'containerId' => $this->id
        ), $this->config->getParameters());
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

    public function testShouldNotBeWidgetizableByDefault()
    {
        $this->assertFalse($this->config->isWidgetizeable());
    }

    public function testWidgetizeable()
    {
        $this->config->setIsNotWidgetizable();
        $this->assertFalse($this->config->isWidgetizeable());
        $this->config->setIsWidgetizable();
        $this->assertTrue($this->config->isWidgetizeable());
    }

    public function testGetUniqueIdShouldIncludeContainerId()
    {
        $this->assertSame('widgetMyTestContainer', $this->config->getUniqueId());
    }

    public function testGetUniqueIdWithParameters()
    {
        $this->config->addParameters(array('viewDataTable' => 'table', 'forceView' => '1', 'mtest' => array('test')));
        $this->assertSame('widgetMyTestContainerviewDataTabletableforceView1mtestArray', $this->config->getUniqueId());
    }

    public function testGetWidgetConfigsShouldBeEmptyByDefault()
    {
        $this->assertSame(array(), $this->config->getWidgetConfigs());
    }

    public function testWidgetConfigsShouldBeEmptyByDefault()
    {
        $this->config->addWidgetConfig($widget1 = $this->createWidgetConfig('widget1'));
        $this->config->addWidgetConfig($widget2 = $this->createWidgetConfig('widget2'));
        $this->config->addWidgetConfig($widget3 = $this->createWidgetConfig('widget3'));
        $this->config->addWidgetConfig($widget4 = new WidgetContainerConfig()); // should be possible to add container to a container
        $this->assertSame(array(
            $widget1,
            $widget2,
            $widget3,
            $widget4
        ), $this->config->getWidgetConfigs());
    }

    public function testSetWidgetConfigsCanOverwriteWidgets()
    {
        $this->assertSame(array(), $this->config->getWidgetConfigs());

        $this->config->addWidgetConfig($widget1 = $this->createWidgetConfig('widget1'));
        $this->config->addWidgetConfig($widget2 = $this->createWidgetConfig('widget2'));
        $this->assertSame(array($widget1,$widget2), $this->config->getWidgetConfigs());

        $widget3 = $this->createWidgetConfig('widget3');
        $widget4 = new WidgetContainerConfig();
        $this->config->setWidgetConfigs(array($widget2, $widget3, $widget4));
        $this->assertSame(array(
            $widget2,
            $widget3,
            $widget4
        ), $this->config->getWidgetConfigs());
    }

    private function createWidgetConfig($widgetName)
    {
        $config = new WidgetConfig();
        $config->setName($widgetName);

        return $config;
    }
}
