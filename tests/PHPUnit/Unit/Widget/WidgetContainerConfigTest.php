<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function test_id_set_get()
    {
        $this->config->setId('testId');

        $this->assertSame('testId', $this->config->getId());
    }

    public function test_name_set_get()
    {
        $this->config->setName('testName');

        $this->assertSame('testName', $this->config->getName());
    }

    public function test_getName_shouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getName());
    }

    public function test_layout_set_get()
    {
        $this->config->setLayout('ByDimension');

        $this->assertSame('ByDimension', $this->config->getLayout());
    }

    public function test_getLayout_shouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getLayout());
    }

    public function test_categoryId_set_get()
    {
        $this->config->setCategoryId('testCat');

        $this->assertSame('testCat', $this->config->getCategoryId());
    }

    public function test_getCategoryId_shouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getCategoryId());
    }

    public function test_subcategoryId_set_get()
    {
        $this->config->setSubcategoryId('testsubcat');

        $this->assertSame('testsubcat', $this->config->getSubcategoryId());
    }

    public function test_getSubcategoryId_shouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->config->getSubcategoryId());
    }

    public function test_module_set_get()
    {
        $this->config->setModule('CoreHome');

        $this->assertSame('CoreHome', $this->config->getModule());
    }

    public function test_getModule_shouldBeTheModuleToRenderItByDefault()
    {
        $this->assertSame('CoreHome', $this->config->getModule());
    }

    public function test_action_set_get()
    {
        $this->config->setAction('get');

        $this->assertSame('get', $this->config->getAction());
    }

    public function test_getAction_shouldBeTheActionToRenderItByDefault()
    {
        $this->assertSame('renderWidgetContainer', $this->config->getAction());
    }

    public function test_order_set_get()
    {
        $this->config->setOrder(99);
        $this->assertSame(99, $this->config->getOrder());

        $this->config->setOrder('98');
        $this->assertSame(98, $this->config->getOrder());
    }

    public function test_getOrder_shouldReturnADefaultValue()
    {
        $this->assertSame(99, $this->config->getOrder());
    }

    public function test_setMiddlewareParameters_set_get()
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

    public function test_getMiddlewareParameters_shouldReturnADefaultValue()
    {
        $this->assertSame(array(), $this->config->getMiddlewareParameters());
    }

    public function test_getParameters_ShouldAddModuleAndAction()
    {
        $this->assertSame(array(
            'module' => 'CoreHome',
            'action' => 'renderWidgetContainer',
            'containerId' => $this->id
        ), $this->config->getParameters());
    }

    public function test_getParameters_ShouldNotBePossibleToOverwriteModuleAndAction()
    {
        $this->config->setParameters(array('module' => 'Actions', 'action' => 'index', 'containerId' => 'test'));

        $this->assertSame(array(
            'module' => 'CoreHome',
            'action' => 'renderWidgetContainer',
            'containerId' => $this->id
        ), $this->config->getParameters());
    }

    public function test_addParameters_ShouldAddMoreParams()
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

    public function test_setParameters_ShouldOverwriteAnyExistingParameters()
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

    public function test_shouldBeEnabledByDefault()
    {
        $this->assertTrue($this->config->isEnabled());
    }

    public function test_enable_disable()
    {
        $this->config->disable();
        $this->assertFalse($this->config->isEnabled());
        $this->config->enable();
        $this->assertTrue($this->config->isEnabled());
    }

    public function test_setIsEnabled()
    {
        $this->config->setIsEnabled(false);
        $this->assertFalse($this->config->isEnabled());
        $this->config->setIsEnabled(true);
        $this->assertTrue($this->config->isEnabled());
    }

    public function test_checkIsEnabled_shouldNotThrowException_IfEnabled()
    {
        self::expectNotToPerformAssertions();

        $this->config->enable();
        $this->config->checkIsEnabled();
    }

    public function test_checkIsEnabled_shouldThrowException_IfDisabled()
    {
        $this->expectException(\Exception::class);

        $this->config->disable();
        $this->config->checkIsEnabled();
    }

    public function test_shouldNotBeWidgetizable_ByDefault()
    {
        $this->assertFalse($this->config->isWidgetizeable());
    }

    public function test_widgetizeable()
    {
        $this->config->setIsNotWidgetizable();
        $this->assertFalse($this->config->isWidgetizeable());
        $this->config->setIsWidgetizable();
        $this->assertTrue($this->config->isWidgetizeable());
    }

    public function test_getUniqueId_shouldIncludeContainerId()
    {
        $this->assertSame('widgetMyTestContainer', $this->config->getUniqueId());
    }

    public function test_getUniqueId_withParameters()
    {
        $this->config->addParameters(array('viewDataTable' => 'table', 'forceView' => '1', 'mtest' => array('test')));
        $this->assertSame('widgetMyTestContainerviewDataTabletableforceView1mtestArray', $this->config->getUniqueId());
    }

    public function test_getWidgetConfigs_shouldBeEmptyByDefault()
    {
        $this->assertSame(array(), $this->config->getWidgetConfigs());
    }

    public function test_widgetConfigs_shouldBeEmptyByDefault()
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

    public function test_setWidgetConfigs_canOverwriteWidgets()
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
