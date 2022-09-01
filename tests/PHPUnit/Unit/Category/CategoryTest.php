<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Category;

use Piwik\Category\Category;
use Piwik\Category\Subcategory;

/**
 * @group Category
 * @group CategoryTest
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Category
     */
    private $category;

    public function setUp(): void
    {
        parent::setUp();
        $this->category = new Category();
    }

    public function test_order_set_get()
    {
        $this->category->setOrder(99);
        $this->assertSame(99, $this->category->getOrder());

        $this->category->setOrder('98');
        $this->assertSame(98, $this->category->getOrder());
    }

    public function test_getOrder_shouldReturnADefaultValue()
    {
        $this->assertSame(99, $this->category->getOrder());
    }

    public function test_id_set_get()
    {
        $this->category->setId('myCustomId');
        $this->assertSame('myCustomId', $this->category->getId());
    }

    public function test_getId_shouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->category->getId());
    }

    public function test_getDisplayName_shouldUseId()
    {
        $this->category->setId('myCustomId');
        $this->assertSame('myCustomId', $this->category->getDisplayName());
    }

    public function test_getSubcategories_ShouldReturnAnEmptyArray_ByDefault()
    {
        $this->assertSame(array(), $this->category->getSubcategories());
    }

    public function test_addSubcategory_ShouldActuallyAddAndReturnSubcategories()
    {
        $subcategory1 = $this->createSubcategory('id1', 'name1');
        $subcategory2 = $this->createSubcategory('id2', 'name2');

        $this->category->addSubcategory($subcategory1);
        $this->category->addSubcategory($subcategory2);

        $this->assertSame(array($subcategory1, $subcategory2), $this->category->getSubcategories());
    }

    public function test_addSubcategory_ShouldThrowException_WhenAddingSubcategoryWithSameIdTwice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Subcategory id1 already exists');

        $subcategory1 = $this->createSubcategory('id1', 'name1');
        $subcategory2 = $this->createSubcategory('id1', 'name2');

        $this->category->addSubcategory($subcategory1);
        $this->category->addSubcategory($subcategory2);
    }

    public function test_hasSubcategories_ShouldDetectIfSubcategoriesArePresent()
    {
        $this->assertFalse($this->category->hasSubCategories());
        $this->addSubcategories(array('myid' => 'myname'));
        $this->assertTrue($this->category->hasSubCategories());
    }

    public function test_getSubcategory_ShouldNotFindASubCategoryById_IfSuchCategoryDoesNotExist()
    {
        $this->assertNull($this->category->getSubcategory('myid'));
    }

    public function test_getSubcategory_ShouldFindAnExistingSubCategoryById()
    {
        $this->addSubcategories(array('myid' => 'myname', 'myid2' => 'myname2'));

        $subcategory = $this->category->getSubcategory('myid2');
        $this->assertTrue($subcategory instanceof Subcategory);
        $this->assertSame('myname2', $subcategory->getName());
    }

    public function test_getSubcategory_ShouldNotFindASubcategoryByName()
    {
        $this->addSubcategories(array('myid' => 'myname'));

        $this->assertNull($this->category->getSubcategory('myname'));
    }

    public function test_hasSubcategory_ShouldActuallyAddTheConfig()
    {
        $this->assertFalse($this->category->hasSubcategory('myid2'));

        $this->addSubcategories(array('myid' => 'myname', 'myid2' => 'myname2'));

        $this->assertTrue($this->category->hasSubcategory('myid2'));
        $this->assertFalse($this->category->hasSubcategory('myname'));
        $this->assertFalse($this->category->hasSubcategory('myname2'));
        $this->assertFalse($this->category->hasSubcategory('mySomething'));
    }

    private function addSubcategories($subcategories)
    {
        foreach ($subcategories as $id => $name) {
            $this->category->addSubcategory($this->createSubcategory($id, $name));
        }
    }

    private function createSubcategory($subcategoryId, $subcategoryName)
    {
        $config = new Subcategory();
        $config->setId($subcategoryId);
        $config->setName($subcategoryName);

        return $config;
    }
}
