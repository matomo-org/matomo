<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Category\Category;
use Piwik\Category\Subcategory;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Categories;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Categories
 * @group CategoriesTest
 */
class CategoriesTest extends IntegrationTestCase
{
    /**
     * @var Categories
     */
    private $categories;

    public function setUp(): void
    {
        parent::setUp();

        $_GET['idSite'] = 1;
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2015-01-01 00:00:00');
        }

        $this->categories = new Categories(StaticContainer::get('Piwik\Plugin\Manager'));
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($_GET['idSite']);
    }

    public function test_getAllCategories_shouldOnlyFindCategories()
    {
        $categories = $this->categories->getAllCategories();

        $this->assertGreaterThanOrEqual(4, count($categories));

        foreach ($categories as $category) {
            $this->assertTrue($category instanceof Category);
        }
    }

    public function test_getAllCategories_shouldHaveACategoryIdDefined()
    {
        $categories = $this->categories->getAllCategories();

        foreach ($categories as $category) {
            $this->assertNotEmpty($category->getId());
        }
    }

    public function test_getAllSubcategories_shouldOnlyFindSubcategories()
    {
        $subcategories = $this->categories->getAllSubcategories();

        $this->assertGreaterThanOrEqual(10, count($subcategories));

        foreach ($subcategories as $subcategory) {
            $this->assertTrue($subcategory instanceof Subcategory);
            $this->assertNotEmpty($subcategory->getId());
        }
    }

    public function test_getAllSubcategories_shouldHaveACategoryIdAndSubcategoryIdDefined()
    {
        $subcategories = $this->categories->getAllSubcategories();

        foreach ($subcategories as $subcategory) {
            $this->assertNotEmpty($subcategory->getId());
            $this->assertNotEmpty($subcategory->getCategoryId());
        }
    }
}
