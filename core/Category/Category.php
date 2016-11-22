<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Category;

/**
 * Base type for category. lets you change the name for a categoryId and specifiy a different order
 * so the category appears eg at a different order in the reporting menu.
 *
 * This class is for now not exposed as public API until needed. Categories of plugins will be automatically
 * displayed in the menu at the very right after all core categories.
 */
class Category
{
    /**
     * The id of the category as specified eg in {@link Piwik\Widget\WidgetConfig::setCategoryId()`} or
     * {@link Piwik\Report\getCategoryId()}. The id is used as the name in the menu and will be visible in the
     * URL.
     *
     * @var string Should be a translation key, eg 'General_Vists'
     */
    protected $id = '';

    /**
     * @var Subcategory[]
     */
    protected $subcategories = array();

    /**
     * The order of the category. The lower the value the further left the category will appear in the menu.
     * @var int
     */
    protected $order = 99;

    /**
     * The icon for this category, eg 'icon-user'
     * @var int
     */
    protected $icon = '';

    /**
     * @param int $order
     * @return static
     */
    public function setOrder($order)
    {
        $this->order = (int) $order;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function addSubcategory(Subcategory $subcategory)
    {
        $subcategoryId = $subcategory->getId();

        if ($this->hasSubcategory($subcategoryId)) {
            throw new \Exception(sprintf('Subcategory %s already exists', $subcategoryId));
        }

        $this->subcategories[$subcategoryId] = $subcategory;
    }

    public function hasSubcategory($subcategoryId)
    {
        return isset($this->subcategories[$subcategoryId]);
    }

    public function getSubcategory($subcategoryId)
    {
        if ($this->hasSubcategory($subcategoryId)) {
            return $this->subcategories[$subcategoryId];
        }
    }

    /**
     * @return Subcategory[]
     */
    public function getSubcategories()
    {
        return array_values($this->subcategories);
    }

    public function hasSubCategories()
    {
        return !empty($this->subcategories);
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

}