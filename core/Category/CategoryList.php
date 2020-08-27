<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Category;

use Piwik\Container\StaticContainer;

/**
 * Base type for category. lets you change the name for a categoryId and specify a different order
 * so the category appears eg at a different order in the reporting menu.
 *
 * This class is for now not exposed as public API until needed. Categories of plugins will be automatically
 * displayed in the menu at the very right after all core categories.
 */
class CategoryList
{
    /**
     * @var Category[] indexed by categoryId
     */
    private $categories = array();

    public function addCategory(Category $category)
    {
        $categoryId = $category->getId();

        if ($this->hasCategory($categoryId)) {
            throw new \Exception(sprintf('Category %s already exists', $categoryId));
        }

        $this->categories[$categoryId] = $category;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function hasCategory($categoryId)
    {
        return isset($this->categories[$categoryId]);
    }

    /**
     * Get the category having the given id, if possible.
     *
     * @param string $categoryId
     * @return Category|null
     */
    public function getCategory($categoryId)
    {
        if ($this->hasCategory($categoryId)) {
            return $this->categories[$categoryId];
        }
    }

    /**
     * @return CategoryList
     */
    public static function get()
    {
        $list = new CategoryList();

        $categories = StaticContainer::get('Piwik\Plugin\Categories');

        foreach ($categories->getAllCategories() as $category) {
            $list->addCategory($category);
        }

        // move subcategories into categories
        foreach ($categories->getAllSubcategories() as $subcategory) {
            $categoryId = $subcategory->getCategoryId();

            if (!$categoryId) {
                continue;
            }

            if ($list->hasCategory($categoryId)) {
                $category = $list->getCategory($categoryId);
            } else {
                $category = new Category();
                $category->setId($categoryId);
                $list->addCategory($category);
            }

            $category->addSubcategory($subcategory);
        }

        return $list;
    }
}
