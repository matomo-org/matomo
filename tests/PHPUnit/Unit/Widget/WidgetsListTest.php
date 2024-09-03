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
use Piwik\Widget\WidgetsList;

/**
 * @group Widget
 * @group WidgetsList
 * @group WidgetsListTest
 */
class WidgetsListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WidgetsList
     */
    private $list;

    public function setUp(): void
    {
        parent::setUp();
        $this->list = new WidgetsList();
    }

    public function testGetWidgetUniqueIdWithoutParameters()
    {
        $id = WidgetsList::getWidgetUniqueId('CoreHome', 'render');
        $this->assertSame('widgetCoreHomerender', $id);
    }

    public function testGetWidgetUniqueIdWithParameters()
    {
        $id = WidgetsList::getWidgetUniqueId('CoreHome', 'render', array('test1' => 'value', 'key' => array('test'), 'test2' => '4k3k'));
        $this->assertSame('widgetCoreHomerendertest1valuekeyArraytest24k3k', $id);
    }

    public function testGetWidgetConfigsShouldBeEmptyByDefault()
    {
        $this->assertSame(array(), $this->list->getWidgetConfigs());
    }

    public function testAddWidgetShouldAddAnyWidgetConfigs()
    {
        $this->list->addWidgetConfig($widget1 = $this->createWidget('widget1'));
        $this->list->addWidgetConfig($widget2 = $this->createWidgetContainer('widget2'));
        $this->list->addWidgetConfig($widget3 = $this->createWidget('widget3'));

        $this->assertSame(array($widget1, $widget2, $widget3), $this->list->getWidgetConfigs());
    }

    public function testAddWidgetsShouldAddAnyWidgetConfigs()
    {
        $this->list->addWidgetConfigs(array(
            $widget1 = $this->createWidget('widget1'),
            $widget2 = $this->createWidgetContainer('widget2'),
            $widget3 = $this->createWidget('widget3'),
        ));

        $this->assertSame(array($widget1, $widget2, $widget3), $this->list->getWidgetConfigs());
    }

    public function testAddToContainerWidgetShouldAddWidgetToContainerImmediatelyIfContainerAlreadyExistsInList()
    {
        $this->list->addWidgetConfigs(array(
            $widget1 = $this->createWidget('widget1'),
            $widget2 = $this->createWidgetContainer('widget2')->setId('testId'),
            $widget3 = $this->createWidget('widget3'),
        ));

        $this->list->addToContainerWidget('testId', $widget4 = $this->createWidget('widget4'));

        $this->assertSame(array($widget4), $widget2->getWidgetConfigs());

        // widget4 should not be added to this widgetConfigs
        $this->assertSame(array($widget1, $widget2, $widget3), $this->list->getWidgetConfigs());
    }

    public function testAddToContainerWidgetShouldAddWidgetToContainerAsSoonAsContainerAddedIfContainerNotAlreadyExistsInList()
    {
        $this->list->addToContainerWidget('testId', $widget4 = $this->createWidget('widget4'));

        $this->list->addWidgetConfigs(array(
            $widget1 = $this->createWidget('widget1'),
            $widget2 = $this->createWidgetContainer('widget2')->setId('testId'),
            $widget3 = $this->createWidget('widget3'),
        ));

        $this->assertSame(array($widget4), $widget2->getWidgetConfigs());

        // widget4 should not be added to this widgetConfigs
        $this->assertSame(array($widget1, $widget2, $widget3), $this->list->getWidgetConfigs());
    }

    /**
     * @dataProvider getWidgetsToRemove
     */
    public function testRemove($categoryId, $name, $expectedWidgetNamesInList)
    {
        $this->list->addWidgetConfigs(array(
            $widget1 = $this->createWidget('widget1')->setCategoryId('Visits'),
            $widget2 = $this->createWidgetContainer('widget2')->setCategoryId('Actions'),
            $widget3 = $this->createWidget('widget3')->setCategoryId('Visits'),
        ));

        $this->list->remove($categoryId, $name);

        $names = array();
        foreach ($this->list->getWidgetConfigs() as $config) {
            $names[] = $config->getName();
        }

        $this->assertSame($expectedWidgetNamesInList, $names);
    }

    public function getWidgetsToRemove()
    {
        return array(
            array('Visits', false, array('widget2')),
            array('Visits', 'widget3', array('widget1', 'widget2')),
            array('Actions', false, array('widget1', 'widget3')),
            array('Actions', 'widget2', array('widget1', 'widget3')),
            array('Actions', 'notExist', array('widget1', 'widget2', 'widget3')),
            array('NotExiSt', false, array('widget1', 'widget2', 'widget3')),
        );
    }

    /**
     * @dataProvider getWidgetsDefined
     */
    public function testIsDefined($module, $action, $exists)
    {
        $this->list->addWidgetConfigs(array(
            $widget1 = $this->createWidget('widget1')->setModule('CoreHome')->setAction('renderMe'),
            $widget2 = $this->createWidgetContainer('widget2')->setModule('CoreHome')->setAction('renderContainer'),
            $widget3 = $this->createWidget('widget3')->setModule('Actions')->setAction('index'),
        ));

        $this->assertSame($exists, $this->list->isDefined($module, $action));
    }

    public function getWidgetsDefined()
    {
        return array(
            array('CoreHome', 'renderMe', $isDefined = true),
            array('CoreHome', 'renderContainer', $isDefined = true),
            array('Actions', 'index', $isDefined = true),
            array('Actions', 'renderMe', $isDefined = false),
            array('AnyThiNg', 'renderMe', $isDefined = false),
            array('CoreHome', 'index', $isDefined = false)
        );
    }

    private function createWidget($name)
    {
        $config = new WidgetConfig();
        $config->setName($name);

        return $config;
    }

    private function createWidgetContainer($name)
    {
        $config = new WidgetContainerConfig();
        $config->setName($name);

        return $config;
    }
}
