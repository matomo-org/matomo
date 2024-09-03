<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function testCategoryIdSetGet()
    {
        $this->subcategory->setCategoryId('testCategory');

        $this->assertSame('testCategory', $this->subcategory->getCategoryId());
    }

    public function testGetCategoryIdShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->subcategory->getCategoryId());
    }

    public function testNameSetGet()
    {
        $this->subcategory->setName('testName');

        $this->assertSame('testName', $this->subcategory->getName());
    }

    public function testGetNameShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->subcategory->getName());
    }

    public function testGetNameShouldDefaultToIdIfNoNameIsSet()
    {
        $this->subcategory->setId('myTestId');

        $this->assertSame('myTestId', $this->subcategory->getName());
        $this->assertSame('myTestId', $this->subcategory->getId());
    }

    public function testOrderSetGet()
    {
        $this->subcategory->setOrder(99);
        $this->assertSame(99, $this->subcategory->getOrder());

        $this->subcategory->setOrder('98');
        $this->assertSame(98, $this->subcategory->getOrder());
    }

    public function testGetOrderShouldReturnADefaultValue()
    {
        $this->assertSame(99, $this->subcategory->getOrder());
    }

    public function testIdSetGet()
    {
        $this->subcategory->setId('myCustomId');
        $this->assertSame('myCustomId', $this->subcategory->getId());
    }

    public function testGetIdShouldBeEmptyStringByDefault()
    {
        $this->assertSame('', $this->subcategory->getId());
    }
}
