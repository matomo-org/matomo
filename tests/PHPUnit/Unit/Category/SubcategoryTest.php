<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Category;

use Piwik\Category\Subcategory;

/**
 * @group Category
 * @group Subcategory
 * @group SubcategoryTest
 */
class SubcategoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Subcategory
     */
    private $subcategory;

    public function setUp(): void
    {
        parent::setUp();
        $this->subcategory = new Subcategory();
    }

    public function test_categoryId_set_get()
    {
        $this->subcategory->setCategoryId('testCategory');

        $this->assertSame('testCategory', $this->subcategory->getCategoryId());
    }

    public function test_getCategoryId_shouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->subcategory->getCategoryId());
    }

    public function test_name_set_get()
    {
        $this->subcategory->setName('testName');

        $this->assertSame('testName', $this->subcategory->getName());
    }

    public function test_getName_shouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->subcategory->getName());
    }

    public function test_getName_ShouldDefaultToId_IfNoNameIsSet()
    {
        $this->subcategory->setId('myTestId');

        $this->assertSame('myTestId', $this->subcategory->getName());
        $this->assertSame('myTestId', $this->subcategory->getId());
    }

    public function test_order_set_get()
    {
        $this->subcategory->setOrder(99);
        $this->assertSame(99, $this->subcategory->getOrder());

        $this->subcategory->setOrder('98');
        $this->assertSame(98, $this->subcategory->getOrder());
    }

    public function test_getOrder_shouldReturnADefaultValue()
    {
        $this->assertSame(99, $this->subcategory->getOrder());
    }

    public function test_id_set_get()
    {
        $this->subcategory->setId('myCustomId');
        $this->assertSame('myCustomId', $this->subcategory->getId());
    }

    public function test_getId_shouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->subcategory->getId());
    }
}
