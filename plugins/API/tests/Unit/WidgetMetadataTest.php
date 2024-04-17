<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Unit;

use Piwik\Category\Category;
use Piwik\Category\CategoryList;
use Piwik\Category\Subcategory;
use Piwik\Plugins\API\WidgetMetadata;
use Piwik\Report\ReportWidgetConfig;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetContainerConfig;

/**
 * @group Widget
 * @group Widgets
 * @group WidgetMetadata
 * @group WidgetMetadataTest
 */
class WidgetMetadataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WidgetMetadata
     */
    private $metadata;

    public function setUp(): void
    {
        $this->metadata = new WidgetMetadata();
    }

    public function test_buildWidgetMetadata_ShouldGenerateMetadata()
    {
        $config = $this->createWidgetConfig('Test', 'CategoryId', 'SubcategoryId');
        $list = $this->createCategoryList(array('CategoryId' => array('SubcategoryId')));
        $metadata = $this->metadata->buildWidgetMetadata($config, $list);

        $this->assertEquals(array(
            'name' => 'Test',
            'category' => array(
                'id' => 'CategoryId',
                'name' => 'CategoryId',
                'order' => 99,
                'icon' => '',
                'help' => '',
                'widget' => null,
            ),
            'subcategory' => array(
                'id' => 'SubcategoryId',
                'name' => 'SubcategoryIdName',
                'order' => 99,
                'help' => '',
            ),
            'module' => 'CoreHome',
            'action' => 'render',
            'order' => 99,
            'parameters' => array (
                'module' => 'CoreHome',
                'action' => 'render'
            ),
            'uniqueId' => 'widgetCoreHomerender',
            'isWide' => false
        ), $metadata);
    }

    public function test_buildWidgetMetadata_ShouldSetCategoryAndSubcategoryToNull_IfBothGivenButNotExistInList()
    {
        $config = $this->createWidgetConfig('Test', 'CategoryId', 'SubcategoryId');
        $list = $this->createCategoryList();
        $metadata = $this->metadata->buildWidgetMetadata($config, $list);

        $this->assertNull($metadata['category']);
        $this->assertNull($metadata['subcategory']);
    }

    public function test_buildWidgetMetadata_ShouldSetSubcategoryToNull_IfCategoryGivenInListButSubcategoryNot()
    {
        $config = $this->createWidgetConfig('Test', 'CategoryId', 'SubcategoryId');
        $list = $this->createCategoryList(array('CategoryId' => array()));
        $metadata = $this->metadata->buildWidgetMetadata($config, $list);

        $this->assertSame(array(
            'id' => 'CategoryId',
            'name' => 'CategoryId',
            'order' => 99,
            'icon' => '',
            'help' => '',
            'widget' => null,
        ), $metadata['category']);
        $this->assertNull($metadata['subcategory']);
    }

    public function test_buildWidgetMetadata_ShouldNotAddCategoryAndSubcategoryToNull_IfNoCategoryListGiven()
    {
        $config = $this->createWidgetConfig('Test', 'CategoryId', 'SubcategoryId');
        $metadata = $this->metadata->buildWidgetMetadata($config);

        $this->assertArrayNotHasKey('category', $metadata);
        $this->assertArrayNotHasKey('subcategory', $metadata);
    }

    public function test_buildWidgetMetadata_ShouldAddOptionalMiddlewareParameters()
    {
        $config = $this->createWidgetConfig('Test', 'CategoryId', 'SubcategoryId');
        $config->setMiddlewareParameters(array('module' => 'Goals', 'action' => 'hasAnyConversions'));
        $metadata = $this->metadata->buildWidgetMetadata($config);

        $this->assertSame(array('module' => 'Goals', 'action' => 'hasAnyConversions'), $metadata['middlewareParameters']);
    }

    public function test_buildWidgetMetadata_ShouldAddReportInformtion_IfReportWidgetConfigGiven()
    {
        $config = new ReportWidgetConfig();
        $config->setDefaultViewDataTable('graph');
        $metadata = $this->metadata->buildWidgetMetadata($config);

        $this->assertSame('graph', $metadata['viewDataTable']);
        $this->assertTrue($metadata['isReport']);
    }

    public function test_buildWidgetMetadata_ShouldAddContainerInformtion_IfWidgetContainerConfigGiven()
    {
        $config = new WidgetContainerConfig();
        $config->setLayout('ByDimension');
        $config->addWidgetConfig($this->createWidgetConfig('NestedName1', 'NestedCategory1', 'NestedSubcategory1'));
        $config->addWidgetConfig($this->createWidgetConfig('NestedName2', 'NestedCategory2', 'NestedSubcategory2'));
        $metadata = $this->metadata->buildWidgetMetadata($config);

        $this->assertSame('ByDimension', $metadata['layout']);
        $this->assertTrue($metadata['isContainer']);
        $this->assertCount(2, $metadata['widgets']);

        $widget1 = $metadata['widgets'][0];
        $widget2 = $metadata['widgets'][1];
        $this->assertSame(array(
            'name' => 'NestedName1',
            'category' => array (
                'id' => 'NestedCategory1',
                'name' => 'NestedCategory1',
                'order' => 99,
                'icon' => '',
                'help' => '',
                'widget' => null,
            ),
            'subcategory' => array (
                'id' => 'NestedSubcategory1',
                'name' => 'NestedSubcategory1',
                'order' => 99,
                'help' => '',
            ),
            'module' => 'CoreHome',
            'action' => 'render',
            'order' => 99,
            'parameters' => array (
                'module' => 'CoreHome',
                'action' => 'render',
            ),
            'uniqueId' => 'widgetCoreHomerender',
            'isWide' => false
        ), $widget1);
        $this->assertSame(array(
            'name' => 'NestedName2',
            'category' => array (
                'id' => 'NestedCategory2',
                'name' => 'NestedCategory2',
                'order' => 99,
                'icon' => '',
                'help' => '',
                'widget' => null,
            ),
            'subcategory' => array (
                'id' => 'NestedSubcategory2',
                'name' => 'NestedSubcategory2',
                'order' => 99,
                'help' => '',
            ),
            'module' => 'CoreHome',
            'action' => 'render',
            'order' => 99,
            'parameters' => array (
                'module' => 'CoreHome',
                'action' => 'render',
            ),
            'uniqueId' => 'widgetCoreHomerender',
            'isWide' => false
        ), $widget2);
    }

    public function test_buildWidgetMetadata_ShouldUseOverrideValues_IfSupplied()
    {
        $categoryList = $this->createCategoryList([
            'Category' => ['Subcategory'],
            'Category2' => ['Subcategory2'],
        ]);

        $config = $this->createWidgetConfig('name', 'Category', 'Subcategory');
        $metadata = $this->metadata->buildWidgetMetadata($config, $categoryList, [
            'name' => 'changed name',
            'category' => 'Category2',
            'subcategory' => 'Subcategory2',
        ]);

        $this->assertEquals([
            'name' => 'changed name',
            'category' => [
                'id' => 'Category2',
                'name' => 'Category2',
                'order' => 99,
                'icon' => '',
                'help' => '',
                'widget' => null,
            ],
            'subcategory' => [
                'id' => 'Subcategory2',
                'name' => 'Subcategory2Name',
                'order' => 99,
                'help' => '',
            ],
            'module' => 'CoreHome',
            'action' => 'render',
            'order' => 99,
            'parameters' => [
                'module' => 'CoreHome',
                'action' => 'render',
            ],
            'uniqueId' => 'widgetCoreHomerender',
            'isWide' => false,
        ], $metadata);
    }

    public function test_buildPageMetadata_ShouldAddContainerInformtion_IfWidgetContainerConfigGiven()
    {
        $config = new WidgetContainerConfig();
        $config->setLayout('ByDimension');

        $widgets = array(
            $this->createWidgetConfig('NestedName1', 'NestedCategory1', 'NestedSubcategory1'),
            $this->createWidgetConfig('NestedName2', 'NestedCategory2', 'NestedSubcategory1'),
        );

        $category = $this->createCategory('NestedCategory1');
        $subcategory = $this->createSubcategory('NestedCategory1', 'NestedSubcategory1');

        $metadata = $this->metadata->buildPageMetadata($category, $subcategory, $widgets);

        $this->assertSame(array(
            'uniqueId' => 'NestedCategory1.NestedSubcategory1',
            'category' => array (
                'id' => 'NestedCategory1',
                'name' => 'NestedCategory1',
                'order' => 99,
                'icon' => '',
                'help' => '',
                'widget' => null,
            ),
            'subcategory' => array (
                'id' => 'NestedSubcategory1',
                'name' => 'NestedSubcategory1Name',
                'order' => 99,
                'help' => '',
            ),
            'widgets' => array (
                0 => array ( // widgets should not have category / subcategory again, it's already present above
                    'name' => 'NestedName1',
                    'module' => 'CoreHome',
                    'action' => 'render',
                    'order' => 99,
                    'parameters' => array (
                        'module' => 'CoreHome',
                        'action' => 'render',
                    ),
                    'uniqueId' => 'widgetCoreHomerender',
                    'isWide' => false
                ), array (
                    'name' => 'NestedName2',
                    'module' => 'CoreHome',
                    'action' => 'render',
                    'order' => 99,
                    'parameters' => array (
                        'module' => 'CoreHome',
                        'action' => 'render',
                    ),
                    'uniqueId' => 'widgetCoreHomerender',
                    'isWide' => false
                )
            )
        ), $metadata);
    }

    private function createWidgetConfig($name, $categoryId, $subcategoryId = '')
    {
        $widgetConfig = new WidgetConfig();
        $widgetConfig->setName($name);
        $widgetConfig->setCategoryId($categoryId);
        $widgetConfig->setSubcategoryId($subcategoryId);
        $widgetConfig->setModule('CoreHome');
        $widgetConfig->setAction('render');

        return $widgetConfig;
    }

    private function createCategoryList($categories = array())
    {
        $list  = new CategoryList();

        foreach ($categories as $categoryId => $subcategoryIds) {
            $category = $this->createCategory($categoryId);
            $list->addCategory($category);

            foreach ($subcategoryIds as $subcategoryId) {
                $subcategory = $this->createSubcategory($categoryId, $subcategoryId);
                $category->addSubcategory($subcategory);
            }
        }

        return $list;
    }

    private function createSubcategory($categoryId, $subcategoryId)
    {
        $subcategory = new Subcategory();
        $subcategory->setCategoryId($categoryId);
        $subcategory->setId($subcategoryId);
        $subcategory->setName($subcategoryId . 'Name');

        return $subcategory;
    }

    private function createCategory($categoryId)
    {
        $category = new Category();
        $category->setId($categoryId);
        return $category;
    }
}
