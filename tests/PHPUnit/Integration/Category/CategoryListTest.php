<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Category;

use Piwik\Category\Category;
use Piwik\Category\CategoryList;
use Piwik\Category\Subcategory;
use Piwik\Container\StaticContainer;
use Piwik\Tests\Framework\Mock\Category\Categories;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Framework/Mock/Category/Categeories.php';

/**
 * @group Category
 * @group CategoryList
 * @group CategoryListTest
 */
class CategoryListTest extends IntegrationTestCase
{
    /**
     * @var Categories
     */
    private $categories;

    public function testGetAllCategoriesWithSubcategories_shouldFindCategories()
    {
        $list = CategoryList::get();

        $this->assertSame(array(
            'General_Actions',
            'General_Visitors',
            'Dashboard_Dashboard',
            'General_MultiSitesSummary',
            'Referrers_Referrers',
            'Goals_Goals',
            'Goals_Ecommerce',
            'Events_Events',
            'UserCountry_VisitLocation',
            'Live!',
            'CustomVariables_CustomVariables',
            'ExampleUI_UiFramework'
        ), array_keys($list->getCategories()));
    }

    public function testGetAllCategoriesWithSubcategories_shouldFindSubcategories()
    {
        $list = CategoryList::get();

        $this->assertTrue(5 < count($list->getCategory('General_Actions')->getSubcategories()));
        $this->assertTrue(5 < count($list->getCategory('General_Visitors')->getSubcategories()));
        $this->assertTrue($list->getCategory('General_Actions')->hasSubcategory('General_Pages'));
    }

    public function test_getAllCategoriesWithSubcategories_shouldMergeCategoriesAndSubcategories()
    {
        $this->categories->setCategories(array(
            $this->createCategory('General_Visits'),
            $this->createCategory('General_Actions'),
            $this->createCategory('Goals_Goals'),
            $this->createCategory('Goals_Ecommerce'),
            $this->createCategory('Referrers_Referrers'),
        ));
        $this->categories->setSubcategories(array(
            $subcat1 = $this->createSubcategory('General_Actions', 'General_Pages'),
            $subcat2 = $this->createSubcategory('Goals_Goals', 'General_Overview'),
            $subcat3 = $this->createSubcategory('General_Actions', 'Actions_Downloads'),
            $subcat4 = $this->createSubcategory('General_AnyThingNotExist', 'General_MySubcategoryId'),
            $subcat5 = $this->createSubcategory('General_Visits', 'Visits'),
            $subcat6 = $this->createSubcategory('Goals_Goals', '4'),
            $subcat7 = $this->createSubcategory('General_Visits', 'General_Engagement'),
            $subcat8 = $this->createSubcategory('Goals_Ecommerce', 'General_Overview'),
        ));

        /** @var CategoryList $list */
        $list = CategoryList::get();

        $categoryNames = array(
            'General_Visits',
            'General_Actions',
            'Goals_Goals',
            'Goals_Ecommerce',
            'Referrers_Referrers',
            'General_AnyThingNotExist' // should be created dynamically as none exists
        );
        $this->assertSame($categoryNames, array_keys($list->getCategories()));

        $this->assertSubcategoriesInCategoryEquals(array($subcat5, $subcat7), 'General_Visits', $list);
        $this->assertSubcategoriesInCategoryEquals(array($subcat1, $subcat3), 'General_Actions', $list);
        $this->assertSubcategoriesInCategoryEquals(array($subcat2, $subcat6), 'Goals_Goals', $list);
        $this->assertSubcategoriesInCategoryEquals(array($subcat8), 'Goals_Ecommerce', $list);
        $this->assertSubcategoriesInCategoryEquals(array(), 'Referrers_Referrers', $list);
        $this->assertSubcategoriesInCategoryEquals(array($subcat4), 'General_AnyThingNotExist', $list);

        // make sure id was actually set
        $this->assertSame('General_AnyThingNotExist', $list->getCategory('General_AnyThingNotExist')->getId());
    }

    private function assertSubcategoriesInCategoryEquals($expectedSubcategories, $categoryId, CategoryList $list)
    {
        $this->assertSame($expectedSubcategories, $list->getCategory($categoryId)->getSubcategories());
    }

    private function createCategory($categoryId)
    {
        $config = new Category();
        $config->setId($categoryId);

        return $config;
    }

    private function createSubcategory($categoryId, $subcategoryId)
    {
        $config = new Subcategory();
        $config->setId($subcategoryId);
        $config->setCategoryId($categoryId);

        return $config;
    }

    public function provideContainerConfig()
    {
        $this->categories = new Categories(StaticContainer::get('Piwik\Plugin\Manager'));

        return array(
            'Piwik\Plugin\Categories' => $this->categories
        );
    }
}
